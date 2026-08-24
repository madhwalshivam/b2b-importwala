<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

Database::init(require ROOT_PATH . '/config/database.php');
$db = Database::getInstance();

echo "--- CATEGORIES COLUMNS ---\n";
$cols = $db->query("SHOW COLUMNS FROM categories")->fetchAll();
foreach ($cols as $c) {
    echo "Field: {$c['Field']} | Type: {$c['Type']} | Null: {$c['Null']}\n";
}

echo "\n--- ALL MAIN CATEGORIES & SUBCATEGORIES ---\n";
$cats = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
foreach ($cats as $c) {
    echo "ID [{$c['id']}]: {$c['name']} (parent_id: " . ($c['parent_id'] ?? 'NULL') . ", image_url: " . ($c['image_url'] ?? $c['image'] ?? 'N/A') . ")\n";
}
