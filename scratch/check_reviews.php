<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM reviews");
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== ALL REVIEWS IN DATABASE ===\n";
print_r($all);
