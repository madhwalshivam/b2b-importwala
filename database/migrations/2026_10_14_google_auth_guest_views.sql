-- Google login, mandatory mobile, guest product view limit.
-- Safe to review. Apply with: php database/migrate_google_auth_guest_views.php
-- The PHP runner adds columns only when missing and normalises existing phones
-- before the unique index, so this file is the schema contract.

ALTER TABLE `users`
  ADD COLUMN `google_id` VARCHAR(255) NULL DEFAULT NULL AFTER `email`,
  ADD COLUMN `auth_provider` VARCHAR(20) NOT NULL DEFAULT 'email' AFTER `google_id`,
  ADD COLUMN `email_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `auth_provider`,
  ADD COLUMN `phone_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `phone`,
  ADD COLUMN `profile_completed` TINYINT(1) NOT NULL DEFAULT 0 AFTER `phone_verified`,
  ADD COLUMN `contact_consent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `profile_completed`,
  ADD COLUMN `consent_at` DATETIME NULL DEFAULT NULL AFTER `contact_consent`,
  ADD COLUMN `last_login_at` DATETIME NULL DEFAULT NULL AFTER `consent_at`,
  ADD COLUMN `signup_source` VARCHAR(32) NULL DEFAULT NULL AFTER `last_login_at`,
  ADD COLUMN `company_name` VARCHAR(150) NULL DEFAULT NULL;

-- After phone values are normalised to 10 digits (see the PHP runner):
-- ALTER TABLE `users` MODIFY `phone` VARCHAR(10) NULL DEFAULT NULL;
-- ALTER TABLE `users` ADD UNIQUE KEY `uniq_users_google_id` (`google_id`);
-- ALTER TABLE `users` ADD UNIQUE KEY `uniq_users_phone` (`phone`);

CREATE TABLE IF NOT EXISTS `guest_product_views` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `guest_token` VARCHAR(128) NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `first_viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_guest_product` (`guest_token`, `product_id`),
  KEY `idx_guest_token` (`guest_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `phone_otps` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone` VARCHAR(10) NOT NULL,
  `otp_hash` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_phone_otp_created` (`phone`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rate_limit_buckets` (
  `bucket_key` VARCHAR(191) NOT NULL,
  `hits` INT UNSIGNED NOT NULL DEFAULT 0,
  `window_start` DATETIME NOT NULL,
  PRIMARY KEY (`bucket_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
  ('guest_product_view_limit', '3'),
  ('require_phone_otp', '0'),
  ('google_login_enabled', '1');

-- Existing accounts with no usable Indian mobile stay incomplete
-- until they add a number on the next login.
-- UPDATE `users` SET `profile_completed` = 0
-- WHERE `phone` IS NULL OR `phone` = '' OR `phone` NOT REGEXP '^[6-9][0-9]{9}$';
