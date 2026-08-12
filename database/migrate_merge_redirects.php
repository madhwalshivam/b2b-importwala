<?php
/**
 * Migration: Product Merges & 301 URL Redirects
 * Run: php database/migrate_merge_redirects.php
 */

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

echo "[1/4] Updating products table status ENUM to allow 'merged'...\n";
try {
    $db->exec("ALTER TABLE products MODIFY COLUMN status ENUM('active', 'inactive', 'merged') DEFAULT 'active'");
    echo "  ✓ Updated products.status column ENUM to include 'merged'\n";
} catch (Exception $e) {
    echo "  ⚠ Note: " . $e->getMessage() . "\n";
}

echo "\n[2/4] Creating url_redirects table...\n";
$exists = $db->query("SHOW TABLES LIKE 'url_redirects'")->fetchColumn();
if (!$exists) {
    $db->exec("
        CREATE TABLE url_redirects (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            old_slug VARCHAR(255) NOT NULL UNIQUE,
            target_url VARCHAR(255) NOT NULL,
            http_code INT DEFAULT 301,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_old_slug (old_slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "  ✓ Created url_redirects table\n";
} else {
    echo "  · url_redirects table already exists\n";
}

echo "\n[3/4] Creating product_merges table...\n";
$exists2 = $db->query("SHOW TABLES LIKE 'product_merges'")->fetchColumn();
if (!$exists2) {
    $db->exec("
        CREATE TABLE product_merges (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            parent_product_id INT UNSIGNED NOT NULL,
            child_product_ids JSON NOT NULL,
            backup_data JSON NOT NULL,
            merged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_parent (parent_product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "  ✓ Created product_merges table\n";
} else {
    echo "  · product_merges table already exists\n";
}

echo "\n[4/4] Done! Migration complete.\n";
