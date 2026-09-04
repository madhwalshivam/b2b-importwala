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
    if (strncmp('Lib\\', $class, 4) === 0) {
        $relPath = str_replace('\\', '/', substr($class, 4));
        $file = ROOT_PATH . '/lib/' . $relPath . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

require_once ROOT_PATH . '/app/Helpers/Functions.php';
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

// Mock server environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/shop';
$_SERVER['HTTP_HOST'] = 'localhost';

echo "=== Testing SearchService with Filters ===\n";
$searchService = new \App\Services\SearchService();

$resAll = $searchService->search('', [], 24, 0);
echo "Total items all: " . ($resAll['total'] ?? 0) . "\n";
echo "Fetched items count: " . count($resAll['items'] ?? []) . "\n";

$resPrice = $searchService->search('', ['min_price' => 500, 'max_price' => 2000], 24, 0);
echo "Filtered by price 500-2000 count: " . ($resPrice['total'] ?? 0) . "\n";

$resMoq = $searchService->search('', ['min_moq' => 1, 'max_moq' => 10], 24, 0);
echo "Filtered by MOQ 1-10 count: " . ($resMoq['total'] ?? 0) . "\n";

echo "Stats facets:\n";
print_r($resAll['facets']['stats'] ?? []);

echo "\n=== Testing CatalogController render ===\n";
$_GET['page'] = 1;
$_GET['sort'] = 'price_asc';
$_GET['per_page'] = 12;

$controller = new \App\Controllers\Web\CatalogController();
ob_start();
try {
    $controller->index();
    $output = ob_get_clean();
    echo "Controller output length: " . strlen($output) . " bytes\n";
    if (strpos($output, 'shop-page-container') !== false) {
        echo "SUCCESS: shop-page-container found in rendered HTML!\n";
    } else {
        echo "WARNING: shop-page-container not found in output.\n";
    }
} catch (\Throwable $e) {
    ob_end_clean();
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
