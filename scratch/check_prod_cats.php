<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

Database::init(require ROOT_PATH . '/config/database.php');
$db = Database::getInstance();

echo "--- PRODUCTS CATEGORY_ID ---\n";
$prods = $db->query("SELECT id, name, category_id FROM products LIMIT 10")->fetchAll();
foreach ($prods as $p) {
    echo "Product ID {$p['id']}: {$p['name']} -> category_id: " . ($p['category_id'] ?? 'NULL') . "\n";
}

echo "--- PRODUCT_CATEGORIES PIVOT ---\n";
$pcats = $db->query("SELECT * FROM product_categories LIMIT 10")->fetchAll();
foreach ($pcats as $pc) {
    echo "Product ID {$pc['product_id']} -> Category ID {$pc['category_id']}\n";
}
