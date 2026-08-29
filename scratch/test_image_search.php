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
$imgPath = __DIR__ . '/../public/uploads/products/sunglasses.jpg';

if (file_exists($imgPath)) {
    echo "Testing Visual Search by uploaded image file: {$imgPath}\n";
    $results = $service->searchByImage($imgPath, null, 6);
    print_r($results);
} else {
    echo "Sample image file not found at {$imgPath}\n";
}
