<?php
require_once __DIR__ . '/../public/index.php';

use App\Core\Database;

$db = Database::getInstance();

echo "Running migrations for Mudsor requirements...\n";

// 1. Users Table
$db->exec("CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NULL,
  `password` VARCHAR(255) NOT NULL,
  `status` ENUM('active', 'suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 2. Wishlist Table
$db->exec("CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_product` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 3. Cart Items Table
$db->exec("CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `session_id` VARCHAR(100) NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_cart_user` (`user_id`),
  INDEX `idx_cart_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 4. Announcements Table
$db->exec("CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `message` TEXT NOT NULL,
  `cta_text` VARCHAR(100) NULL,
  `cta_link` VARCHAR(255) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Seed announcement if empty
$stmt = $db->query("SELECT COUNT(*) FROM `announcements`");
if ($stmt->fetchColumn() == 0) {
    $db->exec("INSERT INTO `announcements` (`message`, `cta_text`, `cta_link`, `is_active`) VALUES 
    ('Mudsor EV Special: Flat 15% OFF on Stainless Steel Crash Guards & Accessories! Use Code: MUDSOR15', 'Shop Deals', '/shop', 1)");
}

// 5. Homepage Compare Products Table
$db->exec("CREATE TABLE IF NOT EXISTS `homepage_compare_products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_prod_compare` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Seed homepage compare products if empty (pick first 2-3 products)
$stmt = $db->query("SELECT COUNT(*) FROM `homepage_compare_products`");
if ($stmt->fetchColumn() == 0) {
    $prodStmt = $db->query("SELECT id FROM products LIMIT 3");
    $prods = $prodStmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($prods as $idx => $pid) {
        $db->exec("INSERT INTO `homepage_compare_products` (`product_id`, `sort_order`) VALUES ($pid, $idx)");
    }
}

echo "Migrations completed successfully!\n";
