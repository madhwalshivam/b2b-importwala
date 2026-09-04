<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

echo "=== Testing Clean SEO URLs & Routing ===\n\n";

// Helper tests
echo "1. Helper Functions:\n";
echo "   category_url('jewelry') => " . category_url('jewelry') . "\n";
echo "   subcategory_url('jewelry', 'earrings') => " . subcategory_url('jewelry', 'earrings') . "\n";
echo "   search_url('hoop & stud earrings') => " . search_url('hoop & stud earrings') . "\n";
echo "   product_url(['slug' => 'gold-hoop-earrings']) => " . product_url(['slug' => 'gold-hoop-earrings']) . "\n\n";

// Test Router setup
$request = new Request();
$response = new Response();
$router = new Router($request, $response);

require ROOT_PATH . '/routes/web.php';

echo "2. Testing Controller Handlers:\n";
$controller = new \App\Controllers\Web\CatalogController();

echo " - Calling category('jewelry')...\n";
ob_start();
try {
    $controller->category('jewelry');
    $out = ob_get_clean();
    echo "   [SUCCESS] Rendered category 'jewelry' (" . strlen($out) . " bytes)\n";
} catch (\Throwable $e) {
    ob_end_clean();
    echo "   [ERROR] " . $e->getMessage() . "\n";
}

echo " - Calling search('hoop-and-stud-earrings')...\n";
ob_start();
try {
    $controller->search('hoop-and-stud-earrings');
    $out = ob_get_clean();
    echo "   [SUCCESS] Rendered search 'hoop-and-stud-earrings' (" . strlen($out) . " bytes)\n";
} catch (\Throwable $e) {
    ob_end_clean();
    echo "   [ERROR] " . $e->getMessage() . "\n";
}

echo "\n=== Clean URL System Test Complete! ===\n";
