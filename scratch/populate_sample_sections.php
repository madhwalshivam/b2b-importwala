<?php
define('ROOT_PATH', dirname(__DIR__));
date_default_timezone_set('Asia/Kolkata');

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once ROOT_PATH . '/app/Helpers/Functions.php';

$productModel = new App\Models\Product();
$sectionModel = new App\Models\HomeSection();

$activeProducts = $productModel->getAllActiveProducts();
echo "Total active products in database: " . count($activeProducts) . "\n";

if (!empty($activeProducts)) {
    $pIds = array_column($activeProducts, 'id');
    echo "Sample product IDs: " . implode(',', array_slice($pIds, 0, 10)) . "\n";

    // Populate Featured Products (ID 5), Best Sellers (ID 6), New Arrivals (ID 11), Featured Deals (ID 10), Flash Sale (ID 12)
    $sampleSlice1 = array_slice($pIds, 0, 5);
    $sampleSlice2 = array_slice($pIds, 1, 5);
    $sampleSlice3 = array_slice($pIds, 2, 5);

    $sectionsToPopulate = [
        5 => $sampleSlice1, // Featured Products
        6 => $sampleSlice2, // Best Sellers
        11 => $sampleSlice3, // New Arrivals
        10 => $sampleSlice1, // Featured Deals
        12 => $sampleSlice2  // Flash Sale
    ];

    foreach ($sectionsToPopulate as $secId => $ids) {
        $sectionModel->saveSectionProducts($secId, $ids);
        $saved = $sectionModel->getSectionProducts($secId, null, false);
        echo "Section ID {$secId}: saved " . count($saved) . " products.\n";
    }
}
echo "Populate test complete!\n";
