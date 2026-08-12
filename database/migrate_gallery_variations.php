<?php
/**
 * Migration: Multi-Image Gallery + Product Variations
 * Run: php database/migrate_gallery_variations.php
 */

// Bootstrap
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});
require_once __DIR__ . '/../app/Helpers/Functions.php';

$db = App\Core\Database::getInstance();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "[1/5] Checking product_images table...\n";
$cols = $db->query("SHOW COLUMNS FROM product_images")->fetchAll(PDO::FETCH_COLUMN);

// Add image_url if missing
if (!in_array('image_url', $cols)) {
    $db->exec("ALTER TABLE product_images ADD COLUMN image_url VARCHAR(500) NULL AFTER product_id");
    echo "  ✓ Added image_url column\n";
    // Copy existing image_path to image_url
    if (in_array('image_path', $cols)) {
        $db->exec("UPDATE product_images SET image_url = image_path WHERE image_url IS NULL");
        echo "  ✓ Copied image_path → image_url\n";
    }
} else {
    echo "  · image_url already exists\n";
}

if (!in_array('variation_value_id', $cols)) {
    $db->exec("ALTER TABLE product_images ADD COLUMN variation_value_id INT UNSIGNED NULL DEFAULT NULL AFTER image_url");
    echo "  ✓ Added variation_value_id column\n";
} else {
    echo "  · variation_value_id already exists\n";
}

if (!in_array('sort_order', $cols)) {
    $db->exec("ALTER TABLE product_images ADD COLUMN sort_order INT DEFAULT 0 AFTER variation_value_id");
    echo "  ✓ Added sort_order column\n";
} else {
    echo "  · sort_order already exists\n";
}

if (!in_array('is_primary', $cols)) {
    $db->exec("ALTER TABLE product_images ADD COLUMN is_primary TINYINT(1) DEFAULT 0 AFTER sort_order");
    echo "  ✓ Added is_primary column\n";
} else {
    echo "  · is_primary already exists\n";
}

// Set first image per product as primary
$db->exec("
    UPDATE product_images pi
    INNER JOIN (
        SELECT MIN(id) as min_id, product_id
        FROM product_images
        GROUP BY product_id
    ) t ON pi.id = t.min_id
    SET pi.is_primary = 1
    WHERE pi.is_primary = 0
");
echo "  ✓ Set primary flags on existing images\n";

echo "\n[2/5] Creating product_variation_types table...\n";
$existing = $db->query("SHOW TABLES LIKE 'product_variation_types'")->fetchColumn();
if (!$existing) {
    $db->exec("
        CREATE TABLE product_variation_types (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id INT UNSIGNED NOT NULL,
            name VARCHAR(100) NOT NULL COMMENT 'e.g. Color, Size',
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "  ✓ Created product_variation_types\n";
} else {
    echo "  · product_variation_types already exists\n";
}

echo "\n[3/5] Creating product_variation_values table...\n";
$existing2 = $db->query("SHOW TABLES LIKE 'product_variation_values'")->fetchColumn();
if (!$existing2) {
    $db->exec("
        CREATE TABLE product_variation_values (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            variation_type_id INT UNSIGNED NOT NULL,
            value VARCHAR(100) NOT NULL COMMENT 'e.g. Black, Large',
            color_hex VARCHAR(7) NULL COMMENT 'optional hex e.g. #000000',
            price_diff DECIMAL(10,2) DEFAULT 0 COMMENT '+/- price adjustment',
            stock_qty INT DEFAULT 0,
            sku VARCHAR(100) NULL,
            is_deleted TINYINT(1) DEFAULT 0 COMMENT 'soft delete - hide from frontend',
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (variation_type_id) REFERENCES product_variation_types(id) ON DELETE CASCADE,
            INDEX idx_type (variation_type_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "  ✓ Created product_variation_values\n";
} else {
    echo "  · product_variation_values already exists\n";
}

echo "\n[4/5] Adding FK on product_images.variation_value_id...\n";
try {
    $fkCheck = $db->query("
        SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_NAME = 'product_images'
        AND COLUMN_NAME = 'variation_value_id'
        AND REFERENCED_TABLE_NAME = 'product_variation_values'
    ")->fetchColumn();
    if (!$fkCheck) {
        $db->exec("
            ALTER TABLE product_images
            ADD CONSTRAINT fk_pi_variation_value
            FOREIGN KEY (variation_value_id)
            REFERENCES product_variation_values(id)
            ON DELETE SET NULL
        ");
        echo "  ✓ FK added\n";
    } else {
        echo "  · FK already exists\n";
    }
} catch (Exception $e) {
    echo "  ⚠ FK skipped: " . $e->getMessage() . "\n";
}

echo "\n[5/5] Done! Migration complete.\n";
echo "\nFinal product_images schema:\n";
foreach ($db->query("SHOW COLUMNS FROM product_images")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  {$r['Field']} | {$r['Type']} | NULL:{$r['Null']}\n";
}
