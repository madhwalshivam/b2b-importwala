CREATE TABLE IF NOT EXISTS `image_mirror_queue` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `source_url` VARCHAR(2048) NOT NULL,
  `source_hash` VARCHAR(40) NOT NULL,
  `r2_key` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
  `attempts` INT DEFAULT 0,
  `last_error` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `idx_source_hash` (`source_hash`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
