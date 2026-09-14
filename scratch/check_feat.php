<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
echo "--- featured_categories ---\n";
print_r($db->query("DESCRIBE featured_categories")->fetchAll());
echo "--- featured_subcategories ---\n";
print_r($db->query("DESCRIBE featured_subcategories")->fetchAll());
