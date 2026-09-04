<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Core/Helpers.php';

// Instantiate CatalogController and capture output of section('featured-products')
$_GET = [
    'category_id' => '',
    'sort' => 'relevance',
];

ob_start();
$controller = new \App\Controllers\Web\CatalogController();
try {
    $controller->section('featured-products');
    $html = ob_get_clean();

    echo "--- VERIFICATION CHECKS FOR /section/featured-products ---\n";
    echo "Total HTML Length: " . strlen($html) . " bytes\n";
    echo "Has Shop Page Wrapper: " . (strpos($html, 'shop-page-wrapper') !== false ? "YES" : "NO") . "\n";
    echo "Has Homepage Section Banner: " . (strpos($html, 'Homepage Section') !== false ? "YES" : "NO") . "\n";
    echo "Has Filter Toggle Button: " . (strpos($html, 'filterToggleBtn') !== false ? "YES" : "NO") . "\n";
    echo "Has Filter Sidebar: " . (strpos($html, 'shopSidebar') !== false ? "YES" : "NO") . "\n";
    echo "Has Categories Accordion: " . (strpos($html, 'Categories') !== false ? "YES" : "NO") . "\n";
    echo "Has Price Filter: " . (strpos($html, 'Price Range') !== false || strpos($html, 'min_price') !== false ? "YES" : "NO") . "\n";
    echo "Has MOQ Filter: " . (strpos($html, 'MOQ') !== false ? "YES" : "NO") . "\n";
    echo "Has Product Grid: " . (strpos($html, 'shopProductGrid') !== false ? "YES" : "NO") . "\n";
    echo "Has Form Action URL for Section: " . (strpos($html, 'action="http://localhost/importwala/section/featured-products"') !== false ? "YES" : "NO") . "\n";
} catch (\Throwable $e) {
    ob_end_clean();
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
