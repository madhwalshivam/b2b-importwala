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

echo "=== TESTING SVG STAR HELPER ===\n";
echo "Full 5.0 Star SVG Length: " . strlen(render_star_rating(5.0)) . "\n";
echo "Partial 4.3 Star SVG Length: " . strlen(render_star_rating(4.3)) . "\n";
echo "Half 4.5 Star SVG Length: " . strlen(render_star_rating(4.5)) . "\n";
echo "Empty 0.0 Star SVG Length: " . strlen(render_star_rating(0.0)) . "\n";

echo "\n=== TESTING PRODUCT DETAIL PAGE RENDER ===\n";
$pController = new App\Controllers\ProductDetailController();
ob_start();
$htmlProduct = $pController->show('mudsor-ev-charger-wall-mount-dock');
$lenProduct = strlen($htmlProduct);
ob_end_clean();
echo "Product detail page rendered successfully ({$lenProduct} bytes).\n";

echo "\n=== TESTING SHOP PAGE RENDER ===\n";
$shopController = new App\Controllers\ShopController();
ob_start();
$htmlShop = $shopController->index();
$lenShop = strlen($htmlShop);
ob_end_clean();
echo "Shop page rendered successfully ({$lenShop} bytes).\n";
