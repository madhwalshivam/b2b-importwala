<?php

define('ROOT_PATH', 'c:/xampp/htdocs/importwala');
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

echo "=== STOREFRONT PRODUCT DETAIL TEST ===\n";

$controller = new App\Controllers\Web\ProductDetailController();
ob_start();
try {
    $controller->show('royal-crystal-drop-earrings-mud-er-801');
    $html = ob_get_clean();
    echo "Rendered Product Detail HTML length: " . strlen($html) . " bytes\n";
    if (str_contains($html, 'Royal Crystal Drop Earrings') && str_contains($html, 'Royal Zircon') && str_contains($html, 'Emerald Green')) {
        echo "[PASS] Storefront product detail rendered bulk-imported product, specifications & variants flawlessly!\n";
    } else {
        echo "[FAIL] Storefront html missing expected content.\n";
    }
} catch (\Throwable $e) {
    ob_end_clean();
    echo "[FAIL] Exception during render: " . $e->getMessage() . "\n";
}
