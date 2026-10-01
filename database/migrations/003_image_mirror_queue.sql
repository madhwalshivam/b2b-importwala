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
