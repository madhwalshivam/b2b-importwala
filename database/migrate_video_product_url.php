<?php
$config = require __DIR__ . '/../config/database.php';

try {
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        $config['host'],
        $config['port'],
        $config['dbname'],
        $config['charset']
    );
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
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
