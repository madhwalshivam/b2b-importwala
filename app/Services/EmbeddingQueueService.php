<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Incremental embedding jobs. Inserts are cheap; the cron worker does the API calls.
 */
class EmbeddingQueueService
{
    private PDO $db;

    public function __construct()
    {
        EmbeddingSchema::ensure();
        $this->db = Database::getInstance();
    }

    public function enqueueUpsert(int $productId): void
    {
        if ($productId <= 0) {
            return;
        }
        $this->cancelPending($productId);
        $this->insertJob($productId, 'upsert');
        try {
            $this->db->prepare("UPDATE products SET embedding_status = 'pending', embedding_error = NULL WHERE id = ?")
                ->execute([$productId]);
        } catch (\Throwable $e) {
            error_log('[EmbeddingQueue] pending flag: ' . $e->getMessage());
        }
    }

    public function enqueueDelete(int $productId): void
    {
        if ($productId <= 0) {
            return;
        }
        $this->cancelPending($productId);
        $this->insertJob($productId, 'delete');
    }

    /**
     * Queue a delete and try to drop the Qdrant point immediately.
     * Used by single-product delete. Bulk delete should call enqueueDelete only.
     */
    public function removeProductVector(int $productId): void
    {
        if ($productId <= 0) {
            return;
        }
        $this->enqueueDelete($productId);
        try {
            (new ProductEmbeddingIndexer())->deleteVector($productId);
            $this->db->prepare("
                UPDATE embedding_jobs
                SET status = 'done', last_error = NULL, updated_at = NOW()
                WHERE product_id = ? AND action = 'delete' AND status IN ('pending','processing')
            ")->execute([$productId]);
        } catch (\Throwable $e) {
            error_log('[EmbeddingQueue] immediate delete failed for ' . $productId . ': ' . $e->getMessage());
        }
    }

    /**
     * @return array{processed:int, done:int, failed:int}
     */
    public function processBatch(int $batch): array
    {
        $batch = max(1, min(20, $batch));
        $this->releaseStuck();

        $stmt = $this->db->query("
            SELECT id, product_id, action, attempts
            FROM embedding_jobs
            WHERE status = 'pending'
            ORDER BY id ASC
            LIMIT {$batch}
        ");
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $indexer = new ProductEmbeddingIndexer();
        $stats = ['processed' => 0, 'done' => 0, 'failed' => 0];
        $claim = $this->db->prepare("
            UPDATE embedding_jobs
            SET status = 'processing', attempts = attempts + 1, updated_at = NOW()
            WHERE id = ? AND status = 'pending'
        ");
        $done = $this->db->prepare("UPDATE embedding_jobs SET status = 'done', last_error = NULL, updated_at = NOW() WHERE id = ?");
        $fail = $this->db->prepare("
            UPDATE embedding_jobs
            SET status = IF(attempts >= 5, 'failed', 'pending'), last_error = ?, updated_at = NOW()
            WHERE id = ?
        ");

        foreach ($jobs as $job) {
            $jobId = (int) $job['id'];
            $productId = (int) $job['product_id'];
            $claim->execute([$jobId]);
            if ($claim->rowCount() < 1) {
                continue;
            }
            $stats['processed']++;
            try {
                if (($job['action'] ?? '') === 'delete') {
                    $indexer->deleteVector($productId);
                } else {
                    $indexer->syncProduct($productId);
                }
                $done->execute([$jobId]);
                $stats['done']++;
                ProductEmbeddingIndexer::log("queue job {$jobId} product {$productId} {$job['action']} ok");
            } catch (\Throwable $e) {
                $msg = substr($e->getMessage(), 0, 500);
                $fail->execute([$msg, $jobId]);
                $stats['failed']++;
                if (($job['action'] ?? '') !== 'delete') {
                    try {
                        $indexer->markFailed($productId, $msg);
                    } catch (\Throwable $ignored) {
                    }
                }
                ProductEmbeddingIndexer::log("queue job {$jobId} product {$productId} failed: {$msg}");
            }
        }

        return $stats;
    }

    private function cancelPending(int $productId): void
    {
        $this->db->prepare("
            UPDATE embedding_jobs
            SET status = 'done', last_error = 'superseded', updated_at = NOW()
            WHERE product_id = ? AND status = 'pending'
        ")->execute([$productId]);
    }

    private function insertJob(int $productId, string $action): void
    {
        $this->db->prepare("
            INSERT INTO embedding_jobs (product_id, action, status, attempts, created_at, updated_at)
            VALUES (?, ?, 'pending', 0, NOW(), NOW())
        ")->execute([$productId, $action]);
    }

    private function releaseStuck(): void
    {
        $this->db->exec("
            UPDATE embedding_jobs
            SET status = 'pending', updated_at = NOW()
            WHERE status = 'processing'
              AND updated_at < (NOW() - INTERVAL 15 MINUTE)
        ");
    }
}
