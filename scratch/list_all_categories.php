<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

Database::init(require ROOT_PATH . '/config/database.php');
$db = Database::getInstance();

$cats = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
foreach ($cats as $c) {
    echo "ID {$c['id']} | Parent: " . ($c['parent_id'] ?? 'NULL') . " | Name: {$c['name']} | Image: {$c['image']} | Status: {$c['status']}\n";
}
