<?php

/**
 * Process the embedding queue (create, edit, import, delete, deactivate).
 *
 *   php tools/process_embedding_queue.php
 *   php tools/process_embedding_queue.php --batch=20
 *
 * Safe to run every minute from Hostinger cron. Each run handles at most 20 jobs.
 */

require __DIR__ . '/cli_bootstrap.php';

use App\Services\EmbeddingSchema;
use App\Services\EmbeddingQueueService;
use App\Services\EmbeddingService;
use App\Services\ProductEmbeddingIndexer;
use App\Services\QdrantClient;

$batch = 20;
foreach ($argv ?? [] as $arg) {
    if (preg_match('/^--batch=(\d+)$/', $arg, $m)) {
        $batch = (int) $m[1];
    }
}
if (!empty($_GET['batch'])) {
    $batch = (int) $_GET['batch'];
}
$batch = max(1, min(20, $batch));

$embeddings = new EmbeddingService();
$qdrant = new QdrantClient();
if (!$embeddings->isConfigured() || !$qdrant->isConfigured()) {
    ProductEmbeddingIndexer::log('FATAL: set EMBEDDING_API_KEY, QDRANT_URL, and QDRANT_API_KEY in .env');
    exit(1);
}

try {
    EmbeddingSchema::ensure();
    $qdrant->ensureCollection($embeddings->dimensions());
    $stats = (new EmbeddingQueueService())->processBatch($batch);
} catch (\Throwable $e) {
    ProductEmbeddingIndexer::log('FATAL: ' . $e->getMessage());
    exit(1);
}

ProductEmbeddingIndexer::log(
    'queue batch processed=' . $stats['processed'] . ' done=' . $stats['done'] . ' failed=' . $stats['failed']
);
exit(0);
