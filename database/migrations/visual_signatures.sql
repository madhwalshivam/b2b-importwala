-- =============================================================
-- Product Visual Signatures Migration
-- importwala.com
-- Stores 64-bit dHash and color histogram signatures for Visual Search
-- =============================================================

CREATE TABLE IF NOT EXISTS `product_visual_signatures` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id`   INT UNSIGNED NOT NULL,
  `image_path`   VARCHAR(500) NOT NULL,
  `dhash`        VARCHAR(64)  NOT NULL,
  `color_sig`    TEXT         NOT NULL,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY `uk_product_id` (`product_id`),
  INDEX `idx_dhash` (`dhash`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
