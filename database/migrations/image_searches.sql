-- =============================================================
-- Persistent Image Search results (refresh / Back / share safe)
-- importwala.com
-- =============================================================

CREATE TABLE IF NOT EXISTS `image_searches` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `token`               CHAR(32)        NOT NULL,
  `uploaded_image_path` VARCHAR(500)    NOT NULL,
  `results_json`        MEDIUMTEXT      NOT NULL,
  `has_confident_match` TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at`          DATETIME        NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_image_searches_token` (`token`),
  KEY `idx_image_searches_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
