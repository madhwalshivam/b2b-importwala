-- Discount Coupons & Offers Engine Schema Migration

CREATE TABLE IF NOT EXISTS coupons (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    discount_type ENUM('flat', 'percentage') NOT NULL DEFAULT 'flat',
    discount_value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    min_order_value DECIMAL(10, 2) NULL DEFAULT 0.00,
    max_discount_cap DECIMAL(10, 2) NULL DEFAULT NULL,
    usage_limit_total INT NULL DEFAULT NULL,
    usage_limit_per_user INT NULL DEFAULT 1,
    scope_type ENUM('all_products', 'specific_products', 'specific_categories') NOT NULL DEFAULT 'all_products',
    valid_from DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    valid_until DATETIME NULL DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_coupons_code (code),
    INDEX idx_coupons_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupon_products (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT(10) UNSIGNED NOT NULL,
    product_id INT(10) UNSIGNED NOT NULL,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_coupon_prod (coupon_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupon_categories (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT(10) UNSIGNED NOT NULL,
    category_id INT(10) UNSIGNED NOT NULL,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_coupon_cat (coupon_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupon_usage (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT(10) UNSIGNED NOT NULL,
    user_id INT(10) UNSIGNED NULL DEFAULT NULL,
    session_id VARCHAR(100) NULL DEFAULT NULL,
    order_id INT(10) UNSIGNED NULL DEFAULT NULL,
    discount_applied DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    used_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    INDEX idx_coupon_usage_coupon (coupon_id),
    INDEX idx_coupon_usage_user (user_id),
    INDEX idx_coupon_usage_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
