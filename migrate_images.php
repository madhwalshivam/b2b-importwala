<?php
require 'app/Core/Database.php';

$db = \App\Core\Database::getInstance();

try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS image_mirror_map (
            id INT AUTO_INCREMENT PRIMARY KEY,
            source_url_hash VARCHAR(64) NOT NULL UNIQUE,
            source_url TEXT NOT NULL,
            r2_url VARCHAR(500) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created table image_mirror_map\n";
    
    // Add source_main_image to products
    $cols = $db->query("SHOW COLUMNS FROM products LIKE 'source_main_image'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE products ADD COLUMN source_main_image TEXT NULL AFTER main_image");
        echo "Added source_main_image to products\n";
    }

    // Add source_gallery_images to products
    $cols = $db->query("SHOW COLUMNS FROM products LIKE 'source_gallery_images'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE products ADD COLUMN source_gallery_images LONGTEXT NULL AFTER gallery_images");
        echo "Added source_gallery_images to products\n";
    }

    // Add source_image_url to product_variants
    $cols = $db->query("SHOW COLUMNS FROM product_variants LIKE 'source_image_url'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE product_variants ADD COLUMN source_image_url TEXT NULL AFTER image_url");
        echo "Added source_image_url to product_variants\n";
    }
    
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
