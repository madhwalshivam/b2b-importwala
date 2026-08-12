<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT id, name, slug, rating_avg, review_count FROM products LIMIT 10");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== PRODUCTS LIST ===\n";
print_r($products);
