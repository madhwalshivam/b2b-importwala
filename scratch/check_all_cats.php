<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

Database::init(require ROOT_PATH . '/config/database.php');
$db = Database::getInstance();

echo "--- FEATURED CATEGORIES ---\n";
$fcats = $db->query("SELECT * FROM featured_categories ORDER BY id ASC")->fetchAll();
foreach ($fcats as $fc) {
    echo "Featured Cat ID {$fc['id']}: {$fc['name']} (image: {$fc['image']})\n";
}

echo "\n--- MAIN CATEGORIES ---\n";
$mcats = $db->query("SELECT * FROM categories ORDER BY id ASC LIMIT 20")->fetchAll();
foreach ($mcats as $mc) {
    echo "Main Cat ID {$mc['id']}: {$mc['name']} (parent_id: {$mc['parent_id']}, image: {$mc['image']})\n";
}
