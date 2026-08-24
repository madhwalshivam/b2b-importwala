<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Models/Category.php';
require_once ROOT_PATH . '/app/Models/Subcategory.php';

use App\Core\Database;
use App\Models\Category;
use App\Models\Subcategory;

Database::init(require ROOT_PATH . '/config/database.php');

$catModel = new Category();
$subModel = new Subcategory();

$mainCats = $catModel->getAllWithDetails();
echo "Total Main Categories (Category Management): " . count($mainCats) . "\n";
foreach (array_slice($mainCats, 0, 5) as $mc) {
    echo "  - [ID {$mc['id']}] {$mc['name']} (slug: {$mc['slug']})\n";
}

echo "\n";
$subs = $subModel->getAllWithCategory();
echo "Total Subcategories (Sub-category Manager): " . count($subs) . "\n";
foreach (array_slice($subs, 0, 5) as $s) {
    echo "  - [ID {$s['id']}] {$s['name']} (Parent: {$s['category_name']}, slug: {$s['slug']})\n";
}
