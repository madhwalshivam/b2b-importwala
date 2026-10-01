<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    
    // Check if columns exist first to make it safe
    $cols = $db->query("SHOW COLUMNS FROM rfq_requests")->fetchAll(PDO::FETCH_COLUMN);
    
    $alterQueries = [];
    if (!in_array('product_id', $cols)) $alterQueries[] = "ADD COLUMN product_id INT NULL";
    if (!in_array('product_sku', $cols)) $alterQueries[] = "ADD COLUMN product_sku VARCHAR(100) NULL";
    if (!in_array('variant_id', $cols)) $alterQueries[] = "ADD COLUMN variant_id INT NULL";
    if (!in_array('variant_label', $cols)) $alterQueries[] = "ADD COLUMN variant_label VARCHAR(255) NULL";
    if (!in_array('variant_sku', $cols)) $alterQueries[] = "ADD COLUMN variant_sku VARCHAR(100) NULL";
    if (!in_array('pricing_mode', $cols)) $alterQueries[] = "ADD COLUMN pricing_mode VARCHAR(20) NULL";
    if (!in_array('unit_price', $cols)) $alterQueries[] = "ADD COLUMN unit_price DECIMAL(10,2) NULL";
    if (!in_array('product_image', $cols)) $alterQueries[] = "ADD COLUMN product_image VARCHAR(500) NULL";
    
    if (!empty($alterQueries)) {
        $sql = "ALTER TABLE rfq_requests " . implode(', ', $alterQueries);
        $db->exec($sql);
        echo "Migration successful.\n";
    } else {
        echo "Columns already exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
