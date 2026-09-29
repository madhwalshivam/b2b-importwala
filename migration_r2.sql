CREATE TABLE IF NOT EXISTS `image_mirror_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_url_hash` varchar(64) NOT NULL,
  `source_url` text NOT NULL,
  `r2_url` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_source_url_hash` (`source_url_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
