<?php
define('ROOT_PATH', __DIR__ . '/..');

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

use App\Services\VisualSearchService;

$service = new VisualSearchService();
$results = $service->searchByCategoryPreset('all', 6);

echo "Visual Search Results:\n";
print_r($results);
