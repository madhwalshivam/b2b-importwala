<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
echo "=== CMS_PAGES DESCRIBE ===\n";
$stmt = $db->query("DESCRIBE cms_pages");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
