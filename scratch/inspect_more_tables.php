<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
foreach (['admin_users', 'tiered_prices', 'product_images'] as $table) {
    echo "--- Columns for table: {$table} ---\n";
    try {
        $cols = $db->query("DESCRIBE {$table}")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo "  {$col['Field']} | {$col['Type']} | Null:{$col['Null']} | Key:{$col['Key']} | Default:{$col['Default']}\n";
        }
    } catch (\Throwable $e) {
        echo "Error describing {$table}: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
