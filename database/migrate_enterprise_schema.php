<?php
// Migration script for Enterprise Production Schema

require_once __DIR__ . '/../app/Core/Database.php';

try {
    $pdo = App\Core\Database::getInstance();

    echo "Running Enterprise Database Schema Migration...\n";

    $sql = <<<SQL
SET FOREIGN_KEY_CHECKS = 0;

-- 1. USERS & ACCESS CONTROL (UUID Sharding-ready)
CREATE TABLE IF NOT EXISTS `users` (
  `id` VARCHAR(36) NOT NULL COMMENT 'ULID/UUID for multi-region DB sharding',
  `company_name` VARCHAR(150) NULL,
  `tax_id` VARCHAR(50) NULL COMMENT 'VAT / GST Number for wholesale verification',
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `user_type` ENUM('customer', 'wholesaler', 'admin', 'superadmin') NOT NULL DEFAULT 'customer',
  `status` ENUM('pending', 'active', 'suspended') NOT NULL DEFAULT 'active',
  `currency_code` VARCHAR(3) NOT NULL DEFAULT 'USD',
  `country_code` VARCHAR(2) NOT NULL DEFAULT 'US',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_users_email` (`email`),
  KEY `idx_users_status_type` (`status`, `user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CATEGORIES
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `parent_id` INT UNSIGNED NULL,
  `slug` VARCHAR(191) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `image_url` VARCHAR(255) NULL,
  `icon_svg` TEXT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_categories_slug` (`slug`),
  KEY `idx_categories_parent_sort` (`parent_id`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PRODUCTS & VARIATIONS
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `sku` VARCHAR(64) NOT NULL,
  `slug` VARCHAR(191) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `short_description` TEXT NULL,
  `full_description` LONGTEXT NULL,
  `main_image` VARCHAR(255) NOT NULL,
  `gallery_images` JSON NULL,
  `video_url` VARCHAR(255) NULL,
  `base_price` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `moq` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Minimum Order Quantity',
  `weight_kg` DECIMAL(8, 4) NOT NULL DEFAULT 0.0000,
  `status` ENUM('draft', 'active', 'archived') NOT NULL DEFAULT 'active',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_new_arrival` TINYINT(1) NOT NULL DEFAULT 0,
  `is_best_seller` TINYINT(1) NOT NULL DEFAULT 0,
  `views_count` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `sales_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `rating_avg` DECIMAL(3, 2) NOT NULL DEFAULT 5.00,
  `rating_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_products_sku` (`sku`),
  UNIQUE KEY `idx_products_slug` (`slug`),
  KEY `idx_products_cat_status` (`category_id`, `status`),
  KEY `idx_products_featured` (`status`, `is_featured`, `sales_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_variations` (
  `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `sku` VARCHAR(64) NOT NULL,
  `color_name` VARCHAR(50) NULL,
  `color_code` VARCHAR(10) NULL,
  `size_name` VARCHAR(50) NULL,
  `material_name` VARCHAR(50) NULL,
  `image_url` VARCHAR(255) NULL,
  `price_offset` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `stock_qty` INT NOT NULL DEFAULT 0,
  `reserved_qty` INT NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_variations_sku` (`sku`),
  KEY `idx_variations_product` (`product_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TIERED PRICES
CREATE TABLE IF NOT EXISTS `tiered_prices` (
  `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `min_qty` INT UNSIGNED NOT NULL,
  `max_qty` INT UNSIGNED NULL,
  `unit_price` DECIMAL(12, 4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tiered_product_qty` (`product_id`, `min_qty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. INVENTORY LOGS
CREATE TABLE IF NOT EXISTS `inventory_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `variation_id` INT UNSIGNED NOT NULL,
  `change_qty` INT NOT NULL,
  `previous_qty` INT NOT NULL,
  `new_qty` INT NOT NULL,
  `reason` ENUM('restock', 'order_reservation', 'order_fulfilled', 'order_cancelled', 'manual_adjustment') NOT NULL,
  `reference_id` VARCHAR(64) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inventory_variation` (`variation_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ORDERS & ORDER ITEMS
CREATE TABLE IF NOT EXISTS `orders` (
  `id` VARCHAR(36) NOT NULL,
  `order_number` VARCHAR(32) NOT NULL,
  `user_id` VARCHAR(36) NOT NULL,
  `idempotency_key` VARCHAR(64) NOT NULL,
  `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
  `payment_status` ENUM('unpaid', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'unpaid',
  `payment_method` VARCHAR(50) NOT NULL,
  `currency_code` VARCHAR(3) NOT NULL DEFAULT 'USD',
  `currency_rate` DECIMAL(12, 6) NOT NULL DEFAULT 1.000000,
  `subtotal` DECIMAL(12, 4) NOT NULL,
  `discount_amount` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `shipping_amount` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `total_amount` DECIMAL(12, 4) NOT NULL,
  `total_weight_kg` DECIMAL(8, 4) NOT NULL DEFAULT 0.0000,
  `shipping_address` JSON NOT NULL,
  `billing_address` JSON NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_orders_number` (`order_number`),
  UNIQUE KEY `idx_orders_idempotency` (`idempotency_key`),
  KEY `idx_orders_user` (`user_id`, `created_at`),
  KEY `idx_orders_status` (`status`, `payment_status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `order_id` VARCHAR(36) NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `variation_id` INT UNSIGNED NOT NULL,
  `sku` VARCHAR(64) NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `variation_details` JSON NULL,
  `unit_price` DECIMAL(12, 4) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `line_total` DECIMAL(12, 4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. CURRENCIES & EXCHANGE RATES
CREATE TABLE IF NOT EXISTS `currencies` (
  `code` VARCHAR(3) NOT NULL,
  `symbol` VARCHAR(10) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `exchange_rate` DECIMAL(12, 6) NOT NULL DEFAULT 1.000000,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
SQL;

    $pdo->exec($sql);
    echo "Enterprise Database Schema Migration Executed Successfully!\n";

} catch (PDOException $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}
