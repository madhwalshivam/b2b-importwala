<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Helpers/Functions.php';
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "=== CREATING FILTER SYSTEM TABLES ===\n";

// 1. filter_attributes
$sql1 = "CREATE TABLE IF NOT EXISTS `filter_attributes` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `type` ENUM('single_select', 'multi_select', 'range') NOT NULL DEFAULT 'multi_select',
    `is_global` TINYINT(1) NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT(11) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$db->exec($sql1);
echo " - Table `filter_attributes` created/verified.\n";

// 2. filter_attribute_options
$sql2 = "CREATE TABLE IF NOT EXISTS `filter_attribute_options` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `attribute_id` INT(10) UNSIGNED NOT NULL,
    `value` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `sort_order` INT(11) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_attribute_id` (`attribute_id`),
    CONSTRAINT `fk_fao_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `filter_attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$db->exec($sql2);
echo " - Table `filter_attribute_options` created/verified.\n";

// 3. filter_attribute_categories
$sql3 = "CREATE TABLE IF NOT EXISTS `filter_attribute_categories` (
    `attribute_id` INT(10) UNSIGNED NOT NULL,
    `category_id` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`attribute_id`, `category_id`),
    KEY `idx_category_id` (`category_id`),
    CONSTRAINT `fk_fac_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `filter_attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$db->exec($sql3);
echo " - Table `filter_attribute_categories` created/verified.\n";

// 4. product_filter_attribute_values
$sql4 = "CREATE TABLE IF NOT EXISTS `product_filter_attribute_values` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT(10) UNSIGNED NOT NULL,
    `attribute_id` INT(10) UNSIGNED NOT NULL,
    `option_id` INT(10) UNSIGNED DEFAULT NULL,
    `value` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_attr_opt` (`attribute_id`, `option_id`),
    CONSTRAINT `fk_pfav_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pfav_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `filter_attributes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pfav_option` FOREIGN KEY (`option_id`) REFERENCES `filter_attribute_options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$db->exec($sql4);
echo " - Table `product_filter_attribute_values` created/verified.\n";
