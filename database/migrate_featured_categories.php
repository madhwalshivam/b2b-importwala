<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();

    echo "Running migration for Featured Categories...\n";

    // 1. Featured Categories (Tabs)
    $db->exec("CREATE TABLE IF NOT EXISTS `featured_categories` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(120) NOT NULL UNIQUE,
        `sort_order` INT NOT NULL DEFAULT 0,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_feat_cat_sort` (`is_active`, `sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Featured Subcategories (Cards per Tab)
    $db->exec("CREATE TABLE IF NOT EXISTS `featured_subcategories` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `featured_category_id` INT UNSIGNED NOT NULL,
        `name` VARCHAR(150) NOT NULL,
        `slug` VARCHAR(180) NOT NULL,
        `image` VARCHAR(255) NOT NULL,
        `link_url` VARCHAR(255) NOT NULL,
        `sort_order` INT NOT NULL DEFAULT 0,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`featured_category_id`) REFERENCES `featured_categories` (`id`) ON DELETE CASCADE,
        INDEX `idx_feat_subcat_sort` (`featured_category_id`, `is_active`, `sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "Featured Categories tables created successfully!\n";
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}
