-- ============================================================
-- Collection Cards Feature Migration
-- Creates tables: collection_cards, collection_card_products
-- Run this SQL in your importwala database
-- ============================================================

CREATE TABLE IF NOT EXISTS `collection_cards` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`       VARCHAR(255) NOT NULL,
    `subtitle`    VARCHAR(500) DEFAULT NULL,
    `image`       VARCHAR(500) DEFAULT NULL,
    `link_url`    VARCHAR(500) DEFAULT '/catalog',
    `badge_text`  VARCHAR(100) DEFAULT NULL,
    `sort_order`  INT NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `collection_card_products` (
    `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `collection_card_id`   INT UNSIGNED NOT NULL,
    `product_id`           INT UNSIGNED NOT NULL,
    `display_order`        INT NOT NULL DEFAULT 0,
    UNIQUE KEY `uq_card_product` (`collection_card_id`, `product_id`),
    FOREIGN KEY (`collection_card_id`) REFERENCES `collection_cards`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
