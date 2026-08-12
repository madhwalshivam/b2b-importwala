-- ============================================================
-- Homepage Sections DB Migration & Hardening
-- ============================================================

-- Add max_products column if not exists
ALTER TABLE `homepage_sections` 
ADD COLUMN IF NOT EXISTS `max_products` INT UNSIGNED NOT NULL DEFAULT 8 AFTER `subtitle`;

-- Add unique key on section_key if not exists
-- Ensure section_key matches slugs for product sections:
INSERT INTO `homepage_sections` (`section_key`, `title`, `subtitle`, `status`, `max_products`, `sort_order`) 
VALUES 
('featured_products', 'Featured Products', 'Top rated accessories with verified fitment guarantee', 'active', 8, 1),
('featured_deals', 'Featured Deals', 'Limited time offers on premium EV accessories', 'active', 8, 2),
('best_sellers', 'Best Sellers', 'Most popular accessories trusted by thousands of riders', 'active', 8, 3),
('new_arrivals', 'New Arrivals', 'Fresh accessories just added to our collection', 'active', 8, 4),
('flash_sale', 'Flash Sale', 'Grab these deals before they are gone — massive discounts await', 'active', 6, 5)
ON DUPLICATE KEY UPDATE 
`title` = VALUES(`title`),
`subtitle` = VALUES(`subtitle`);

-- Table for mapping section -> products with display order
CREATE TABLE IF NOT EXISTS `homepage_section_products` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `section_id`    INT UNSIGNED NOT NULL,
    `product_id`    INT UNSIGNED NOT NULL,
    `display_order` SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_section_product` (`section_id`, `product_id`),
    KEY `idx_section_order` (`section_id`, `display_order`),
    KEY `idx_product_id`    (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
