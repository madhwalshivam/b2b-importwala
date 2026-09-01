<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

use App\Core\Database;

$db = Database::getInstance();

$tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
echo "TABLES:\n" . implode(", ", $tables) . "\n\n";

foreach (['products', 'product_images', 'product_variants', 'product_specifications', 'categories'] as $table) {
    if (in_array($table, $tables)) {
        echo "=== SCHEMA FOR $table ===\n";
        $cols = $db->query("DESCRIBE `$table`")->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo "{$col['Field']} | {$col['Type']} | Null:{$col['Null']} | Default:{$col['Default']}\n";
        }
        echo "\n";
    }
}
