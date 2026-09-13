<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "=== SKUs Search (IW-JW-101 to IW-JW-147) ===\n";
$stmt = $db->prepare("SELECT id, sku, name, status FROM products WHERE sku >= 'IW-JW-101' AND sku <= 'IW-JW-147' ORDER BY id ASC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total matching products: " . count($products) . "\n";
foreach ($products as $p) {
    echo "ID: {$p['id']} | SKU: {$p['sku']} | Name: {$p['name']}\n";
}
