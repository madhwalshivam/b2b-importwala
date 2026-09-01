<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT id, label, url, parent_id FROM nav_links");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
