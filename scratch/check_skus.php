<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "=== SKUs Search ===\n";
$stmt = $db->prepare("SELECT id, sku, name, status FROM products WHERE sku LIKE 'IW-JW-%' ORDER BY id ASC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total IW-JW products: " . count($products) . "\n";
foreach ($products as $p) {
    echo "ID: {$p['id']} | SKU: {$p['sku']} | Name: {$p['name']} | Status: {$p['status']}\n";
}
