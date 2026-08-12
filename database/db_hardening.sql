-- Database Hardening Migration Script
-- Adds Indexes for Foreign Keys and High-Throughput Columns

-- Indexes on Wishlist
ALTER TABLE wishlist ADD INDEX idx_wishlist_user_id (user_id);
ALTER TABLE wishlist ADD INDEX idx_wishlist_product_id (product_id);
ALTER TABLE wishlist ADD UNIQUE INDEX idx_wishlist_user_prod (user_id, product_id);

-- Indexes on Orders and Order Items
ALTER TABLE orders ADD INDEX idx_orders_customer_email (customer_email);
ALTER TABLE orders ADD INDEX idx_orders_customer_phone (customer_phone);
ALTER TABLE orders ADD INDEX idx_orders_status (order_status);
ALTER TABLE orders ADD INDEX idx_orders_created_at (created_at);

ALTER TABLE order_items ADD INDEX idx_order_items_order_id (order_id);
ALTER TABLE order_items ADD INDEX idx_order_items_product_id (product_id);

-- Indexes on Products
ALTER TABLE products ADD INDEX idx_products_category_id (category_id);
ALTER TABLE products ADD INDEX idx_products_brand_id (brand_id);
ALTER TABLE products ADD INDEX idx_products_status (status);
ALTER TABLE products ADD INDEX idx_products_sku (sku);

-- Indexes on Live Sessions
ALTER TABLE live_sessions ADD INDEX idx_live_sessions_updated_at (updated_at);
ALTER TABLE live_sessions ADD INDEX idx_live_sessions_session_id (session_id);
