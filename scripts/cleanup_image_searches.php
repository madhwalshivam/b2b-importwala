<?php
/**
 * Cron-safe cleanup of expired image_searches rows + files.
 *
 * Usage:
 *   php scripts/cleanup_image_searches.php
 *
 * Suggested cron (daily):
 *   15 3 * * * cd /path/to/importwala && php scripts/cleanup_image_searches.php >> storage/logs/image_search_cleanup.log 2>&1
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_PATH);
require_once ROOT_PATH . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

@set_time_limit(0);

echo '[' . date('Y-m-d H:i:s') . "] Image search cleanup starting...\n";

try {
    $store = new \App\Services\ImageSearchStoreService();
    $stats = $store->cleanupExpired();
    echo "Deleted rows: {$stats['rows']}, files: {$stats['files']}\n";
    echo "DONE\n";
    exit(0);
} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    exit(1);
}
