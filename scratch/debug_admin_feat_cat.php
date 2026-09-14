<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

require_once __DIR__ . '/../app/Helpers/Functions.php';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

$controller = new App\Controllers\Admin\FeaturedCategoryController();
$html = $controller->index();
echo "Rendered length: " . strlen($html) . "\n";
if (str_contains($html, "Homepage Category Grids Manager")) {
    echo "SUCCESS: Admin page header present!\n";
}
if (str_contains($html, "Section 1: Main Category Tiles")) {
    echo "SUCCESS: Section 1 Table card present!\n";
}
if (str_contains($html, "Section 2: Subcategory Icon Grid")) {
    echo "SUCCESS: Section 2 Table card present!\n";
}
