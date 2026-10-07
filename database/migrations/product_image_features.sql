-- =============================================================
-- Product Image Features (pure-PHP Visual Search index)
-- importwala.com
-- =============================================================

CREATE TABLE IF NOT EXISTS `product_image_features` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`       INT UNSIGNED    NOT NULL,
  `variant_id`       INT UNSIGNED    NULL DEFAULT NULL,
  `image_url`        VARCHAR(500)    NOT NULL,
  `dhash`            CHAR(16)        NOT NULL,
  `ahash`            CHAR(16)        NOT NULL,
  `color_hist`       MEDIUMTEXT      NOT NULL,
  `dominant_colors`  VARCHAR(64)     NULL DEFAULT NULL,
  `aspect_ratio`     DECIMAL(8,4)    NULL DEFAULT NULL,
  `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pif_product_image` (`product_id`, `image_url`(191)),
  KEY `idx_pif_product_id` (`product_id`),
  KEY `idx_pif_dhash` (`dhash`),
  KEY `idx_pif_variant_id` (`variant_id`),
  CONSTRAINT `fk_pif_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
