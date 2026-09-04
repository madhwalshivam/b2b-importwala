<?php
define('ROOT_PATH', dirname(__DIR__));
spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

use App\Core\Database;
use App\Services\VisualSearchService;

$db = Database::getInstance();
$service = new VisualSearchService();

// Copy Product 19's main image to Product 20
$p19Img = $db->query("SELECT main_image FROM products WHERE id = 19")->fetchColumn();
$db->prepare("UPDATE products SET main_image = ? WHERE id = 20")->execute([$p19Img]);

$service->indexProduct(20);

$res = $service->searchByProductId(19, 8);
print_r($res);
