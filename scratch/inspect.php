<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
echo "=== PRODUCT_IMAGES DESCRIBE ===\n";
$stmt = $db->query("DESCRIBE product_images");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
