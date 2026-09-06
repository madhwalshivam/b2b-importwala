<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Model.php';
require __DIR__ . '/../app/Models/Product.php';
require __DIR__ . '/../app/Models/ProductVariant.php';
require __DIR__ . '/../app/Models/ProductSpecification.php';
require __DIR__ . '/../app/Models/Category.php';
require __DIR__ . '/../app/Models/Subcategory.php';
require __DIR__ . '/../app/Models/Brand.php';
require __DIR__ . '/../app/Services/BulkImportService.php';

use App\Services\BulkImportService;

$service = new BulkImportService();
$csvPath = $service->generateTemplate('csv');
$xlsxPath = $service->generateTemplate('xlsx');

echo "Generated CSV Template: " . $csvPath . " (Size: " . filesize($csvPath) . " bytes)\n";
echo "Generated XLSX Template: " . $xlsxPath . " (Size: " . filesize($xlsxPath) . " bytes)\n";

// Test Parsing the generated CSV template
$parseResult = $service->parseAndValidate($csvPath);
echo "Parse result success: " . ($parseResult['success'] ? 'YES' : 'NO') . "\n";
if ($parseResult['success']) {
    echo "Total Products parsed: " . $parseResult['summary']['total_products'] . "\n";
    echo "Total Variants parsed: " . $parseResult['summary']['total_variants'] . "\n";
} else {
    echo "Parse Error: " . $parseResult['error'] . "\n";
}
