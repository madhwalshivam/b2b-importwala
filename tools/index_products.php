<?php

/**
 * Backfill CLIP embeddings for active products (main image only).
 *
 *   php tools/index_products.php
 *   php tools/index_products.php --batch=20
 *   php tools/index_products.php --product=123
 *   php tools/index_products.php --force
 *
 * Resumable: rows with embedding_status=done and the same main image are skipped.
 * Failed rows are retried until embedding_attempts reaches 5.
 * Each run processes at most 20 products. Schedule it every 1–2 minutes until
 * the log reports 0 remaining, then leave tools/process_embedding_queue.php
 * on cron for new and edited products.
 */

require __DIR__ . '/cli_bootstrap.php';

use App\Services\EmbeddingSchema;
use App\Services\EmbeddingService;
use App\Services\ProductEmbeddingIndexer;
use App\Services\QdrantClient;

$batch = 20;
$onlyProductId = 0;
$force = in_array('--force', $argv ?? [], true) || !empty($_GET['force']);
foreach ($argv ?? [] as $arg) {
    if (preg_match('/^--batch=(\d+)$/', $arg, $m)) {
        $batch = (int) $m[1];
    }
    if (preg_match('/^--product=(\d+)$/', $arg, $m)) {
        $onlyProductId = (int) $m[1];
    }
}
if (!empty($_GET['batch'])) {
    $batch = (int) $_GET['batch'];
}
if (!empty($_GET['product'])) {
    $onlyProductId = (int) $_GET['product'];
}
$batch = max(1, min(20, $batch));

echo "=== ImportWale image embedding backfill ===\n";

$embeddings = new EmbeddingService();
$qdrant = new QdrantClient();
if (!$embeddings->isConfigured() || !$qdrant->isConfigured()) {
    ProductEmbeddingIndexer::log('FATAL: set EMBEDDING_API_KEY, QDRANT_URL, and QDRANT_API_KEY in .env');
    exit(1);
}

try {
    EmbeddingSchema::ensure();
    $qdrant->ensureCollection($embeddings->dimensions());
} catch (\Throwable $e) {
    ProductEmbeddingIndexer::log('FATAL: ' . $e->getMessage());
    exit(1);
}

$indexer = new ProductEmbeddingIndexer($embeddings, $qdrant);

if ($onlyProductId > 0) {
    echo "Indexing product {$onlyProductId}\n";
    try {
        $indexer->syncProduct($onlyProductId);
        ProductEmbeddingIndexer::log("product {$onlyProductId} ok");
        exit(0);
    } catch (\Throwable $e) {
        $indexer->markFailed($onlyProductId, $e->getMessage());
        ProductEmbeddingIndexer::log("product {$onlyProductId} failed: " . $e->getMessage());
        exit(1);
    }
}

$ids = $indexer->claimBackfillBatch($batch, $force);
echo 'Claimed ' . count($ids) . " product(s).\n";
$ok = 0;
$failed = 0;
foreach ($ids as $id) {
    try {
        $indexer->syncProduct($id);
        $ok++;
        ProductEmbeddingIndexer::log("product {$id} ok");
    } catch (\Throwable $e) {
        $failed++;
        $indexer->markFailed($id, $e->getMessage());
        ProductEmbeddingIndexer::log("product {$id} failed: " . $e->getMessage());
    }
}

$left = $indexer->remainingCount();
ProductEmbeddingIndexer::log("batch done ok={$ok} failed={$failed} remaining={$left}");
exit(0);
