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

$sectionModel = new App\Models\HomeSection();
$rawSections = $sectionModel->getAllSections();

echo "Testing index controller logic:\n";
foreach ($rawSections as &$sec) {
    $secId = (int)$sec['id'];
    $sec['products'] = $sectionModel->getSectionProducts($secId, null, false);
    echo "Section ID {$secId} '{$sec['title']}': " . count($sec['products']) . " products found\n";
}
unset($sec);

echo "\nTesting saveSectionProducts on Section ID 1:\n";
$initialProducts = $sectionModel->getSectionProducts(1, null, false);
$initialIds = array_column($initialProducts, 'id');
echo "Initial product IDs for Section 1: " . implode(',', $initialIds) . "\n";

// Save new product list
$testIds = [1, 2, 3];
$sectionModel->saveSectionProducts(1, $testIds);
$updatedProducts = $sectionModel->getSectionProducts(1, null, false);
$updatedIds = array_column($updatedProducts, 'id');
echo "Updated product IDs for Section 1: " . implode(',', $updatedIds) . "\n";

// Restore initial product list
$sectionModel->saveSectionProducts(1, $initialIds);
$restoredProducts = $sectionModel->getSectionProducts(1, null, false);
$restoredIds = array_column($restoredProducts, 'id');
echo "Restored product IDs for Section 1: " . implode(',', $restoredIds) . "\n";

echo "\nTest completed successfully!\n";
