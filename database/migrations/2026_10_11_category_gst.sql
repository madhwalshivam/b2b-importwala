-- Category-wise GST / HSN.
-- Apply with: php database/migrations/run_category_gst.php
-- The runner is idempotent. These statements match what it applies.

ALTER TABLE `categories`
  ADD COLUMN `hsn_code` VARCHAR(8) NULL DEFAULT NULL AFTER `status`,
  ADD COLUMN `gst_rate` DECIMAL(5,2) NULL DEFAULT 18.00 AFTER `hsn_code`,
  ADD COLUMN `is_gst_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `gst_rate`;

ALTER TABLE `subcategories`
  ADD COLUMN `hsn_code` VARCHAR(8) NULL DEFAULT NULL AFTER `status`,
  ADD COLUMN `gst_rate` DECIMAL(5,2) NULL DEFAULT NULL AFTER `hsn_code`,
  ADD COLUMN `is_gst_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `gst_rate`;

ALTER TABLE `products`
  ADD COLUMN `gst_rate` DECIMAL(5,2) NULL DEFAULT NULL AFTER `hsn_code`;

ALTER TABLE `order_items`
  ADD COLUMN `hsn_code` VARCHAR(8) NULL DEFAULT NULL AFTER `sku`,
  ADD COLUMN `gst_rate` DECIMAL(5,2) NULL DEFAULT NULL AFTER `tax_amount`,
  ADD COLUMN `taxable_amount` DECIMAL(12,2) NULL DEFAULT NULL AFTER `gst_rate`,
  ADD COLUMN `cgst_amount` DECIMAL(12,2) NULL DEFAULT NULL AFTER `taxable_amount`,
  ADD COLUMN `sgst_amount` DECIMAL(12,2) NULL DEFAULT NULL AFTER `cgst_amount`,
  ADD COLUMN `igst_amount` DECIMAL(12,2) NULL DEFAULT NULL AFTER `sgst_amount`,
  ADD COLUMN `total_gst_amount` DECIMAL(12,2) NULL DEFAULT NULL AFTER `igst_amount`;

ALTER TABLE `orders`
  ADD COLUMN `total_taxable_amount` DECIMAL(12,2) NULL DEFAULT NULL AFTER `tax_total`,
  ADD COLUMN `total_cgst` DECIMAL(12,2) NULL DEFAULT NULL AFTER `total_taxable_amount`,
  ADD COLUMN `total_sgst` DECIMAL(12,2) NULL DEFAULT NULL AFTER `total_cgst`,
  ADD COLUMN `total_igst` DECIMAL(12,2) NULL DEFAULT NULL AFTER `total_sgst`,
  ADD COLUMN `total_gst` DECIMAL(12,2) NULL DEFAULT NULL AFTER `total_igst`,
  ADD COLUMN `shipping_state` VARCHAR(80) NULL DEFAULT NULL AFTER `total_gst`;

CREATE TABLE IF NOT EXISTS `gst_settings` (
  `id` TINYINT UNSIGNED NOT NULL,
  `seller_state` VARCHAR(80) NOT NULL DEFAULT 'Delhi',
  `seller_gstin` VARCHAR(15) NULL DEFAULT NULL,
  `default_gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 18.00,
  `price_mode` ENUM('exclusive','inclusive') NOT NULL DEFAULT 'exclusive',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gst_rate_audit_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type` VARCHAR(20) NOT NULL,
  `entity_id` INT UNSIGNED NOT NULL,
  `old_hsn` VARCHAR(30) NULL DEFAULT NULL,
  `new_hsn` VARCHAR(30) NULL DEFAULT NULL,
  `old_rate` DECIMAL(5,2) NULL DEFAULT NULL,
  `new_rate` DECIMAL(5,2) NULL DEFAULT NULL,
  `changed_by` INT UNSIGNED NULL DEFAULT NULL,
  `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gst_audit_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
