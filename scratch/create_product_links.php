<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();

$sql = "
CREATE TABLE IF NOT EXISTS product_links (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    linked_product_id INT UNSIGNED NOT NULL,
    link_type VARCHAR(50) DEFAULT 'variant',
    label VARCHAR(100) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_link (product_id, linked_product_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (linked_product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$db->exec($sql);
echo "Table `product_links` created or verified successfully!\n";
