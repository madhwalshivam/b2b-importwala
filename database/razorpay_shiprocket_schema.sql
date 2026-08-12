-- Razorpay & Shiprocket Integration Migration Schema

CREATE TABLE IF NOT EXISTS `payment_gateway_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider` VARCHAR(50) NOT NULL DEFAULT 'razorpay',
    `key_id` VARCHAR(255) NOT NULL,
    `key_secret` VARCHAR(255) NOT NULL,
    `webhook_secret` VARCHAR(255) DEFAULT NULL,
    `mode` ENUM('test','live') DEFAULT 'test',
    `is_active` TINYINT(1) DEFAULT 1,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `shipping_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider` VARCHAR(50) NOT NULL DEFAULT 'shiprocket',
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `pickup_location` VARCHAR(255) DEFAULT NULL,
    `auto_assign_courier` TINYINT(1) DEFAULT 1,
    `token` TEXT DEFAULT NULL,
    `token_expires_at` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admin_audit_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT UNSIGNED NULL,
    `admin_email` VARCHAR(150) NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
