<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
echo "=== CATEGORIES ===\n";
print_r($db->query("SELECT id, name, slug FROM categories")->fetchAll(PDO::FETCH_ASSOC));

echo "=== SUBCATEGORIES ===\n";
print_r($db->query("SELECT id, category_id, name, slug FROM subcategories")->fetchAll(PDO::FETCH_ASSOC));
