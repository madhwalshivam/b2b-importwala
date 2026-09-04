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

$urlsToTest = [
    '/shop',
    '/products',
    '/shop?min_price=100&max_price=1000&sort=price_asc',
    '/shop?min_moq=5&max_moq=25&per_page=12&page=1',
    '/category/earrings',
    '/shop?min_price=999999', // Zero results test case
];

echo "=== TESTING ALL SHOP URL COMBINATIONS ===\n\n";

foreach ($urlsToTest as $testUrl) {
    echo "Testing URL: {$testUrl}\n";
    $parsed = parse_url($testUrl);
    $_GET = [];
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $_GET);
    }
    
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $testUrl;
    $_SERVER['HTTP_HOST'] = 'localhost';

    $controller = new \App\Controllers\Web\CatalogController();
    ob_start();
    try {
        if (strpos($parsed['path'], '/category/') === 0) {
            $parts = explode('/', trim($parsed['path'], '/'));
            $slug = $parts[1] ?? 'catalog';
            $controller->category($slug);
        } else {
            $controller->index();
        }
        $html = ob_get_clean();
        
        $hasContainer = strpos($html, 'shop-page-container') !== false;
        $hasBreadcrumb = strpos($html, 'shop-breadcrumb') !== false;
        $hasSidebar = strpos($html, 'shopSidebar') !== false;
        $hasToolbar = strpos($html, 'shop-toolbar') !== false;
        $hasProductGrid = strpos($html, 'shopProductGrid') !== false;
        $hasEmptyState = strpos($html, 'shop-empty-state') !== false;
        $hasPagination = strpos($html, 'shop-pagination-wrapper') !== false;

        echo "  - Render Status: OK (" . strlen($html) . " bytes)\n";
        echo "  - Shop Container: " . ($hasContainer ? 'YES' : 'NO') . "\n";
        echo "  - Breadcrumbs: " . ($hasBreadcrumb ? 'YES' : 'NO') . "\n";
        echo "  - Sidebar Filters: " . ($hasSidebar ? 'YES' : 'NO') . "\n";
        echo "  - Top Toolbar: " . ($hasToolbar ? 'YES' : 'NO') . "\n";
        echo "  - Product Grid: " . ($hasProductGrid ? 'YES' : 'NO') . "\n";
        echo "  - Zero State Rendered: " . ($hasEmptyState ? 'YES' : 'NO') . "\n";
        echo "  - Pagination Controls: " . ($hasPagination ? 'YES' : 'NO') . "\n\n";
    } catch (\Throwable $e) {
        ob_end_clean();
        echo "  - Render Status: FAILED (" . $e->getMessage() . ")\n\n";
    }
}
