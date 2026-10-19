-- Recently viewed / product history for logged-in customers.
-- Idempotent. Does not modify any other table.
-- Run in phpMyAdmin against the live ImportWale database.
--
-- users.id  = varchar(36)
-- products.id = int(10) unsigned

CREATE TABLE IF NOT EXISTS `user_product_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(36) NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `last_viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `view_count` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_product` (`user_id`, `product_id`),
  KEY `idx_user_recent` (`user_id`, `last_viewed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional foreign keys (safe to ignore if phpMyAdmin reports a type/collation mismatch)
SET @db := DATABASE();

SET @fk_user := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'user_product_history' AND CONSTRAINT_NAME = 'fk_uph_user'
);
SET @sql := IF(@fk_user = 0,
  'ALTER TABLE `user_product_history` ADD CONSTRAINT `fk_uph_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_prod := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'user_product_history' AND CONSTRAINT_NAME = 'fk_uph_product'
);
SET @sql := IF(@fk_prod = 0,
  'ALTER TABLE `user_product_history` ADD CONSTRAINT `fk_uph_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
