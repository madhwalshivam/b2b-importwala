<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "Database Tables (" . count($tables) . "):\n";
echo implode("\n", $tables) . "\n\n";

$targetTables = ['admins', 'users', 'hero_banners', 'banners', 'categories', 'sub_categories', 'subcategories', 'products'];
foreach ($targetTables as $table) {
    if (in_array($table, $tables)) {
        echo "--- Columns for table: {$table} ---\n";
        $cols = $db->query("DESCRIBE {$table}")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo "  {$col['Field']} | {$col['Type']} | Null:{$col['Null']} | Key:{$col['Key']} | Default:{$col['Default']}\n";
        }
        echo "\n";
    } else {
        echo "--- Table '{$table}' DOES NOT EXIST ---\n\n";
    }
}
