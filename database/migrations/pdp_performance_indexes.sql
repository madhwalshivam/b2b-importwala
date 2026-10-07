-- ============================================================
-- Product Detail Page performance indexes
-- Safe to re-run: each statement checks information_schema first
-- via the companion PHP runner, OR run these one-by-one and
-- ignore "Duplicate key name" errors.
-- Run on Hostinger MySQL (phpMyAdmin or mysql CLI).
-- ============================================================

-- Products: common PDP / related lookups
ALTER TABLE `products` ADD INDEX `idx_prod_status_subcat_feat` (`status`, `subcategory_id`, `is_featured`, `id`);
ALTER TABLE `products` ADD INDEX `idx_prod_status_cat_feat` (`status`, `category_id`, `is_featured`, `id`);
ALTER TABLE `products` ADD INDEX `idx_prod_slug_status` (`slug`, `status`);

-- Variants
ALTER TABLE `product_variants` ADD INDEX `idx_pv_product_active_sort` (`product_id`, `is_active`, `sort_order`, `id`);

-- Product images / specs (product_id already indexed; composite helps ORDER BY)
ALTER TABLE `product_images` ADD INDEX `idx_pi_product_primary_sort` (`product_id`, `is_primary`, `sort_order`, `id`);
ALTER TABLE `product_specifications` ADD INDEX `idx_ps_product_sort` (`product_id`, `sort_order`, `id`);

-- (tiered_prices.variant_id does not exist on all schemas — skipped)

-- Wishlist / cart session lookups used on PDP header state
ALTER TABLE `wishlist` ADD INDEX `idx_wishlist_session` (`session_id`);
ALTER TABLE `wishlist` ADD INDEX `idx_wishlist_session_product` (`session_id`, `product_id`);
ALTER TABLE `cart_items` ADD INDEX `idx_cart_session_product` (`session_id`, `product_id`);

-- Settings key lookup
ALTER TABLE `settings` ADD INDEX `idx_settings_key` (`setting_key`);

-- Variation attribute map (variation_id is already in PK; value lookup helper)
-- product_variation_attribute_map already has PRIMARY(variation_id, attribute_value_id)

-- Nav links
ALTER TABLE `nav_links` ADD INDEX `idx_nav_parent_active_sort` (`parent_id`, `is_active`, `sort_order`);

-- Visual features: category-scoped similarity joins already use product_id
-- Ensure products.category_id / subcategory_id covered (see above composites)
