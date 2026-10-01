<?php
/**
 * Image Mirror Queue Worker (CLI)
 * Runs in the background to process image_mirror_queue.
 */
require_once __DIR__ . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(__DIR__);
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/app/Helpers/Functions.php';
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

use App\Services\ImageMirrorWorker;

echo "Starting Image Mirror Worker...\n";
$worker = new ImageMirrorWorker();

while (true) {
    $result = $worker->run(20, 40);
    
    if ($result['processed'] > 0) {
        echo date('Y-m-d H:i:s') . " - Processed: {$result['processed']}, Done: {$result['done']}, Failed: {$result['failed']}, Pending: {$result['pending']}\n";
    }

    if ($result['pending'] == 0) {
        // Sleep if queue is empty
        sleep(5);
    } else {
        // Small pause to prevent 100% CPU
        usleep(500000);
    }
}
