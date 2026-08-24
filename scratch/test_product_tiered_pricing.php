<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/Subcategory.php';
require_once __DIR__ . '/../app/Infrastructure/Cache/CacheInterface.php';
require_once __DIR__ . '/../app/Infrastructure/Cache/CacheManager.php';
require_once __DIR__ . '/../app/Services/BaseService.php';
require_once __DIR__ . '/../app/Services/TieredPricingService.php';

$prodModel = new App\Models\Product();
$catModel  = new App\Models\Category();
$subModel  = new App\Models\Subcategory();

$cats = $catModel->getActiveCategories();
$catId = $cats[0]['id'] ?? 1;

// 1. Insert product with subcategory and wholesale base pricing
$productId = $prodModel->insert([
    'name'           => 'ImportWala B2B Fast Charger Dock',
    'slug'           => 'importwala-b2b-fast-charger-dock-' . time(),
    'sku'            => 'IW-CHG-' . rand(100, 999),
    'category_id'    => $catId,
    'subcategory_id' => null,
    'price'          => 499.00,
    'sale_price'     => 449.00,
    'base_price'     => 350.00,
    'moq'            => 5,
    'stock'          => 200,
    'status'         => 'active'
]);

echo "Created Product ID: {$productId}\n";

// 2. Insert Wholesale Pricing Tiers
$db = App\Core\Database::getInstance();
$stmt = $db->prepare("INSERT INTO tiered_prices (product_id, min_qty, max_qty, unit_price) VALUES (?, ?, ?, ?)");
$stmt->execute([$productId, 5, 19, 350.00]);
$stmt->execute([$productId, 20, 99, 320.00]);
$stmt->execute([$productId, 100, null, 290.00]);

echo "Inserted 3 Wholesale Pricing Tiers.\n";

// 3. Verify Tiered Prices Query
$tiers = $db->query("SELECT * FROM tiered_prices WHERE product_id = {$productId} ORDER BY min_qty ASC")->fetchAll(PDO::FETCH_ASSOC);
echo "Fetched Tiers Count: " . count($tiers) . "\n";
foreach ($tiers as $t) {
    $maxStr = $t['max_qty'] !== null ? $t['max_qty'] : '∞';
    echo "  Tier: Qty {$t['min_qty']} - {$maxStr} => ₹{$t['unit_price']}\n";
}

// 4. Test TieredPricingService calculation
$pricingService = new App\Services\TieredPricingService();
$productData = $prodModel->find($productId);
$productData['tiered_prices'] = $tiers;

$res5 = $pricingService->calculateUnitPrice($productData, null, 5);
echo "Calc for Qty 5: Unit Price = ₹{$res5['effective_unit_price']}\n";

$res50 = $pricingService->calculateUnitPrice($productData, null, 50);
echo "Calc for Qty 50: Unit Price = ₹{$res50['effective_unit_price']}\n";

$res150 = $pricingService->calculateUnitPrice($productData, null, 150);
echo "Calc for Qty 150: Unit Price = ₹{$res150['effective_unit_price']}\n";

// 5. Clean up test product and tiers
$db->exec("DELETE FROM tiered_prices WHERE product_id = {$productId}");
$prodModel->delete($productId);
echo "Cleaned Up Test Product and Tiers Successfully.\n";
