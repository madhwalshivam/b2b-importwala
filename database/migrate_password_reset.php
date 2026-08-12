<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    $tables = ['admin_users', 'users', 'customers'];
    
    foreach ($tables as $table) {
        echo "Checking table `$table`..." . PHP_EOL;
        
        $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE 'reset_token'");
        if (!$stmt->fetch()) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `reset_token` VARCHAR(255) NULL AFTER `password`");
            echo "Added `reset_token` column to `$table`." . PHP_EOL;
        } else {
            echo "`reset_token` already exists in `$table`." . PHP_EOL;
        }

        $stmt2 = $db->query("SHOW COLUMNS FROM `$table` LIKE 'reset_token_expires_at'");
        if (!$stmt2->fetch()) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `reset_token_expires_at` DATETIME NULL AFTER `reset_token`");
            echo "Added `reset_token_expires_at` column to `$table`." . PHP_EOL;
        } else {
            echo "`reset_token_expires_at` already exists in `$table`." . PHP_EOL;
        }
    }
    
    echo "Migration completed successfully." . PHP_EOL;
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . PHP_EOL;
}
