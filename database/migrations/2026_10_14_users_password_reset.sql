-- Idempotent: add customer password-reset columns.
-- Does not modify Google login columns.
-- Run in phpMyAdmin against the live ImportWale database.

SET @db := DATABASE();

-- users.reset_token
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'users' AND COLUMN_NAME = 'reset_token'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `users` ADD COLUMN `reset_token` VARCHAR(64) NULL DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- users.reset_token_expires_at
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'users' AND COLUMN_NAME = 'reset_token_expires_at'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `users` ADD COLUMN `reset_token_expires_at` DATETIME NULL DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- customers.reset_token (only if the customers table exists)
SET @table_exists := (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'customers'
);
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'reset_token'
);
SET @sql := IF(@table_exists = 1 AND @exists = 0,
  'ALTER TABLE `customers` ADD COLUMN `reset_token` VARCHAR(64) NULL DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'reset_token_expires_at'
);
SET @sql := IF(@table_exists = 1 AND @exists = 0,
  'ALTER TABLE `customers` ADD COLUMN `reset_token_expires_at` DATETIME NULL DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
