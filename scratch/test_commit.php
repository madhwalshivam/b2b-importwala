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

$parseResult = $service->parseAndValidate($csvPath);
if ($parseResult['success']) {
    $commitResult = $service->commitImport($parseResult['products']);
    echo "Commit Success: " . ($commitResult['success'] ? 'YES' : 'NO') . "\n";
    if ($commitResult['success']) {
        echo "Created Products: " . $commitResult['created_products'] . "\n";
        echo "Updated Products: " . $commitResult['updated_products'] . "\n";
        echo "Created Variants: " . $commitResult['created_variants'] . "\n";
    } else {
        echo "Commit Error: " . $commitResult['error'] . "\n";
    }
}
