<?php

namespace App\Services;

/**
 * Qdrant Cloud REST client (curl). Collection "products", cosine distance.
 */
class QdrantClient
{
    public const TIMEOUT_SECONDS = 8;

    private string $baseUrl;
    private string $apiKey;
    private string $collection;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) env('QDRANT_URL', ''), '/');
        $this->apiKey = trim((string) env('QDRANT_API_KEY', ''));
        $this->collection = trim((string) env('QDRANT_COLLECTION', 'products'));
        if ($this->collection === '') {
            $this->collection = 'products';
        }
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    public function collection(): string
    {
        return $this->collection;
    }

    public function ensureCollection(int $dimensions): void
    {
        $this->assertConfigured();
        $existing = $this->request('GET', '/collections/' . rawurlencode($this->collection), null, true);
        if ($existing['status'] === 200) {
            $size = (int) ($existing['json']['result']['config']['params']['vectors']['size'] ?? 0);
            if ($size > 0 && $size !== $dimensions) {
                error_log('[Qdrant] collection vector size is ' . $size . ' but EMBEDDING_DIMENSIONS is ' . $dimensions);
                throw new QdrantException('Vector size does not match EMBEDDING_DIMENSIONS. Create a new collection or keep the original dimension.');
            }
            return;
        }
        if ($existing['status'] !== 404) {
            throw new QdrantException('Could not read the vector collection.');
        }

        $created = $this->request('PUT', '/collections/' . rawurlencode($this->collection), [
            'vectors' => [
                'size' => $dimensions,
                'distance' => 'Cosine',
            ],
        ]);
        if ($created['status'] < 200 || $created['status'] >= 300) {
            throw new QdrantException('Could not create the vector collection.');
        }

        foreach ([
            ['field_name' => 'status', 'field_schema' => 'keyword'],
            ['field_name' => 'category_id', 'field_schema' => 'integer'],
            ['field_name' => 'subcategory_id', 'field_schema' => 'integer'],
        ] as $index) {
            try {
                $this->request('PUT', '/collections/' . rawurlencode($this->collection) . '/index', $index, true);
            } catch (\Throwable $e) {
                error_log('[Qdrant] payload index: ' . $e->getMessage());
            }
        }
    }

    /**
     * @param float[] $vector
     * @param array<string, mixed> $payload
     */
    public function upsert(int $productId, array $vector, array $payload): void
    {
        $this->assertConfigured();
        if ($productId <= 0 || $vector === []) {
            throw new QdrantException('Invalid point.');
        }
        $res = $this->request('PUT', '/collections/' . rawurlencode($this->collection) . '/points', [
            'points' => [
                [
                    'id' => $productId,
                    'vector' => array_values($vector),
                    'payload' => $payload,
                ],
            ],
        ]);
        if ($res['status'] < 200 || $res['status'] >= 300) {
            throw new QdrantException('Could not save the product vector.');
        }
    }

    public function delete(int $productId): void
    {
        if ($productId <= 0) {
            return;
        }
        $this->deleteMany([$productId]);
    }

    /**
     * @param int[] $productIds
     */
    public function deleteMany(array $productIds): void
    {
        $this->assertConfigured();
        $ids = [];
        foreach ($productIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        if ($ids === []) {
            return;
        }
        $res = $this->request('POST', '/collections/' . rawurlencode($this->collection) . '/points/delete', [
            'points' => array_values(array_unique($ids)),
        ], true);
        if ($res['status'] === 404) {
            return;
        }
        if ($res['status'] < 200 || $res['status'] >= 300) {
            throw new QdrantException('Could not remove the product vector.');
        }
    }

    /**
     * Cosine similarity search. Score is higher-is-better. Results below $minScore are omitted by Qdrant.
     *
     * @param float[] $vector
     * @return array<int, array{product_id:int, score:float, payload:array}>
     */
    public function search(array $vector, int $limit, float $minScore, ?int $categoryId = null): array
    {
        $this->assertConfigured();
        $limit = max(1, min(60, $limit));
        $must = [
            ['key' => 'status', 'match' => ['value' => 'active']],
        ];
        if ($categoryId !== null && $categoryId > 0) {
            $must[] = ['key' => 'category_id', 'match' => ['value' => $categoryId]];
        }

        $res = $this->request('POST', '/collections/' . rawurlencode($this->collection) . '/points/search', [
            'vector' => array_values($vector),
            'limit' => $limit,
            'with_payload' => true,
            'score_threshold' => $minScore,
            'filter' => ['must' => $must],
        ], true);
        if ($res['status'] === 404) {
            $dimensions = (int) env('EMBEDDING_DIMENSIONS', 1024);
            if ($dimensions <= 0) {
                $dimensions = 1024;
            }
            $this->ensureCollection($dimensions);
            return [];
        }
        if ($res['status'] < 200 || $res['status'] >= 300) {
            throw new QdrantException('Could not search the catalog.');
        }

        $rows = $res['json']['result'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $score = (float) ($row['score'] ?? 0);
            if ($score < $minScore) {
                continue;
            }
            $payload = is_array($row['payload'] ?? null) ? $row['payload'] : [];
            $pid = (int) ($payload['product_id'] ?? $row['id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            $out[] = [
                'product_id' => $pid,
                'score' => round($score, 4),
                'payload' => $payload,
            ];
        }

        usort($out, static function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        return $out;
    }

    private function assertConfigured(): void
    {
        if (!$this->isConfigured()) {
            error_log('[Qdrant] QDRANT_URL or QDRANT_API_KEY is not set');
            throw new QdrantException('Vector search is not configured.');
        }
        if (!function_exists('curl_init')) {
            error_log('[Qdrant] PHP curl extension missing');
            throw new QdrantException('Vector search is not configured.');
        }
    }

    /**
     * @param array<string, mixed>|null $body
     * @return array{status:int, json:array}
     */
    private function request(string $method, string $path, ?array $body = null, bool $allowError = false): array
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        if ($ch === false) {
            throw new QdrantException('Vector search is unavailable.');
        }
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'api-key: ' . $this->apiKey,
        ];
        $opts = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_HTTPHEADER => $headers,
        ];
        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        curl_setopt_array($ch, $opts);
        SecureCurl::applyCaBundle($ch);
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($errno !== 0 || $raw === false) {
            error_log('[Qdrant] ' . $method . ' ' . $path . ' curl ' . $errno . ' ' . $err);
            throw new QdrantException('Vector search is unavailable.');
        }

        $json = json_decode((string) $raw, true);
        if (!is_array($json)) {
            $json = [];
        }
        if (!$allowError && ($status < 200 || $status >= 300)) {
            $snippet = substr(preg_replace('/\s+/', ' ', (string) $raw) ?? '', 0, 300);
            error_log('[Qdrant] ' . $method . ' ' . $path . ' HTTP ' . $status . ' ' . $snippet);
            throw new QdrantException('Vector search is unavailable.');
        }
        return ['status' => $status, 'json' => $json];
    }
}
