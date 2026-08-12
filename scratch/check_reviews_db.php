<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SHOW TABLES LIKE '%review%'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "=== TABLES MATCHING REVIEW ===\n";
print_r($tables);

if (!empty($tables)) {
    foreach ($tables as $t) {
        echo "\n=== DESCRIBE {$t} ===\n";
        $stmt2 = $db->query("DESCRIBE `{$t}`");
        print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
    }
}
