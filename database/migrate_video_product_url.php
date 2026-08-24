<?php
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $pdo = App\Core\Database::getInstance();
    
    // Check if column product_url exists
    $stmt = $pdo->query("SHOW COLUMNS FROM homepage_videos LIKE 'product_url'");
    $col = $stmt->fetch();
    
    if (!$col) {
        $pdo->exec("ALTER TABLE homepage_videos ADD COLUMN product_url VARCHAR(500) NULL AFTER description");
        echo "Column 'product_url' added successfully to homepage_videos table.\n";
    } else {
        echo "Column 'product_url' already exists in homepage_videos table.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
