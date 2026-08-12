<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/ecommerce/product/mudsor-ev-charger-wall-mount-dock';
$_SERVER['SCRIPT_NAME'] = '/ecommerce/index.php';

$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require_once $file;
});

require_once __DIR__ . '/../app/Helpers/Functions.php';

$controller = new App\Controllers\ProductDetailController();
$html = $controller->show('mudsor-ev-charger-wall-mount-dock');

echo "=== GALLERY WRAPPER HTML ===\n";
$pos = strpos($html, 'product-gallery-wrapper');
if ($pos !== false) {
    echo substr($html, $pos - 20, 1500);
} else {
    echo "NOT FOUND IN HTML!\n";
}
