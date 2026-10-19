<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Build a product payload, embed its main image, and upsert/delete the Qdrant point.
 */
class ProductEmbeddingIndexer
{
    private const MAX_ATTEMPTS = 5;

    private PDO $db;
    private EmbeddingService $embeddings;
    private QdrantClient $qdrant;

    public function __construct(?EmbeddingService $embeddings = null, ?QdrantClient $qdrant = null)
    {
        EmbeddingSchema::ensure();
        $this->db = Database::getInstance();
        $this->embeddings = $embeddings ?? new EmbeddingService();
        $this->qdrant = $qdrant ?? new QdrantClient();
    }

    /**
     * Embed one product's current main image, or remove its vector when it
     * should not be searchable.
     */
    public function syncProduct(int $productId): bool
    {
        $product = $this->loadProduct($productId);
        if (!$product) {
            $this->qdrant->delete($productId);
            return true;
        }

        $status = (string) ($product['status'] ?? '');
        $image = trim((string) ($product['main_image'] ?? ''));
        if ($status !== 'active' || $image === '' || $this->isPlaceholder($image)) {
            $this->qdrant->delete($productId);
            $this->mark($productId, 'skipped', null, 'Not searchable (inactive, missing image, or placeholder).');
            return true;
        }

        $vector = $this->embeddings->embedRemote($image);
        $payload = $this->payload($product);
        $this->qdrant->upsert($productId, $vector, $payload);
        $this->mark($productId, 'done', $image, null);
        return true;
    }

    public function deleteVector(int $productId): void
    {
        $this->qdrant->delete($productId);
        if ($this->loadProduct($productId)) {
            $this->mark($productId, 'skipped', null, null);
        }
    }

    /**
     * Claim the next backfill batch. Pending queue rows are left for the queue worker.
     *
     * @return int[]
     */
    public function claimBackfillBatch(int $batch, bool $force): array
    {
        $batch = max(1, min(20, $batch));
        if ($force) {
            $stmt = $this->db->query("
                SELECT id FROM products
                WHERE status = 'active'
                  AND main_image IS NOT NULL AND main_image <> ''
                ORDER BY id ASC
                LIMIT {$batch}
            ");
            $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
        } else {
            $stmt = $this->db->prepare("
                SELECT id FROM products
                WHERE status = 'active'
                  AND main_image IS NOT NULL AND main_image <> ''
                  AND (
                        embedding_status IS NULL
                        OR embedding_status = ''
                        OR embedding_status = 'processing'
                        OR (embedding_status = 'failed' AND embedding_attempts < :maxAttempts)
                        OR (
                            embedding_status = 'done'
                            AND embedding_source_url IS NOT NULL
                            AND embedding_source_url <> main_image
                        )
                  )
                ORDER BY id ASC
                LIMIT {$batch}
            ");
            $stmt->execute(['maxAttempts' => self::MAX_ATTEMPTS]);
            $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
        }

        $claimed = [];
        $upd = $this->db->prepare("
            UPDATE products
            SET embedding_status = 'processing', embedding_error = NULL
            WHERE id = ?
        ");
        foreach ($ids as $id) {
            $upd->execute([$id]);
            $claimed[] = $id;
        }
        return $claimed;
    }

    public function markFailed(int $productId, string $error): void
    {
        $stmt = $this->db->prepare("
            UPDATE products
            SET embedding_status = 'failed',
                embedding_attempts = embedding_attempts + 1,
                embedding_error = :err
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $productId,
            'err' => substr($error, 0, 500),
        ]);
    }

    public function remainingCount(): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM products
            WHERE status = 'active'
              AND main_image IS NOT NULL AND main_image <> ''
              AND (
                    embedding_status IS NULL
                    OR embedding_status = ''
                    OR embedding_status = 'pending'
                    OR embedding_status = 'processing'
                    OR (embedding_status = 'failed' AND embedding_attempts < ?)
              )
        ");
        $stmt->execute([self::MAX_ATTEMPTS]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadProduct(int $productId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, category_id, subcategory_id, main_image, status
            FROM products
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $product
     * @return array<string, mixed>
     */
    private function payload(array $product): array
    {
        $attrs = $this->attributeLabels((int) $product['id']);
        return [
            'product_id' => (int) $product['id'],
            'category_id' => (int) ($product['category_id'] ?? 0),
            'subcategory_id' => (int) ($product['subcategory_id'] ?? 0),
            'jewellery_type' => $attrs['jewellery_type'] ?? '',
            'gender' => $attrs['gender'] ?? '',
            'metal_color' => $attrs['metal_color'] ?? '',
            'material' => $attrs['material'] ?? '',
            'status' => 'active',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function attributeLabels(int $productId): array
    {
        $wanted = ['jewellery_type', 'gender', 'metal_color', 'material'];
        $out = array_fill_keys($wanted, '');
        try {
            $stmt = $this->db->prepare("
                SELECT fa.slug, COALESCE(NULLIF(fao.value, ''), NULLIF(pfav.value, '')) AS label
                FROM product_filter_attribute_values pfav
                INNER JOIN filter_attributes fa ON fa.id = pfav.attribute_id
                LEFT JOIN filter_attribute_options fao ON fao.id = pfav.option_id
                WHERE pfav.product_id = ?
                  AND fa.slug IN ('jewellery_type', 'gender', 'metal_color', 'material')
            ");
            $stmt->execute([$productId]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $slug = (string) ($row['slug'] ?? '');
                $label = trim((string) ($row['label'] ?? ''));
                if ($label === '' || !isset($out[$slug])) {
                    continue;
                }
                $out[$slug] = $out[$slug] === '' ? $label : ($out[$slug] . ', ' . $label);
            }
        } catch (\Throwable $e) {
            error_log('[Embedding] attribute payload: ' . $e->getMessage());
        }
        return $out;
    }

    private function mark(int $productId, string $status, ?string $sourceUrl, ?string $error): void
    {
        $stmt = $this->db->prepare("
            UPDATE products
            SET embedding_status = :status,
                embedded_at = CASE WHEN :statusDone = 'done' THEN NOW() ELSE embedded_at END,
                embedding_error = :err,
                embedding_source_url = CASE WHEN :sourceSet = 1 THEN :source ELSE embedding_source_url END
            WHERE id = :id
        ");
        $stmt->execute([
            'status' => $status,
            'statusDone' => $status,
            'err' => $error !== null ? substr($error, 0, 500) : null,
            'sourceSet' => $sourceUrl !== null ? 1 : 0,
            'source' => $sourceUrl !== null ? substr($sourceUrl, 0, 500) : null,
            'id' => $productId,
        ]);
    }

    private function isPlaceholder(string $image): bool
    {
        return stripos($image, 'placeholder') !== false;
    }

    public static function log(string $line): void
    {
        $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        $dir = $root . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $stamp = date('Y-m-d H:i:s');
        @file_put_contents($dir . '/embedding_index.log', "[{$stamp}] {$line}\n", FILE_APPEND);
        if (PHP_SAPI === 'cli') {
            echo $line . "\n";
        }
    }
}
