<?php
/**
 * CLI: Rebuild / backfill product_image_features index.
 *
 * Usage:
 *   php scripts/backfill_image_features.php
 *   php scripts/backfill_image_features.php --force
 *   php scripts/backfill_image_features.php --batch=50
 *
 * Safe to re-run. Skips already-indexed image URLs unless --force.
 */

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/app/Helpers/Functions.php';
if (file_exists(ROOT_PATH . '/app/Core/EnvLoader.php')) {
    require_once ROOT_PATH . '/app/Core/EnvLoader.php';
    \App\Core\EnvLoader::load(ROOT_PATH);
}

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

if (PHP_SAPI !== 'cli') {
    // Allow admin-triggered HTTP with a simple key for Hostinger cron if needed
    $key = $_GET['key'] ?? '';
    $expected = getenv('VISUAL_SEARCH_BACKFILL_KEY') ?: '';
    if ($expected === '' || !hash_equals($expected, $key)) {
        http_response_code(403);
        echo "CLI only (or provide valid ?key=)\n";
        exit(1);
    }
    header('Content-Type: text/plain');
}

@set_time_limit(0);
@ini_set('memory_limit', '512M');

$force = in_array('--force', $argv ?? [], true) || !empty($_GET['force']);
$batch = 50;
$onlyProductId = 0;
foreach ($argv ?? [] as $arg) {
    if (preg_match('/^--batch=(\d+)$/', $arg, $m)) {
        $batch = max(10, min(100, (int) $m[1]));
    }
    if (preg_match('/^--product=(\d+)$/', $arg, $m)) {
        $onlyProductId = (int) $m[1];
    }
}
if (!empty($_GET['batch'])) {
    $batch = max(10, min(100, (int) $_GET['batch']));
}
if (!empty($_GET['product'])) {
    $onlyProductId = (int) $_GET['product'];
}

echo "=== ImportWala Image Search Backfill ===\n";
echo 'GD: ' . (extension_loaded('gd') ? 'YES' : 'NO') . "\n";
echo 'Imagick: ' . (extension_loaded('imagick') ? 'YES' : 'NO') . "\n";
echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . "\n";
echo 'post_max_size: ' . ini_get('post_max_size') . "\n";
echo 'force=' . ($force ? 'yes' : 'no') . " batch={$batch}\n\n";

if (!extension_loaded('gd')) {
    echo "FATAL: PHP GD extension is not enabled. Enable extension=gd in php.ini and restart Apache.\n";
    exit(1);
}

// Ensure table exists
$sqlFile = ROOT_PATH . '/database/migrations/product_image_features.sql';
try {
    $db = \App\Core\Database::getInstance();
    $db->query('SELECT 1 FROM product_image_features LIMIT 1');
} catch (\Throwable $e) {
    if (is_file($sqlFile)) {
        echo "Creating product_image_features table...\n";
        $db = \App\Core\Database::getInstance();
        $db->exec(file_get_contents($sqlFile));
    } else {
        echo "FATAL: migration SQL missing and table does not exist.\n";
        exit(1);
    }
}

$service = new \App\Services\VisualSearchService();

$started = microtime(true);

if ($onlyProductId > 0) {
    echo "Indexing single product_id={$onlyProductId} force=" . ($force ? 'yes' : 'no') . "\n";
    $ok = $service->indexProduct($onlyProductId, $force);
    $elapsed = round(microtime(true) - $started, 1);
    $db = \App\Core\Database::getInstance();
    $stmt = $db->prepare('SELECT COUNT(*) FROM product_image_features WHERE product_id = ?');
    $stmt->execute([$onlyProductId]);
    $cnt = (int) $stmt->fetchColumn();
    echo ($ok ? "OK" : "FAIL") . " in {$elapsed}s — feature rows for product: {$cnt}\n";
    exit($ok ? 0 : 1);
}

$stats = $service->indexAllProducts($force, $batch, function ($done, $total, $success, $fail, $skipped) {
    echo sprintf(
        "[%s] Progress %d/%d | newly_indexed=%d skipped=%d failed=%d\n",
        date('H:i:s'),
        $done,
        $total,
        $success,
        $skipped,
        $fail
    );
    if (function_exists('flush')) {
        @ob_flush();
        @flush();
    }
});

$elapsed = round(microtime(true) - $started, 1);
echo "\n=== DONE in {$elapsed}s ===\n";
echo "Products scanned: {$stats['total']}\n";
echo "Newly indexed products: {$stats['indexed']}\n";
echo "Skipped (already had features): {$stats['skipped']}\n";
echo "Failed: {$stats['failed']}\n";
echo "Total feature rows: {$stats['total_images_indexed']}\n";

$diag = $service->getIndexStats();
echo 'Products with at least 1 feature: ' . $diag['products_indexed'] . ' / ' . $diag['active_products'] . "\n";
exit(0);
