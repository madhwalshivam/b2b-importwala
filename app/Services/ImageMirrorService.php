<?php

namespace App\Services;

use App\Core\Database;
use App\Services\CloudflareR2;

class ImageMirrorService
{
    private \PDO $db;
    private CloudflareR2 $r2;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->r2 = new CloudflareR2();
    }

    /**
     * Mirror an external URL to Cloudflare R2 and return the R2 URL.
     * Returns ['success' => bool, 'url' => string|null, 'error' => string|null]
     */
    public function mirrorImage(string $url, string $sku, string $type): array
    {
        $url = trim($url);
        if (empty($url))
            return ['success' => false, 'error' => 'Empty URL provided'];

        // Validate R2 client is initialized
        if ($this->r2->getClient() === null) {
            throw new \Exception('Cloudflare R2 is not configured properly! Client could not be initialized.');
        }

        // Skip if already R2
        if (strpos($url, 'r2.dev') !== false || strpos($url, 'cloudflare') !== false) {
            return ['success' => true, 'url' => $url];
        }
        if (!preg_match('#^https?://#i', $url)) {
            return ['success' => false, 'error' => 'Invalid URL protocol'];
        }

        $hash = hash('sha256', $url);

        // Check map
        $stmt = $this->db->prepare("SELECT r2_url FROM image_mirror_map WHERE source_url_hash = ?");
        $stmt->execute([$hash]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            // Verify existing URL is an r2.dev public URL, otherwise ignore map
            if (strpos($existing, 'r2.dev') !== false) {
                return ['success' => true, 'url' => $existing];
            } else {
                // Delete invalid cached URL
                $this->db->prepare("DELETE FROM image_mirror_map WHERE source_url_hash = ?")->execute([$hash]);
            }
        }

        $logFile = __DIR__ . '/../../logs/import_images.log';
        if (!is_dir(dirname($logFile)))
            @mkdir(dirname($logFile), 0777, true);

        // Download with Retries
        $maxRetries = 2;
        $imgData = false;
        $httpCode = 0;
        $errorMsg = '';

        $fetchUrl = \App\Helpers\ImageMirror::normalizeFetchUrl($url);
        for ($i = 0; $i <= $maxRetries; $i++) {
            $ch = curl_init($fetchUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $imgData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($imgData !== false && $httpCode >= 200 && $httpCode < 300 && strlen($imgData) > 0) {
                break; // Success
            }

            if ($i < $maxRetries) {
                sleep(1); // Short delay before retry
            } else {
                $err = "Download failed for {$url} (SKU: {$sku}). HTTP: {$httpCode}, cURL: {$error}";
                error_log(date('[Y-m-d H:i:s] ') . $err . PHP_EOL, 3, $logFile);
                return ['success' => false, 'error' => "HTTP {$httpCode} / cURL: {$error}"];
            }
        }

        // Validate image
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($imgData);

        $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (!isset($allowedMimes[$mime])) {
            $err = "Invalid MIME type ({$mime}) for {$url} (SKU: {$sku}).";
            error_log(date('[Y-m-d H:i:s] ') . $err . PHP_EOL, 3, $logFile);
            return ['success' => false, 'error' => "Invalid MIME type ({$mime})"];
        }

        // Double check with getimagesizefromstring
        if (!@getimagesizefromstring($imgData)) {
            $err = "File is not a valid image structure for {$url} (SKU: {$sku}).";
            error_log(date('[Y-m-d H:i:s] ') . $err . PHP_EOL, 3, $logFile);
            return ['success' => false, 'error' => 'Invalid image structure'];
        }

        $ext = $allowedMimes[$mime];
        $safeSku = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($sku));
        // Deterministic R2 key: same source URL + same SKU always yields same key
        $r2Key = "products/{$safeSku}/{$hash}.{$ext}";

        // Upload directly using client — retry up to 3 times on network errors
        $client = $this->r2->getClient();
        $bucket = $this->r2->getBucketName();
        $r2PublicBase = rtrim($this->r2->getPublicBaseUrl(), '/');
        $cleanKey = ltrim($r2Key, '/');
        $r2Url = $r2PublicBase . '/' . $cleanKey;

        $maxUploadRetries = 3;
        $lastUploadError = null;

        for ($attempt = 1; $attempt <= $maxUploadRetries; $attempt++) {
            try {
                $client->putObject([
                    'Bucket' => $bucket,
                    'Key' => $cleanKey,
                    'Body' => $imgData,
                    'ContentType' => $mime,
                    'CacheControl' => 'public, max-age=31536000'
                ]);

                // Verify upload succeeded
                $client->headObject([
                    'Bucket' => $bucket,
                    'Key' => $cleanKey
                ]);

                // Cache it in the mirror map
                $stmt = $this->db->prepare("INSERT IGNORE INTO image_mirror_map (source_url_hash, source_url, r2_url) VALUES (?, ?, ?)");
                try {
                    $stmt->execute([$hash, $url, $r2Url]);
                } catch (\Exception $e) {
                }

                return ['success' => true, 'url' => $r2Url];

            } catch (\Aws\Exception\AwsException $e) {
                $lastUploadError = "AwsError: " . $e->getAwsErrorCode() . " - " . $e->getMessage();
                error_log(date('[Y-m-d H:i:s] ') . "R2 Upload attempt {$attempt}/{$maxUploadRetries} failed for {$url} (SKU: {$sku}). {$lastUploadError}" . PHP_EOL, 3, $logFile);
                if ($attempt < $maxUploadRetries) {
                    sleep($attempt); // 1s, 2s backoff
                }
            } catch (\Exception $e) {
                $lastUploadError = $e->getMessage();
                error_log(date('[Y-m-d H:i:s] ') . "R2 Upload attempt {$attempt}/{$maxUploadRetries} failed for {$url} (SKU: {$sku}). Error: {$lastUploadError}" . PHP_EOL, 3, $logFile);
                if ($attempt < $maxUploadRetries) {
                    sleep($attempt);
                }
            }
        }

        return ['success' => false, 'error' => 'Upload failed after ' . $maxUploadRetries . ' attempts: ' . $lastUploadError];
    }
}
