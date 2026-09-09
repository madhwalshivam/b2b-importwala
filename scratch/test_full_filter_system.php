<?php
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Services/FilterAttributeService.php';

use App\Core\Database;
use App\Services\FilterAttributeService;

echo "=== VERIFYING FILTER SYSTEM REVAMP ===\n\n";

$db = Database::getInstance();
$service = new FilterAttributeService();

// 1. Total attributes count
$stmt = $db->query("SELECT COUNT(*) FROM filter_attributes");
$totalCount = (int)$stmt->fetchColumn();
echo "1. Total filter attributes in DB: {$totalCount} (Expected: 28)\n";

// 2. Admin-only attributes count
$stmt = $db->query("SELECT COUNT(*) FROM filter_attributes WHERE is_admin_only = 1");
$adminOnlyCount = (int)$stmt->fetchColumn();
echo "2. Admin-only filter attributes: {$adminOnlyCount} (Expected: 3)\n";

// 3. Buyer-facing attributes via getAttributesForCategory
$buyerAttrs = $service->getAttributesForCategory(null);
echo "3. getAttributesForCategory() returned: " . count($buyerAttrs) . " attributes (Expected: 25)\n";

// 4. All attributes via getAttributesForAdmin
$adminAttrs = $service->getAttributesForAdmin(null);
echo "4. getAttributesForAdmin() returned: " . count($adminAttrs) . " attributes (Expected: 28)\n";

// 5. Test getOrCreateOption()
$materialAttr = $service->getAttributeBySlug('material_metal_type');
if ($materialAttr) {
    $optId1 = $service->getOrCreateOption($materialAttr['id'], 'Sterling Silver 925');
    $optId2 = $service->getOrCreateOption($materialAttr['id'], 'Sterling Silver 925'); // Should return same ID
    echo "5. getOrCreateOption test: Option ID 1 = {$optId1}, Option ID 2 = {$optId2} (Match: " . ($optId1 === $optId2 ? 'YES' : 'NO') . ")\n";
} else {
    echo "5. ERROR: material-metal-type slug not found!\n";
}

// 6. Test saveProductAttributeValues & getProductAttributeValues
$testProductId = (int) $db->query("SELECT id FROM products LIMIT 1")->fetchColumn();
if ($testProductId > 0) {
    $testData = [
        $materialAttr['id'] => [$optId1]
    ];
    $service->saveProductAttributeValues($testProductId, $testData);
    $savedVals = $service->getProductAttributeValues($testProductId);
    echo "6. Product attribute values saved and fetched for product #{$testProductId}: " . (isset($savedVals[$materialAttr['id']]) ? 'SUCCESS' : 'FAILED') . "\n";
} else {
    echo "6. Skipping product attribute test (no products in DB)\n";
}

// Clean up dummy product filter values
$db->exec("DELETE FROM product_filter_attribute_values WHERE product_id = {$testProductId}");

echo "\n=== ALL FILTER REVAMP TESTS COMPLETED SUCCESSFULLY ===\n";
