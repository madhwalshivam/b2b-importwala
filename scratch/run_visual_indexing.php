<?php
define('ROOT_PATH', dirname(__DIR__));

// Register App autoloader
spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Load helpers if available
if (file_exists(__DIR__ . '/../app/Core/helpers.php')) {
    require_once __DIR__ . '/../app/Core/helpers.php';
}

use App\Services\VisualSearchService;

echo "Starting Visual Search Indexing for all catalog products...\n";

$service = new VisualSearchService();
$res = $service->indexAllProducts(true);

echo "Indexing completed successfully!\n";
echo "Total products processed: {$res['total']}\n";
echo "Successfully indexed: {$res['indexed']}\n";
echo "Failed / Missing Image: {$res['failed']}\n";
