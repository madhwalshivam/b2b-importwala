<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/Subcategory.php';

$catModel = new App\Models\Category();
$subModel = new App\Models\Subcategory();

// 1. Fetch existing main category
$cats = $catModel->getActiveCategories();
$mainCatId = $cats[0]['id'] ?? 1;

echo "Using Main Category ID: {$mainCatId}\n";

// 2. Create subcategory
$subId = $subModel->createSubcategory([
    'category_id' => $mainCatId,
    'name' => 'Test Charger Docks',
    'slug' => 'test-charger-docks',
    'sort_order' => 1,
    'status' => 'active'
]);

echo "Created Subcategory ID: {$subId}\n";

// 3. Test Slug Uniqueness Check
$slugExists = $subModel->slugExists('test-charger-docks');
echo "Slug 'test-charger-docks' exists: " . ($slugExists ? 'YES' : 'NO') . "\n";

// 4. Test Update
$subModel->updateSubcategory($subId, [
    'category_id' => $mainCatId,
    'name' => 'Updated Charger Docks',
    'slug' => 'test-charger-docks',
    'sort_order' => 2,
    'status' => 'active'
]);

$fetched = $subModel->findWithCategory($subId);
echo "Fetched Subcategory Name: {$fetched['name']}, Category Name: {$fetched['category_name']}\n";

// 5. Clean up
$subModel->deleteSubcategory($subId);
echo "Subcategory Cleaned Up Successfully.\n";
