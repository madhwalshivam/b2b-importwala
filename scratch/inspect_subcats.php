<?php
define('ROOT_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance();

echo "=== subcategories table ===\n";
$stmt = $db->query("SELECT s.*, c.name as cat_name FROM subcategories s JOIN categories c ON s.category_id = c.id");
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($subs as $s) {
    echo "ID: {$s['id']} | Category: {$s['cat_name']} ({$s['category_id']}) | Name: '{$s['name']}' | Slug: '{$s['slug']}' | Status: {$s['status']}\n";
}

echo "\n=== child categories in categories table (parent_id IS NOT NULL) ===\n";
$stmt2 = $db->query("SELECT c.*, p.name as parent_name FROM categories c JOIN categories p ON c.parent_id = p.id WHERE c.parent_id IS NOT NULL");
$subs2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($subs2 as $s) {
    echo "ID: {$s['id']} | Category: {$s['parent_name']} ({$s['parent_id']}) | Name: '{$s['name']}' | Slug: '{$s['slug']}' | Status: {$s['status']}\n";
}
