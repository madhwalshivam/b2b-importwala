<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "=== CMS_PAGES LIST ===\n";
$stmt = $db->query("SELECT id, slug, title FROM cms_pages");
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($pages);

echo "\n=== SETTINGS TABLE PHONE/CONTACT KEYS ===\n";
$stmt2 = $db->query("SELECT * FROM settings WHERE setting_key LIKE '%phone%' OR setting_key LIKE '%contact%' OR setting_value LIKE '%92177%' OR setting_value LIKE '%8800%'");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
