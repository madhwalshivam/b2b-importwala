<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Setting;
use PDO;

/**
 * Upload to embed to Qdrant search, with a 1-hour vector cache per sid.
 */
class EmbeddingSearchService
{
    public const TOP_K = 60;
    public const CACHE_SECONDS = 3600;

    private PDO $db;

    public function __construct()
    {
        EmbeddingSchema::ensure();
        $this->db = Database::getInstance();
    }

    public static function minScore(): float
    {
        $raw = null;
        try {
            $raw = Setting::get('image_search_min_score', null);
        } catch (\Throwable $e) {
            $raw = null;
        }
        if ($raw === null || $raw === '') {
            $raw = env('IMAGE_SEARCH_MIN_SCORE', '0.70');
        }
        $n = (float) $raw;
        if ($n <= 0 || $n > 0.99) {
            $n = 0.70;
        }
        return $n;
    }

    public static function rateLimitPerHour(): int
    {
        $n = (int) env('IMAGE_SEARCH_RATE_LIMIT', 20);
        return $n > 0 ? $n : 20;
    }

    /**
     * @return array{unavailable:bool, bad_image:bool, message:?string, items:array, vector?:array}
     */
    public function searchFile(string $path): array
    {
        $empty = [
            'unavailable' => false,
            'bad_image' => false,
            'message' => null,
            'items' => [],
        ];
        try {
            $vector = (new EmbeddingService())->embedFile($path);
            $hits = (new QdrantClient())->search($vector, self::TOP_K, self::minScore(), null);
            $empty['items'] = $this->toResultRows($hits);
            $empty['vector'] = $vector;
            return $empty;
        } catch (EmbeddingException $e) {
            if ($e->isUnavailable()) {
                $empty['unavailable'] = true;
                $empty['message'] = 'Image search is temporarily unavailable. Please try again in a few minutes.';
                return $empty;
            }
            $empty['bad_image'] = true;
            $empty['message'] = 'Could not read this image. Use a JPG, PNG, or WEBP photo under 5MB.';
            return $empty;
        } catch (QdrantException $e) {
            error_log('[ImageSearch] ' . $e->getMessage());
            $empty['unavailable'] = true;
            $empty['message'] = 'Image search is temporarily unavailable. Please try again in a few minutes.';
            return $empty;
        } catch (\Throwable $e) {
            error_log('[ImageSearch] ' . $e->getMessage());
            $empty['unavailable'] = true;
            $empty['message'] = 'Image search is temporarily unavailable. Please try again in a few minutes.';
            return $empty;
        }
    }

    /**
     * @param float[] $vector
     */
    public function rememberVector(string $token, array $vector): void
    {
        $token = $this->cleanToken($token);
        if ($token === '' || $vector === []) {
            return;
        }
        $json = json_encode(array_values($vector));
        if ($json === false) {
            return;
        }
        $stmt = $this->db->prepare("
            INSERT INTO image_search_vectors (token, vector_json, created_at, expires_at)
            VALUES (:token, :json, NOW(), DATE_ADD(NOW(), INTERVAL " . (int) self::CACHE_SECONDS . " SECOND))
            ON DUPLICATE KEY UPDATE
              vector_json = VALUES(vector_json),
              created_at = NOW(),
              expires_at = DATE_ADD(NOW(), INTERVAL " . (int) self::CACHE_SECONDS . " SECOND)
        ");
        $stmt->execute([
            'token' => $token,
            'json' => $json,
        ]);
        if (mt_rand(1, 50) === 1) {
            $this->db->exec("DELETE FROM image_search_vectors WHERE expires_at <= NOW() LIMIT 200");
        }
    }

    /**
     * Re-query Qdrant for an existing sid. Uses the cached vector when it is
     * less than an hour old; otherwise rebuilds it from the saved upload.
     *
     * @return array{unavailable:bool, items:array}
     */
    public function searchByToken(string $token, ?string $savedImagePath, ?int $categoryId): array
    {
        $token = $this->cleanToken($token);
        $vector = $this->loadVector($token);
        if ($vector === null && $savedImagePath && is_file($savedImagePath)) {
            try {
                $vector = (new EmbeddingService())->embedFile($savedImagePath);
                $this->rememberVector($token, $vector);
            } catch (\Throwable $e) {
                error_log('[ImageSearch] cache rebuild failed: ' . $e->getMessage());
                return ['unavailable' => true, 'items' => []];
            }
        }
        if ($vector === null) {
            return ['unavailable' => true, 'items' => []];
        }
        try {
            $hits = (new QdrantClient())->search($vector, self::TOP_K, self::minScore(), $categoryId);
            return ['unavailable' => false, 'items' => $this->toResultRows($hits)];
        } catch (\Throwable $e) {
            error_log('[ImageSearch] category query failed: ' . $e->getMessage());
            return ['unavailable' => true, 'items' => []];
        }
    }

    /**
     * @param array<int, array{product_id:int, score:float}> $hits
     * @return array<int, array{product_id:int, variant_id:null, score:float}>
     */
    private function toResultRows(array $hits): array
    {
        $min = self::minScore();
        $rows = [];
        $seen = [];
        foreach ($hits as $hit) {
            $pid = (int) ($hit['product_id'] ?? 0);
            $score = (float) ($hit['score'] ?? 0);
            if ($pid <= 0 || $score < $min || isset($seen[$pid])) {
                continue;
            }
            $seen[$pid] = true;
            $rows[] = [
                'id' => $pid,
                'product_id' => $pid,
                'variant_id' => null,
                'score' => round($score, 4),
                'raw_score' => round($score, 4),
            ];
        }
        return $rows;
    }

    /**
     * @return float[]|null
     */
    private function loadVector(string $token): ?array
    {
        if ($token === '') {
            return null;
        }
        $stmt = $this->db->prepare("
            SELECT vector_json FROM image_search_vectors
            WHERE token = ? AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $json = $stmt->fetchColumn();
        if (!is_string($json) || $json === '') {
            return null;
        }
        $data = json_decode($json, true);
        if (!is_array($data) || count($data) < 8) {
            return null;
        }
        $out = [];
        foreach ($data as $n) {
            if (!is_numeric($n)) {
                return null;
            }
            $out[] = (float) $n;
        }
        return $out;
    }

    private function cleanToken(string $token): string
    {
        $token = preg_replace('/[^a-f0-9]/', '', strtolower($token)) ?? '';
        return strlen($token) === 32 ? $token : '';
    }
}
