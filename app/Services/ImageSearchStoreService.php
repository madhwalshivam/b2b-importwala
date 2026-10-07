<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Persist image-search uploads + ranked results for the /image-search page.
 * Public-safe JSON only (product_id, variant_id, score).
 */
class ImageSearchStoreService
{
    public const TTL_DAYS = 7;
    public const TOKEN_BYTES = 16; // 32 hex chars
    public const MAX_SIDE = 600;

    private PDO $db;
    private string $rootPath;
    private string $storageDir;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        $this->storageDir = $this->rootPath . '/storage/image_search';
        $this->ensureTable();
        if (!is_dir($this->storageDir)) {
            @mkdir($this->storageDir, 0755, true);
        }
        // Opportunistic cleanup (~1% of requests)
        if (mt_rand(1, 100) === 1) {
            try {
                $this->cleanupExpired();
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * Save resized query image + ranked results. Returns 32-char token.
     *
     * @param string $sourceImagePath Local path to uploaded/temp image
     * @param array  $items           Search result items (will be sanitized)
     * @param bool   $hasConfidentMatch
     */
    public function store(string $sourceImagePath, array $items, bool $hasConfidentMatch): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_BYTES));
        $relPath = $token . '.jpg';
        $absPath = $this->storageDir . '/' . $relPath;

        if (!$this->saveResizedJpeg($sourceImagePath, $absPath)) {
            // Fall back to copying raw bytes if GD resize fails but file is readable
            $bin = @file_get_contents($sourceImagePath);
            if ($bin === false || $bin === '') {
                throw new \RuntimeException('Could not save uploaded image for image search.');
            }
            file_put_contents($absPath, $bin);
        }

        $safe = [];
        foreach ($items as $item) {
            $row = $this->sanitizeResultRow($item);
            if ($row !== null) {
                $safe[] = $row;
            }
        }

        $expires = (new \DateTimeImmutable('now'))->modify('+' . self::TTL_DAYS . ' days')->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            INSERT INTO image_searches (token, uploaded_image_path, results_json, has_confident_match, created_at, expires_at)
            VALUES (:token, :path, :json, :confident, NOW(), :expires)
        ");
        $stmt->execute([
            'token' => $token,
            'path' => 'storage/image_search/' . $relPath,
            'json' => json_encode($safe, JSON_UNESCAPED_UNICODE),
            'confident' => $hasConfidentMatch ? 1 : 0,
            'expires' => $expires,
        ]);

        return $token;
    }

    /**
     * @return array{token:string,uploaded_image_path:string,results:array,has_confident_match:bool,created_at:string,expires_at:string}|null
     */
    public function findValid(string $token): ?array
    {
        $token = preg_replace('/[^a-f0-9]/', '', strtolower($token)) ?? '';
        if (strlen($token) !== 32) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT token, uploaded_image_path, results_json, has_confident_match, created_at, expires_at
            FROM image_searches
            WHERE token = ? AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $results = json_decode($row['results_json'] ?? '[]', true);
        if (!is_array($results)) {
            $results = [];
        }

        return [
            'token' => $row['token'],
            'uploaded_image_path' => $row['uploaded_image_path'],
            'results' => $results,
            'has_confident_match' => !empty($row['has_confident_match']),
            'created_at' => $row['created_at'],
            'expires_at' => $row['expires_at'],
        ];
    }

    public function absoluteImagePath(string $storedPath): ?string
    {
        $storedPath = str_replace(['..', '\\'], ['', '/'], $storedPath);
        if (preg_match('~^https?://~i', $storedPath)) {
            return null;
        }
        $abs = $this->rootPath . '/' . ltrim($storedPath, '/');
        return (is_file($abs)) ? $abs : null;
    }

    /**
     * Delete expired rows and their image files.
     *
     * @return array{rows:int,files:int}
     */
    public function cleanupExpired(): array
    {
        $stmt = $this->db->query("
            SELECT id, uploaded_image_path FROM image_searches WHERE expires_at <= NOW() LIMIT 500
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $files = 0;
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) $row['id'];
            $abs = $this->absoluteImagePath($row['uploaded_image_path'] ?? '');
            if ($abs && is_file($abs) && @unlink($abs)) {
                $files++;
            }
        }
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $del = $this->db->prepare("DELETE FROM image_searches WHERE id IN ($placeholders)");
            $del->execute($ids);
        }
        return ['rows' => count($ids), 'files' => $files];
    }

    private function sanitizeResultRow(array $item): ?array
    {
        $pid = (int) ($item['product_id'] ?? $item['id'] ?? 0);
        if ($pid <= 0) {
            return null;
        }
        $vid = $item['variant_id'] ?? $item['matched_variant_id'] ?? null;
        $vid = ($vid !== null && (int) $vid > 0) ? (int) $vid : null;
        $score = isset($item['score']) ? (float) $item['score'] : (isset($item['raw_score']) ? (float) $item['raw_score'] : 0.0);

        return [
            'product_id' => $pid,
            'variant_id' => $vid,
            'score' => round(max(0, min(1, $score)), 4),
        ];
    }

    private function saveResizedJpeg(string $sourcePath, string $destPath): bool
    {
        if (!extension_loaded('gd') || !is_file($sourcePath)) {
            return false;
        }
        $bin = @file_get_contents($sourcePath);
        if ($bin === false || $bin === '') {
            return false;
        }
        $img = @imagecreatefromstring($bin);
        if (!$img) {
            return false;
        }

        $w = imagesx($img);
        $h = imagesy($img);
        if ($w < 1 || $h < 1) {
            imagedestroy($img);
            return false;
        }

        $max = self::MAX_SIDE;
        $scale = min(1.0, $max / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $dst = imagecreatetruecolor($nw, $nh);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);

        $ok = imagejpeg($dst, $destPath, 85);
        imagedestroy($dst);
        return (bool) $ok;
    }

    private function ensureTable(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        try {
            $this->db->query('SELECT 1 FROM image_searches LIMIT 1');
        } catch (\Throwable $e) {
            $sqlFile = $this->rootPath . '/database/migrations/image_searches.sql';
            if (is_file($sqlFile)) {
                try {
                    $this->db->exec(file_get_contents($sqlFile));
                } catch (\Throwable $e2) {
                    error_log('[ImageSearchStore] ensureTable failed: ' . $e2->getMessage());
                }
            }
        }
    }
}
