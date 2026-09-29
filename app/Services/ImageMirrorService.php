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
     * Returns null on failure.
     */
    public function mirrorImage(string $url, string $sku, string $type): ?string
    {
        $url = trim($url);
        if (empty($url)) return null;

        // Skip if already R2 or local relative path (not an external url)
        if (strpos($url, 'r2.dev') !== false || strpos($url, 'cloudflare') !== false) {
            return $url;
        }
        if (!preg_match('#^https?://#i', $url)) {
            // Already a local path or invalid
            return $url;
        }

        $hash = hash('sha256', $url);
        
        // Check map
        $stmt = $this->db->prepare("SELECT r2_url FROM image_mirror_map WHERE source_url_hash = ?");
        $stmt->execute([$hash]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            return $existing;
        }

        // Download
        $tempFile = sys_get_temp_dir() . '/' . uniqid('mirror_') . '.tmp';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $fp = fopen($tempFile, 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if ($httpCode < 200 || $httpCode >= 300 || filesize($tempFile) === 0) {
            @unlink($tempFile);
            return null;
        }

        // Validate image
        $mime = mime_content_type($tempFile);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMimes)) {
            @unlink($tempFile);
            return null;
        }

        // Optional compression (resize to 1600px max, webp)
        $finalFile = $this->compressImage($tempFile, $mime);
        $finalMime = mime_content_type($finalFile);
        $ext = ($finalMime === 'image/webp') ? 'webp' : (($finalMime === 'image/png') ? 'png' : (($finalMime === 'image/gif') ? 'gif' : 'jpg'));

        $shortHash = substr($hash, 0, 8);
        $r2Key = "products/" . strtolower($sku) . "/{$type}-{$shortHash}.{$ext}";

        // Upload to R2
        $success = $this->r2->uploadFile($finalFile, $r2Key, $finalMime);
        
        @unlink($tempFile);
        if ($finalFile !== $tempFile) {
            @unlink($finalFile);
        }

        if ($success) {
            // Assume we can construct the public URL (you might want this from config)
            $r2PublicBase = 'https://01e0ff8f64110937bdefd6c0f82bc3c6.r2.cloudflarestorage.com/importwala-images/'; // or cdn.importwala.com
            $r2Url = $r2PublicBase . $r2Key;

            $stmt = $this->db->prepare("INSERT INTO image_mirror_map (source_url_hash, source_url, r2_url) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$hash, $url, $r2Url]);
            } catch (\Exception $e) {}

            return $r2Url;
        }

        return null;
    }

    private function compressImage(string $filePath, string $mimeType): string
    {
        if (!function_exists('imagecreatefromjpeg')) {
            return $filePath;
        }

        switch ($mimeType) {
            case 'image/jpeg': $img = @imagecreatefromjpeg($filePath); break;
            case 'image/png': $img = @imagecreatefrompng($filePath); break;
            case 'image/webp': $img = @imagecreatefromwebp($filePath); break;
            case 'image/gif': $img = @imagecreatefromgif($filePath); break;
            default: return $filePath;
        }

        if (!$img) return $filePath;

        $width = imagesx($img);
        $height = imagesy($img);
        $max = 1600;

        if ($width > $max || $height > $max) {
            if ($width > $height) {
                $newWidth = $max;
                $newHeight = (int)($height * ($max / $width));
            } else {
                $newHeight = $max;
                $newWidth = (int)($width * ($max / $height));
            }

            $newImg = imagecreatetruecolor($newWidth, $newHeight);
            if ($mimeType === 'image/png' || $mimeType === 'image/webp' || $mimeType === 'image/gif') {
                imagealphablending($newImg, false);
                imagesavealpha($newImg, true);
                $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
                imagefilledrectangle($newImg, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($img);
            $img = $newImg;
        }

        $outFile = sys_get_temp_dir() . '/' . uniqid('opt_') . '.webp';
        
        if (function_exists('imagewebp')) {
            imagewebp($img, $outFile, 80);
            imagedestroy($img);
            return $outFile;
        }
        
        imagedestroy($img);
        return $filePath;
    }
}
