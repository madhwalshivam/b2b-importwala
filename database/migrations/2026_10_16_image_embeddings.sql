-- Visual search: embedding status, incremental queue, and cached query vectors.
-- Safe to re-run on MariaDB (ADD COLUMN IF NOT EXISTS).
-- The CLI scripts also apply this through App\Services\EmbeddingSchema.

ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `embedding_status` VARCHAR(20) NULL DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `embedded_at` DATETIME NULL DEFAULT NULL AFTER `embedding_status`,
  ADD COLUMN IF NOT EXISTS `embedding_error` VARCHAR(500) NULL DEFAULT NULL AFTER `embedded_at`,
  ADD COLUMN IF NOT EXISTS `embedding_attempts` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `embedding_error`,
  ADD COLUMN IF NOT EXISTS `embedding_source_url` VARCHAR(500) NULL DEFAULT NULL AFTER `embedding_attempts`;

ALTER TABLE `products`
  ADD INDEX IF NOT EXISTS `idx_products_embedding_status` (`embedding_status`);

CREATE TABLE IF NOT EXISTS `embedding_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `action` ENUM('upsert','delete') NOT NULL DEFAULT 'upsert',
  `status` ENUM('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_error` VARCHAR(500) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_embedding_jobs_status` (`status`, `id`),
  KEY `idx_embedding_jobs_product` (`product_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Query embedding for one image-search sid. Kept 1 hour so paging,
-- sort, and category changes do not call the embedding API again.
CREATE TABLE IF NOT EXISTS `image_search_vectors` (
  `token` CHAR(32) NOT NULL,
  `vector_json` LONGTEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  PRIMARY KEY (`token`),
  KEY `idx_image_search_vectors_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
