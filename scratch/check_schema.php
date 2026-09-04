<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();
echo "Total products all status: " . $db->query("SELECT COUNT(*) FROM products")->fetchColumn() . "\n";
echo "Active products: " . $db->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn() . "\n";

$cats = $db->query("SELECT id, name, slug FROM categories WHERE status='active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
echo "\nActive Categories (" . count($cats) . "):\n";
foreach ($cats as $c) {
    $subCount = $db->query("SELECT COUNT(*) FROM subcategories WHERE category_id={$c['id']} AND status='active'")->fetchColumn();
    $prodCount = $db->query("SELECT COUNT(*) FROM products WHERE category_id={$c['id']} AND status='active'")->fetchColumn();
    echo " - [ID {$c['id']}] {$c['name']} (slug: {$c['slug']}) -> {$subCount} subcats, {$prodCount} products\n";
}
