<?php
namespace App\Services;

use App\Core\Database;
use App\Services\CloudflareR2;

class ImageMirrorWorker
{
    private \PDO $db;
    private CloudflareR2 $r2;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->r2 = new CloudflareR2();
    }

    public function run(int $limit = 20, int $seconds = 40): array
    {
        set_time_limit(60);
        $startTime = time();

        // Also retry Bulkflow rows that already exhausted their attempts on the
        // old transport errors. A retry replaces last_error, and attempts < 6
        // stops a row that still fails the same way from looping.
        $stmt = $this->db->prepare("
            SELECT * FROM image_mirror_queue
            WHERE (status IN ('pending', 'failed') AND attempts < 3)
               OR (
                    status = 'failed'
                    AND attempts < 6
                    AND source_url LIKE '%bulkflowai.com/api/img%'
                    AND last_error IN ('HTTP Code 400', 'Empty response', 'HTTP 400: no body')
               )
            ORDER BY updated_at ASC
            LIMIT " . (int) $limit
        );
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return ['processed' => 0, 'done' => 0, 'failed' => 0, 'pending' => $this->getPendingCount()];
        }

        $processed = 0;
        $done = 0;
        $failed = 0;

        // Mark as processing
        $ids = array_column($rows, 'id');
        if (!empty($ids)) {
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $this->db->prepare("UPDATE image_mirror_queue SET status = 'processing', updated_at = NOW() WHERE id IN ($inQuery)")->execute($ids);
        }

        // Process in chunks of 6 (parallel)
        $chunks = array_chunk($rows, 6);
        foreach ($chunks as $chunk) {
            if (time() - $startTime >= $seconds) {
                break;
            }

            $results = $this->downloadParallel($chunk);

            foreach ($chunk as $row) {
                $processed++;
                $res = $results[$row['id']] ?? ['success' => false, 'error' => 'No response'];

                if ($res['success']) {
                    // Upload to R2
                    $uploadRes = $this->processAndUpload($row, $res['data'], $res['mime']);
                    if ($uploadRes['success']) {
                        $this->markDone($row, $uploadRes['r2_key']);
                        $done++;
                    } else {
                        $this->markFailed($row, $uploadRes['error']);
                        $failed++;
                    }
                } else {
                    $this->markFailed($row, $res['error']);
                    $failed++;
                }
            }
        }

        // Reset leftover processing
        $this->db->query("UPDATE image_mirror_queue SET status = 'pending', updated_at = NOW() WHERE status = 'processing' AND updated_at < NOW() - INTERVAL 5 MINUTE");

        return [
            'processed' => $processed,
            'done' => $done,
            'failed' => $failed,
            'pending' => $this->getPendingCount()
        ];
    }

    private function getPendingCount(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM image_mirror_queue WHERE status IN ('pending', 'failed') AND attempts < 3")->fetchColumn();
    }

    private function markDone(array $row, string $r2Key): void
    {
        $this->db->prepare("UPDATE image_mirror_queue SET status = 'done', r2_key = ?, updated_at = NOW() WHERE id = ?")->execute([$r2Key, $row['id']]);

        // Build R2 public URL directly from key (avoid calling image_url() to prevent recursion)
        $r2PublicUrl = rtrim(env('R2_PUBLIC_URL', ''), '/');
        $r2Url = !empty($r2PublicUrl) ? $r2PublicUrl . '/' . ltrim($r2Key, '/') : $r2Key;
        $sourceUrl = $row['source_url'];

        // Update every table column that may still hold the original external URL
        $updates = [
            "UPDATE product_images SET image_url = ?, image_path = ? WHERE image_url = ?",
            "UPDATE product_images SET image_url = ?, image_path = ? WHERE image_path = ?",
            "UPDATE products SET main_image = ? WHERE main_image = ?",
            "UPDATE product_colors SET swatch_hex_or_image = ? WHERE swatch_hex_or_image = ?",
            "UPDATE product_variants SET image_url = ? WHERE image_url = ?",
            "UPDATE inquiry_items SET product_image_snapshot = ? WHERE product_image_snapshot = ?",
        ];

        foreach ($updates as $sql) {
            try {
                // product_images needs 3 params (url, path, old); others need 2
                if (strpos($sql, 'image_url = ?, image_path') !== false) {
                    $this->db->prepare($sql)->execute([$r2Url, $r2Url, $sourceUrl]);
                } else {
                    $this->db->prepare($sql)->execute([$r2Url, $sourceUrl]);
                }
            } catch (\Exception $e) {
                // Ignore update errors (e.g. missing table)
            }
        }
    }


    private function markFailed(array $row, string $error): void
    {
        $this->db->prepare("UPDATE image_mirror_queue SET status = 'failed', attempts = attempts + 1, last_error = ?, updated_at = NOW() WHERE id = ?")->execute([substr($error, 0, 1000), $row['id']]);
    }

    private function downloadParallel(array $chunk): array
    {
        $mh = curl_multi_init();
        $handles = [];
        $fetchUrls = [];

        foreach ($chunk as $row) {
            $fetchUrl = \App\Helpers\ImageMirror::normalizeFetchUrl((string) $row['source_url']);
            $fetchUrls[$row['id']] = $fetchUrl;
            $ch = curl_init();
            curl_setopt_array($ch, $this->curlFetchOptions($fetchUrl, false));
            curl_multi_add_handle($mh, $ch);
            $handles[$row['id']] = $ch;
        }

        do {
            $mrc = curl_multi_exec($mh, $active);
            if ($active) {
                if (curl_multi_select($mh, 1.0) === -1) {
                    usleep(10000);
                }
            }
        } while ($active && $mrc === CURLM_OK);

        // curl_error() stays empty until the multi interface reports the handle.
        $errnoById = [];
        while ($info = curl_multi_info_read($mh)) {
            $hid = array_search($info['handle'], $handles, true);
            if ($hid !== false) {
                $errnoById[$hid] = (int) ($info['result'] ?? 0);
            }
        }

        // Read every body before closing any handle. Closing one easy handle
        // can wipe the body of a sibling that has not been read yet.
        $raw = [];
        foreach ($handles as $id => $ch) {
            $data = curl_multi_getcontent($ch);
            $raw[$id] = [
                'errno' => $errnoById[$id] ?? curl_errno($ch),
                'error' => curl_error($ch),
                'code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
                'type' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
                'data' => is_string($data) ? $data : '',
            ];
        }
        foreach ($handles as $ch) {
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);

        $results = [];
        foreach ($raw as $id => $info) {
            $judged = $this->interpretDownload($info);
            $body = $info['data'];
            $transportFailed = ($info['errno'] ?? 0) !== 0 || ($info['code'] ?? 0) === 0 || $body === '';
            if (!$judged['success'] && $transportFailed) {
                // Single HTTP/1.1 request reports curl errors the multi
                // interface hides, and it still has the response body when
                // the parallel read comes back empty.
                $judged = $this->downloadSingle($fetchUrls[$id]);
            }
            $results[$id] = $judged;
        }

        return $results;
    }

    private function downloadSingle(string $url): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, $this->curlFetchOptions($url, true));
        $data = curl_exec($ch);
        $info = [
            'errno' => curl_errno($ch),
            'error' => curl_error($ch),
            'code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'type' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
            'data' => is_string($data) ? $data : '',
        ];
        curl_close($ch);
        return $this->interpretDownload($info);
    }

    private function curlFetchOptions(string $url, bool $http1): array
    {
        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_REFERER => 'https://google.com/',
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => ['Accept: image/jpeg, image/png, image/webp, image/gif, image/*;q=0.8, */*;q=0.5'],
        ];
        if ($http1) {
            $opts[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
        }
        return $opts;
    }

    private function interpretDownload(array $info): array
    {
        $code = (int) ($info['code'] ?? 0);
        $errno = (int) ($info['errno'] ?? 0);
        $error = (string) ($info['error'] ?? '');
        $type = $info['type'] ?? '';
        $data = is_string($info['data'] ?? null) ? $info['data'] : '';

        if ($data !== '' && $this->looksLikeImage($data) && ($code === 0 || ($code >= 200 && $code < 500))) {
            $mime = (is_string($type) && strpos($type, 'image/') !== false) ? $type : $this->mimeFromImage($data);
            return ['success' => true, 'data' => $data, 'mime' => $mime];
        }

        if ($errno !== 0 || $error !== '') {
            $msg = $error !== '' ? $error : ('cURL error ' . $errno);
            if ($code > 0) {
                $msg .= ' (HTTP ' . $code . ')';
            }
            return ['success' => false, 'error' => $msg];
        }

        if ($code >= 400) {
            return ['success' => false, 'error' => 'HTTP ' . $code . ':' . $this->bodySnippet($data, is_string($type) ? $type : '')];
        }

        if ($data === '') {
            return ['success' => false, 'error' => 'Empty response (HTTP ' . $code . ')'];
        }

        $mime = (is_string($type) && strpos($type, 'image/') !== false) ? $type : 'image/jpeg';
        return ['success' => true, 'data' => $data, 'mime' => $mime];
    }

    private function bodySnippet(string $data, string $type): string
    {
        if ($data === '') {
            return ' empty body';
        }
        if ($type !== '' && strpos($type, 'image/') !== false) {
            return ' image body ' . strlen($data) . ' bytes';
        }
        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($data)));
        if ($text === '') {
            return ' ' . strlen($data) . ' bytes';
        }
        if (strlen($text) > 180) {
            $text = substr($text, 0, 180) . '...';
        }
        return ' ' . $text;
    }

    private function looksLikeImage(string $data): bool
    {
        if (strlen($data) < 12) {
            return false;
        }
        if (strncmp($data, "\xFF\xD8\xFF", 3) === 0) {
            return true;
        }
        if (strncmp($data, "\x89PNG\r\n\x1a\n", 8) === 0) {
            return true;
        }
        if (strncmp($data, 'GIF87a', 6) === 0 || strncmp($data, 'GIF89a', 6) === 0) {
            return true;
        }
        return strncmp($data, 'RIFF', 4) === 0 && substr($data, 8, 4) === 'WEBP';
    }

    private function mimeFromImage(string $data): string
    {
        if (strncmp($data, "\x89PNG", 4) === 0) {
            return 'image/png';
        }
        if (strncmp($data, 'GIF8', 4) === 0) {
            return 'image/gif';
        }
        if (strncmp($data, 'RIFF', 4) === 0) {
            return 'image/webp';
        }
        return 'image/jpeg';
    }

    private function processAndUpload(array $row, string $data, string $mime): array
    {
        // Infer extension
        $ext = 'jpg';
        if (strpos($mime, 'png') !== false) $ext = 'png';
        elseif (strpos($mime, 'webp') !== false) $ext = 'webp';
        elseif (strpos($mime, 'gif') !== false) $ext = 'gif';

        $hash = $row['source_hash'];
        $folder = substr($hash, 0, 2);
        $r2Key = "products/{$folder}/{$hash}.{$ext}";

        // Resize using GD if possible
        if (function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring($data);
            if ($img !== false) {
                $width = imagesx($img);
                $height = imagesy($img);
                
                if ($width > 1600) {
                    $newWidth = 1600;
                    $newHeight = (int)($height * (1600 / $width));
                    
                    $resized = imagecreatetruecolor($newWidth, $newHeight);
                    if ($ext === 'png') {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                    }
                    
                    imagecopyresampled($resized, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    
                    ob_start();
                    if ($ext === 'png') {
                        imagepng($resized, null, 8);
                    } elseif ($ext === 'webp') {
                        imagewebp($resized, null, 82);
                    } else {
                        imagejpeg($resized, null, 82);
                    }
                    $data = ob_get_clean();
                    
                    imagedestroy($resized);
                }
                imagedestroy($img);
            }
        }

        // Write to temp file for upload
        $tmp = tempnam(sys_get_temp_dir(), 'img_');
        file_put_contents($tmp, $data);

        try {
            $success = $this->r2->uploadFile($tmp, $r2Key, $mime ?: 'image/jpeg');
            @unlink($tmp);
            if ($success) {
                return ['success' => true, 'r2_key' => $r2Key];
            }
            return ['success' => false, 'error' => 'R2 Upload returned false. Check logs.'];
        } catch (\Exception $e) {
            @unlink($tmp);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
