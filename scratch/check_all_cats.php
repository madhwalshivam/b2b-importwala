<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
echo "=== All categories rows in DB ===\n";
$allCats = $db->query("SELECT id, parent_id, name, slug, image, custom_icon, status, sort_order FROM categories ORDER BY id ASC")->fetchAll();
foreach ($allCats as $c) {
    echo "ID: {$c['id']} | Parent: {$c['parent_id']} | Name: {$c['name']} | Slug: {$c['slug']} | Status: {$c['status']} | Image: {$c['image']}\n";
}
