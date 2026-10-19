-- Idempotent: add optional GSTIN on users.
-- Does not modify any other columns.
-- Run in phpMyAdmin against the live ImportWale database.

SET @db := DATABASE();

SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'users' AND COLUMN_NAME = 'gst_number'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `users` ADD COLUMN `gst_number` VARCHAR(15) NULL DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
