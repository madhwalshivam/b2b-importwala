-- ImportWale Razorpay checkout columns
-- Take a full database backup in phpMyAdmin before running this.
-- Safe to run more than once. It does not delete or rewrite existing rows.

CREATE TABLE IF NOT EXISTS `payment_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NULL,
  `event` VARCHAR(80) NOT NULL,
  `razorpay_order_id` VARCHAR(100) NULL,
  `razorpay_payment_id` VARCHAR(100) NULL,
  `status` VARCHAR(40) NULL,
  `message` VARCHAR(500) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payment_logs_order` (`order_id`),
  KEY `idx_payment_logs_rz_order` (`razorpay_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- orders.paid_at
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'paid_at') = 0,
    'ALTER TABLE `orders` ADD COLUMN `paid_at` DATETIME NULL',
    'SELECT 1'
  )
);
PREPARE iw_stmt FROM @sql; EXECUTE iw_stmt; DEALLOCATE PREPARE iw_stmt;

-- orders.razorpay_method
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'razorpay_method') = 0,
    'ALTER TABLE `orders` ADD COLUMN `razorpay_method` VARCHAR(40) NULL',
    'SELECT 1'
  )
);
PREPARE iw_stmt FROM @sql; EXECUTE iw_stmt; DEALLOCATE PREPARE iw_stmt;

-- orders.checkout_source (cart or buynow)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'checkout_source') = 0,
    'ALTER TABLE `orders` ADD COLUMN `checkout_source` VARCHAR(20) NULL',
    'SELECT 1'
  )
);
PREPARE iw_stmt FROM @sql; EXECUTE iw_stmt; DEALLOCATE PREPARE iw_stmt;

-- orders.cart_session_id
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'cart_session_id') = 0,
    'ALTER TABLE `orders` ADD COLUMN `cart_session_id` VARCHAR(80) NULL',
    'SELECT 1'
  )
);
PREPARE iw_stmt FROM @sql; EXECUTE iw_stmt; DEALLOCATE PREPARE iw_stmt;

-- order_items.variation_id
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_items' AND COLUMN_NAME = 'variation_id') = 0,
    'ALTER TABLE `order_items` ADD COLUMN `variation_id` INT UNSIGNED NULL',
    'SELECT 1'
  )
);
PREPARE iw_stmt FROM @sql; EXECUTE iw_stmt; DEALLOCATE PREPARE iw_stmt;

-- order_items.variant_id
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_items' AND COLUMN_NAME = 'variant_id') = 0,
    'ALTER TABLE `order_items` ADD COLUMN `variant_id` INT UNSIGNED NULL',
    'SELECT 1'
  )
);
PREPARE iw_stmt FROM @sql; EXECUTE iw_stmt; DEALLOCATE PREPARE iw_stmt;

-- order_items.variant_label
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_items' AND COLUMN_NAME = 'variant_label') = 0,
    'ALTER TABLE `order_items` ADD COLUMN `variant_label` VARCHAR(150) NULL',
    'SELECT 1'
  )
);
PREPARE iw_stmt FROM @sql; EXECUTE iw_stmt; DEALLOCATE PREPARE iw_stmt;
