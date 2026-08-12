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

$controller = new App\Controllers\PageController();

$slugs = ['terms-and-conditions', 'privacy-policy', 'refund-policy', 'shipping-policy', 'cancellation-policy', 'about-us', 'contact-us'];

foreach ($slugs as $slug) {
    ob_start();
    $html = $controller->show($slug);
    $length = strlen($html);
    ob_end_clean();
    echo "Slug [{$slug}] rendered successfully ({$length} bytes)\n";
}
