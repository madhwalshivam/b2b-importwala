<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    // Check if column already exists
    $cols = $db->query("SHOW COLUMNS FROM featured_categories LIKE 'image'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE featured_categories ADD COLUMN image VARCHAR(255) NULL AFTER slug");
        echo "Column 'image' added to featured_categories table successfully!\n";
    } else {
        echo "Column 'image' already exists in featured_categories table.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
