<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Models/FeaturedCategory.php';

use App\Core\Database;
use App\Models\FeaturedCategory;

Database::init(require ROOT_PATH . '/config/database.php');

$fcModel = new FeaturedCategory();
$cats = $fcModel->getActiveWithSubcategories();

echo "Featured Categories Data:\n";
foreach ($cats as $c) {
    echo "ID: {$c['id']} | Name: {$c['name']} | Image: " . ($c['image'] ?? 'NULL') . " | Custom Icon: " . ($c['custom_icon'] ?? 'NULL') . "\n";
}

echo "\nCategories Table Main Parent Categories:\n";
$db = Database::getInstance();
$stmt = $db->query("SELECT id, name, slug, image, custom_icon FROM categories WHERE parent_id IS NULL");
$mainCats = $stmt->fetchAll();
foreach ($mainCats as $mc) {
    echo "ID: {$mc['id']} | Name: {$mc['name']} | Image: {$mc['image']} | Custom Icon: {$mc['custom_icon']}\n";
}
