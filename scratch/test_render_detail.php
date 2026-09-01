<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

require_once ROOT_PATH . '/app/Helpers/Functions.php';
require_once ROOT_PATH . '/config/app.php';

try {
    \App\Infrastructure\Cache\CacheManager::getInstance()->flush();
} catch (\Throwable $e) {}

$controller = new App\Controllers\Web\ProductDetailController();
ob_start();
try {
    $controller->show('mudsor-protective-phone-case');
    $html = ob_get_clean();
    echo "SUCCESS! Rendered Product Detail Page Length: " . strlen($html) . " bytes\n";
    if (str_contains($html, 'Show all specifications')) {
        echo "✅ FOUND 'Show all specifications' button!\n";
    } else {
        echo "❌ 'Show all specifications' button NOT found in HTML!\n";
    }
    if (str_contains($html, 'max-h-[235px]') && str_contains($html, 'overflow-y-auto')) {
        echo "✅ FOUND Scrollable Variant Container (max-h-[235px] overflow-y-auto)!\n";
    } else {
        echo "❌ Scrollable Variant Container NOT found!\n";
    }
    if (str_contains($html, 'updateVariantQty')) {
        echo "✅ FOUND Quantity Stepper Controls (updateVariantQty)!\n";
    }
} catch (\Throwable $e) {
    ob_end_clean();
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
