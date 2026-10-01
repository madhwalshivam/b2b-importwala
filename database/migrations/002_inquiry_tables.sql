CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `inquiry_number` VARCHAR(50) NOT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `company_name` VARCHAR(150) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `gst_number` VARCHAR(50) DEFAULT NULL,
  `business_type` VARCHAR(100) DEFAULT NULL,
  `customer_message` TEXT,
  `delivery_timeline` VARCHAR(100) DEFAULT NULL,
  `total_products` INT DEFAULT 0,
  `total_quantity` INT DEFAULT 0,
  `status` VARCHAR(50) DEFAULT 'New',
  `admin_notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (`inquiry_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inquiry_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `inquiry_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `product_name_snapshot` VARCHAR(255) NOT NULL,
  `sku_snapshot` VARCHAR(100) DEFAULT NULL,
  `product_image_snapshot` VARCHAR(500) DEFAULT NULL,
  `variation_id` INT UNSIGNED DEFAULT NULL,
  `variation_name` VARCHAR(255) DEFAULT NULL,
  `quantity` INT DEFAULT 1,
  `price_snapshot` DECIMAL(10,2) DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`inquiry_id`) REFERENCES `inquiries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
