<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
echo "--- featured_categories data ---\n";
print_r($db->query("SELECT * FROM featured_categories")->fetchAll());
echo "--- featured_subcategories data ---\n";
print_r($db->query("SELECT * FROM featured_subcategories")->fetchAll());
