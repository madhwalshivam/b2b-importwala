<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/ecommerce/';
$_SERVER['REQUEST_METHOD'] = 'GET';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

require_once __DIR__ . '/../app/Helpers/Functions.php';

$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;

echo "=== TESTING REVIEW CONTROLLER ===\n";
App\Controllers\ReviewController::recalculateProductRating(1);
echo "Recalculated product 1 rating successfully.\n";

$pController = new App\Controllers\ProductDetailController();
ob_start();
$html = $pController->show('mudsor-ev-charger-wall-mount-dock');
$len = strlen($html);
ob_end_clean();
echo "Product detail page rendered successfully ({$len} bytes).\n";
