<?php

define('ROOT_PATH', 'c:/xampp/htdocs/importwala');
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

use App\Services\BulkImportService;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductSpecification;

echo "=== BULK PRODUCT IMPORT VERIFICATION TEST ===\n\n";

// Clean up previous test runs
$db = App\Core\Database::getInstance();
$stmtClean = $db->prepare("SELECT id FROM products WHERE sku = 'MUD-ER-801'");
$stmtClean->execute();
$prevP = $stmtClean->fetch();
if ($prevP) {
    (new Product())->delete($prevP['id']);
    $db->prepare("DELETE FROM product_variants WHERE variant_code LIKE 'MUD-ER-801%'")->execute();
}

$service = new BulkImportService();

// 1. Test Template Generation
echo "Step 1: Testing Template Generation...\n";
$templatePath = $service->generateTemplate('xlsx');
echo "Generated template at: {$templatePath} (Size: " . filesize($templatePath) . " bytes)\n";

if (!file_exists($templatePath) || filesize($templatePath) < 1000) {
    echo "[FAIL] Template generation failed!\n";
    exit(1);
}
echo "[PASS] Template generated successfully.\n\n";

// 2. Test Parsing & Validation
echo "Step 2: Testing Parsing & Validation...\n";
$parseResult = $service->parseAndValidate($templatePath);

if (!$parseResult['success']) {
    echo "[FAIL] Parsing failed: " . ($parseResult['error'] ?? 'Unknown error') . "\n";
    exit(1);
}

echo "Summary:\n";
print_r($parseResult['summary']);

$products = $parseResult['products'];
echo "Parsed " . count($products) . " product group(s):\n";
foreach ($products as $p) {
    echo "- SKU: {$p['product_sku']} | Name: {$p['name']} | Status: {$p['status']} | Variants: " . count($p['variants']) . "\n";
    if (!empty($p['errors'])) {
        echo "  Errors: " . implode('; ', $p['errors']) . "\n";
    }
    if (!empty($p['warnings'])) {
        echo "  Warnings: " . implode('; ', $p['warnings']) . "\n";
    }
}

if ($parseResult['summary']['valid_products'] + $parseResult['summary']['warning_products'] === 0) {
    echo "[FAIL] No valid products parsed from sample template!\n";
    exit(1);
}
echo "[PASS] Parsing & Validation passed.\n\n";

// 3. Test Database Commit (Pass 1 - Insert)
echo "Step 3: Testing Database Commit (Pass 1 - Insert)...\n";
$commitResult1 = $service->commitImport($products);
echo "Commit Pass 1 Result:\n";
print_r($commitResult1);

if (!$commitResult1['success'] || $commitResult1['created_products'] === 0) {
    echo "[FAIL] Database commit Pass 1 failed!\n";
    exit(1);
}
echo "[PASS] Database commit Pass 1 successful.\n\n";

// 4. Test Specifications Persistence
echo "Step 4: Verifying Product Specifications in DB...\n";
$db = App\Core\Database::getInstance();
$stmtP = $db->prepare("SELECT id FROM products WHERE sku = ?");
$stmtP->execute(['MUD-ER-801']);
$prodDb = $stmtP->fetch();

if (!$prodDb) {
    echo "[FAIL] Product 'MUD-ER-801' not found in DB after commit!\n";
    exit(1);
}

$productId = (int)$prodDb['id'];
$specModel = new ProductSpecification();
$specs = $specModel->getByProduct($productId);

echo "Fetched " . count($specs) . " specifications for Product ID {$productId}:\n";
foreach ($specs as $s) {
    echo "  - {$s['spec_key']}: {$s['spec_value']}\n";
}

if (empty($specs)) {
    echo "[FAIL] No specifications stored for imported product!\n";
    exit(1);
}
echo "[PASS] Product Specifications stored and verified.\n\n";

// 5. Test Idempotency (Pass 2 - Re-import Update)
echo "Step 5: Testing Idempotency (Pass 2 - Re-import Update)...\n";
$commitResult2 = $service->commitImport($products);
echo "Commit Pass 2 Result:\n";
print_r($commitResult2);

if (!$commitResult2['success'] || $commitResult2['updated_products'] === 0 || $commitResult2['created_products'] !== 0) {
    echo "[FAIL] Idempotency test failed! Re-import should update, not create duplicates.\n";
    exit(1);
}
echo "[PASS] Idempotency test passed (Updated existing SKU without creating duplicates).\n\n";

// Cleanup temp template file
@unlink($templatePath);

echo "=== ALL BULK PRODUCT IMPORT TESTS PASSED SUCCESSFULLY! ===\n";
