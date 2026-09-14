<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "=== Top-level Categories (parent_id IS NULL OR parent_id = 0) ===\n";
$mainCats = $db->query("SELECT id, name, slug, image, custom_icon, status, sort_order FROM categories WHERE (parent_id IS NULL OR parent_id = 0) ORDER BY sort_order ASC, name ASC")->fetchAll();
print_r($mainCats);

echo "\n=== All Subcategories (from subcategories table OR child categories) ===\n";
$subs = $db->query("SELECT id, category_id, name, slug, image, status, sort_order FROM subcategories ORDER BY sort_order ASC, name ASC")->fetchAll();
print_r($subs);
