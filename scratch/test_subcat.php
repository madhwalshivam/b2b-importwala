<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_PATH);
require_once ROOT_PATH . '/app/Helpers/Functions.php';
require_once ROOT_PATH . '/config/app.php';
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Services/CloudflareR2.php';
require_once ROOT_PATH . '/app/Models/Category.php';
require_once ROOT_PATH . '/app/Models/Subcategory.php';
require_once ROOT_PATH . '/app/Models/Brand.php';
require_once ROOT_PATH . '/app/Models/Factory.php';
require_once ROOT_PATH . '/app/Models/Product.php';
require_once ROOT_PATH . '/app/Models/ProductVariant.php';
require_once ROOT_PATH . '/app/Models/ProductSpecification.php';
require_once ROOT_PATH . '/app/Services/ImageMirrorService.php';
require_once ROOT_PATH . '/app/Services/FilterAttributeService.php';
require_once ROOT_PATH . '/app/Services/VariationService.php';
require_once ROOT_PATH . '/app/Services/BulkImportService.php';

$service = new \App\Services\BulkImportService();
// We'll simulate the resolution process
$reflection = new ReflectionClass($service);
$methodCat = $reflection->getMethod('resolveOrCreateCategory');
$methodCat->setAccessible(true);
$methodSubcat = $reflection->getMethod('resolveOrCreateSubcategory');
$methodSubcat->setAccessible(true);

$catName = "TestCat " . rand(1, 1000);
$subcatName = "TestSub " . rand(1, 1000);

echo "Creating category: $catName\n";
$catId = $methodCat->invoke($service, $catName);
echo "Category ID: " . var_export($catId, true) . "\n";

echo "Creating subcategory: $subcatName\n";
$subId = $methodSubcat->invoke($service, $catId, $subcatName);
echo "Subcategory ID: " . var_export($subId, true) . "\n";

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM subcategories WHERE id = " . (int)$subId);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Subcategory row: " . print_r($row, true) . "\n";

// Test if we can insert into products using this subcategory
$stmtInsert = $db->prepare("INSERT INTO products (name, slug, sku, category_id, subcategory_id) VALUES ('test_prod', 'test-prod-123', 'sku123', ?, ?)");
try {
    $stmtInsert->execute([$catId, $subId]);
    echo "Product inserted successfully.\n";
} catch (\Exception $e) {
    echo "Insert failed: " . $e->getMessage() . "\n";
}
