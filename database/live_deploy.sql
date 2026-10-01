-- =============================================================
-- IMPORTWALA - Live Server Database Migration
-- Run this file once in phpMyAdmin or via SSH on live server
-- All statements use IF NOT EXISTS - 100% safe to run
-- =============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- -------------------------------------------------------
-- 1. INQUIRIES SYSTEM
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `inquiry_number` VARCHAR(50) NOT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `company_name` VARCHAR(150) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `gst_number` VARCHAR(50) DEFAULT NULL,
  `business_type` VARCHAR(100) DEFAULT NULL,
  `customer_message` TEXT,
  `delivery_timeline` VARCHAR(100) DEFAULT NULL,
  `total_products` INT DEFAULT 0,
  `total_quantity` INT DEFAULT 0,
  `status` VARCHAR(50) DEFAULT 'New',
  `admin_notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (`inquiry_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inquiry_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `inquiry_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `product_name_snapshot` VARCHAR(255) NOT NULL,
  `sku_snapshot` VARCHAR(100) DEFAULT NULL,
  `product_image_snapshot` VARCHAR(500) DEFAULT NULL,
  `variation_id` INT UNSIGNED DEFAULT NULL,
  `variation_name` VARCHAR(255) DEFAULT NULL,
  `quantity` INT DEFAULT 1,
  `price_snapshot` DECIMAL(10,2) DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`inquiry_id`) REFERENCES `inquiries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 2. IMAGE MIRROR QUEUE (background R2 upload system)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `image_mirror_queue` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `source_url` TEXT NOT NULL,
  `source_hash` VARCHAR(40) NOT NULL UNIQUE,
  `r2_key` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_error` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status_attempts` (`status`, `attempts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 3. EXTRA TABLES (badges, auth, settings)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_badges` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `badge_text` VARCHAR(100) NOT NULL,
  `badge_icon` VARCHAR(100) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notification_log` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_type` VARCHAR(100) NOT NULL,
  `channel` VARCHAR(50) NOT NULL,
  `recipient` VARCHAR(255) NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `error_message` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `refresh_tokens` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(36) NOT NULL,
  `user_type` VARCHAR(50) NOT NULL,
  `token_hash` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT,
  `expires_at` DATETIME NOT NULL,
  `revoked_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_log` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(36) NOT NULL,
  `user_type` VARCHAR(50) NOT NULL,
  `event_type` VARCHAR(100) NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT,
  `details` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 4. RFQ (Request for Quote)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rfq_requests` (
  `id`                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_name`           VARCHAR(255)  NOT NULL,
  `product_reference_link` VARCHAR(1000) DEFAULT NULL,
  `quantity`               INT UNSIGNED  NOT NULL,
  `unit`                   VARCHAR(50)   NOT NULL,
  `target_price`           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `overall_budget`         VARCHAR(100)  NOT NULL,
  `sourcing_purpose`       VARCHAR(100)  NOT NULL,
  `specifications`         TEXT          DEFAULT NULL,
  `full_name`              VARCHAR(150)  NOT NULL,
  `phone`                  VARCHAR(15)   NOT NULL,
  `email`                  VARCHAR(255)  NOT NULL,
  `pincode`                CHAR(6)       NOT NULL,
  `business_type`          VARCHAR(100)  NOT NULL,
  `has_gst`                TINYINT(1)    NOT NULL DEFAULT 0,
  `additional_comments`    TEXT          DEFAULT NULL,
  `status`                 ENUM('New','Contacted','Quoted','Closed') NOT NULL DEFAULT 'New',
  `admin_notes`            TEXT          DEFAULT NULL,
  `created_at`             DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`             DATETIME      DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_phone` (`phone`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rfq_reference_photos` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `rfq_id`        INT UNSIGNED NOT NULL,
  `file_path`     VARCHAR(500) NOT NULL,
  `original_name` VARCHAR(255) DEFAULT NULL,
  `file_size`     INT UNSIGNED DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_rfq_id` (`rfq_id`),
  FOREIGN KEY (`rfq_id`) REFERENCES `rfq_requests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 5. COLLECTION CARDS (home page feature)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `collection_cards` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title`      VARCHAR(255) NOT NULL,
  `subtitle`   VARCHAR(500) DEFAULT NULL,
  `image`      VARCHAR(500) DEFAULT NULL,
  `link_url`   VARCHAR(500) DEFAULT '/catalog',
  `badge_text` VARCHAR(100) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `collection_card_products` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `collection_card_id` INT UNSIGNED NOT NULL,
  `product_id`         INT UNSIGNED NOT NULL,
  `display_order`      INT NOT NULL DEFAULT 0,
  UNIQUE KEY `uq_card_product` (`collection_card_id`, `product_id`),
  FOREIGN KEY (`collection_card_id`) REFERENCES `collection_cards`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 6. MISSING TABLES (safe IF NOT EXISTS)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_categories` (
  `product_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`product_id`, `category_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_brands` (
  `product_id` INT UNSIGNED NOT NULL,
  `brand_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`product_id`, `brand_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_included_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `item_name` VARCHAR(255) NOT NULL,
  `is_included` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_related` (
  `product_id` INT UNSIGNED NOT NULL,
  `related_product_id` INT UNSIGNED NOT NULL,
  `relation_type` VARCHAR(50) DEFAULT 'related',
  `sort_order` INT DEFAULT 0,
  PRIMARY KEY (`product_id`, `related_product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`related_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `url_redirects` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `old_slug` VARCHAR(255) NOT NULL,
  `target_url` VARCHAR(500) NOT NULL,
  `http_code` INT DEFAULT 301,
  UNIQUE KEY (`old_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(36) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `address_line1` VARCHAR(255) NOT NULL,
  `address_line2` VARCHAR(255) DEFAULT NULL,
  `landmark` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `pincode` VARCHAR(20) NOT NULL,
  `country` VARCHAR(100) NOT NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wholesale_inquiries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `company` VARCHAR(150) DEFAULT NULL,
  `quantity` INT DEFAULT 0,
  `message` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;

-- =============================================================
-- DONE! All tables created successfully.
-- =============================================================
