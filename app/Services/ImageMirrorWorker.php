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

        $stmt = $this->db->prepare("SELECT * FROM image_mirror_queue WHERE status IN ('pending', 'failed') AND attempts < 3 ORDER BY updated_at ASC LIMIT " . (int)$limit);
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
        $results = [];

        foreach ($chunk as $row) {
            $ch = curl_init($row['source_url']);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_REFERER        => 'https://google.com/',
                CURLOPT_HTTPHEADER     => ['Accept: image/jpeg, image/png, image/webp, image/gif, image/*;q=0.8, */*;q=0.5'],
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$row['id']] = $ch;
        }

        do {
            curl_multi_exec($mh, $active);
            if ($active) {
                curl_multi_select($mh);
            }
        } while ($active);


        foreach ($handles as $id => $ch) {
            $error = curl_error($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $data = curl_multi_getcontent($ch);

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);

            if ($error) {
                $results[$id] = ['success' => false, 'error' => $error];
            } elseif ($code >= 400) {
                $results[$id] = ['success' => false, 'error' => "HTTP Code $code"];
            } elseif (empty($data)) {
                $results[$id] = ['success' => false, 'error' => "Empty response"];
            } else {
                $results[$id] = ['success' => true, 'data' => $data, 'mime' => strpos($type, 'image/') !== false ? $type : 'image/jpeg'];
            }
        }

        curl_multi_close($mh);
        return $results;
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
