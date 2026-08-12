-- Mudsor Seed Data

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Default Roles
INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Super Admin', 'super-admin', 'Full platform owner with unrestricted access.'),
(2, 'Store Manager', 'store-manager', 'Manages products, orders, inventory and customer inquiries.'),
(3, 'Order Executive', 'order-executive', 'Handles order processing, packing updates, and invoice printing.'),
(4, 'Catalog Manager', 'catalog-manager', 'Manages products, categories, brands, and scooter models.');

-- 2. Module Permissions
INSERT INTO `permissions` (`id`, `module`, `action`, `name`, `key_code`) VALUES
(1, 'dashboard', 'view', 'View Dashboard', 'dashboard.view'),
(2, 'products', 'view', 'View Products', 'products.view'),
(3, 'products', 'add', 'Add Product', 'products.add'),
(4, 'products', 'edit', 'Edit Product', 'products.edit'),
(5, 'products', 'delete', 'Delete Product', 'products.delete'),
(6, 'categories', 'view', 'View Categories', 'categories.view'),
(7, 'categories', 'add', 'Add Category', 'categories.add'),
(8, 'categories', 'edit', 'Edit Category', 'categories.edit'),
(9, 'categories', 'delete', 'Delete Category', 'categories.delete'),
(10, 'brands', 'view', 'View Brands', 'brands.view'),
(11, 'brands', 'add', 'Add Brand', 'brands.add'),
(12, 'brands', 'edit', 'Edit Brand', 'brands.edit'),
(13, 'brands', 'delete', 'Delete Brand', 'brands.delete'),
(14, 'scooter_models', 'view', 'View Scooter Models', 'scooter_models.view'),
(15, 'scooter_models', 'add', 'Add Scooter Model', 'scooter_models.add'),
(16, 'scooter_models', 'edit', 'Edit Scooter Model', 'scooter_models.edit'),
(17, 'scooter_models', 'delete', 'Delete Scooter Model', 'scooter_models.delete'),
(18, 'orders', 'view', 'View Orders', 'orders.view'),
(19, 'orders', 'edit', 'Update Order Status', 'orders.edit'),
(20, 'orders', 'delete', 'Cancel/Delete Order', 'orders.delete'),
(21, 'customers', 'view', 'View Customers', 'customers.view'),
(22, 'customers', 'edit', 'Edit Customer', 'customers.edit'),
(23, 'coupons', 'view', 'View Coupons', 'coupons.view'),
(24, 'coupons', 'add', 'Add Coupon', 'coupons.add'),
(25, 'coupons', 'edit', 'Edit Coupon', 'coupons.edit'),
(26, 'coupons', 'delete', 'Delete Coupon', 'coupons.delete'),
(27, 'inventory', 'view', 'View Inventory', 'inventory.view'),
(28, 'inventory', 'edit', 'Update Stock', 'inventory.edit'),
(29, 'reviews', 'view', 'View Reviews', 'reviews.view'),
(30, 'reviews', 'edit', 'Approve/Reply Review', 'reviews.edit'),
(31, 'blog', 'view', 'View Blog Posts', 'blog.view'),
(32, 'blog', 'add', 'Add Blog Post', 'blog.add'),
(33, 'blog', 'edit', 'Edit Blog Post', 'blog.edit'),
(34, 'cms', 'view', 'View CMS Pages', 'cms.view'),
(35, 'cms', 'edit', 'Edit CMS Pages', 'cms.edit'),
(36, 'sections', 'view', 'View Homepage Sections', 'sections.view'),
(37, 'sections', 'edit', 'Edit Homepage Sections', 'sections.edit'),
(38, 'theme', 'view', 'View Theme Builder', 'theme.view'),
(39, 'theme', 'edit', 'Edit Theme Builder', 'theme.edit'),
(40, 'settings', 'view', 'View Settings', 'settings.view'),
(41, 'settings', 'edit', 'Edit Settings', 'settings.edit'),
(42, 'employees', 'view', 'View Employees', 'employees.view'),
(43, 'employees', 'add', 'Add Employee', 'employees.add'),
(44, 'employees', 'edit', 'Edit Employee', 'employees.edit'),
(45, 'employees', 'delete', 'Delete Employee', 'employees.delete'),
(46, 'logs', 'view', 'View Activity Logs', 'logs.view');

-- Grant all permissions to Super Admin (Role 1)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`;

-- Grant Store Manager (Role 2) selected permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, id FROM `permissions` WHERE module IN ('dashboard', 'products', 'categories', 'brands', 'scooter_models', 'orders', 'customers', 'inventory', 'reviews');

-- Grant Order Executive (Role 3) selected permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions` WHERE key_code IN ('dashboard.view', 'orders.view', 'orders.edit', 'customers.view', 'inventory.view');

-- 3. Default Super Admin Employee (Password: admin123)
-- bcrypt hash for 'admin123'
INSERT INTO `admin_users` (`id`, `role_id`, `name`, `email`, `phone`, `username`, `password`, `photo`, `status`) VALUES
(1, 1, 'Jass Rughwani', 'mudsorinfo@gmail.com', '9217714452', 'admin', '$2y$10$e.w2pPmsYn5j3xU4jCjUa.6n5g9bX.8K7P0s3P1w2pPmsYn5j3xU4', '/assets/images/avatar.png', 'active');

-- 4. Brands (Electric Scooter Manufacturers)
INSERT INTO `brands` (`id`, `name`, `slug`, `logo`, `banner`, `description`, `is_featured`, `sort_order`, `status`) VALUES
(1, 'Ola Electric', 'ola-electric', '/uploads/brands/ola.png', NULL, 'Premium accessories for Ola S1 Pro, S1 X, S1 Air electric scooters.', 1, 1, 'active'),
(2, 'Ather Energy', 'ather-energy', '/uploads/brands/ather.png', NULL, 'Custom engineered accessories for Ather 450X, 450 Apex and Rizta.', 1, 2, 'active'),
(3, 'TVS Motors', 'tvs-motors', '/uploads/brands/tvs.png', NULL, 'Heavy-duty accessories for TVS iQube series.', 1, 3, 'active'),
(4, 'Bajaj Chetak', 'bajaj-chetak', '/uploads/brands/bajaj.png', NULL, 'Elegant accessories tailored for Bajaj Chetak EV.', 1, 4, 'active'),
(5, 'Hero Vida', 'hero-vida', '/uploads/brands/hero-vida.png', NULL, 'Modular smart accessories for Hero Vida V1 Pro / Plus.', 1, 5, 'active'),
(6, 'Okinawa', 'okinawa', '/uploads/brands/okinawa.png', NULL, 'Sturdy accessories for Okinawa PraisePro and iPraise+.', 0, 6, 'active'),
(7, 'Ampere', 'ampere', '/uploads/brands/ampere.png', NULL, 'Ergonomic accessories for Ampere Nexus and Primus.', 0, 7, 'active'),
(8, 'Pure EV', 'pure-ev', '/uploads/brands/pure-ev.png', NULL, 'Durable accessories for Pure EV EPluto 7G.', 0, 8, 'active');

-- 5. Scooter Models per Brand
INSERT INTO `scooter_models` (`id`, `brand_id`, `name`, `slug`, `year_generation`, `sort_order`, `status`) VALUES
-- Ola Models
(1, 1, 'Ola S1 Pro (Gen 1 & Gen 2)', 'ola-s1-pro', '2022-2026', 1, 'active'),
(2, 1, 'Ola S1 X', 'ola-s1-x', '2023-2026', 2, 'active'),
(3, 1, 'Ola S1 Air', 'ola-s1-air', '2023-2026', 3, 'active'),
-- Ather Models
(4, 2, 'Ather 450X (Gen 3 / Gen 4)', 'ather-450x', '2021-2026', 1, 'active'),
(5, 2, 'Ather Rizta', 'ather-rizta', '2024-2026', 2, 'active'),
(6, 2, 'Ather 450 Apex', 'ather-450-apex', '2024-2026', 3, 'active'),
-- TVS Models
(7, 3, 'TVS iQube S / ST', 'tvs-iqube', '2022-2026', 1, 'active'),
-- Bajaj Models
(8, 4, 'Bajaj Chetak Premium / Urbane', 'bajaj-chetak-premium', '2023-2026', 1, 'active'),
-- Hero Vida Models
(9, 5, 'Hero Vida V1 Pro', 'hero-vida-v1-pro', '2023-2026', 1, 'active'),
-- Okinawa Models
(10, 6, 'Okinawa PraisePro', 'okinawa-praise-pro', '2022-2026', 1, 'active');

-- 6. Categories & Subcategories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `image`, `description`, `is_featured`, `sort_order`, `status`) VALUES
(1, 'Mobile Holders & Mounts', 'mobile-holders-mounts', 'smartphone', '/uploads/categories/mobile-holder.jpg', 'All-weather waterproof mobile holders with vibration dampening for electric scooters.', 1, 1, 'active'),
(2, 'Footrests & Crash Guards', 'footrests-crash-guards', 'shield', '/uploads/categories/crash-guard.jpg', 'Heavy duty stainless steel guards and foldable footrests.', 1, 2, 'active'),
(3, 'Body & Touchscreen Covers', 'body-touchscreen-covers', 'layers', '/uploads/categories/covers.jpg', 'Waterproof heavy-duty body covers and screen guard protection.', 1, 3, 'active'),
(4, 'Seat Covers & Cushions', 'seat-covers-cushions', 'armchair', '/uploads/categories/seat-cover.jpg', 'Memory foam breathable & waterproof seat covers.', 1, 4, 'active'),
(5, 'Chargers & Extensions', 'chargers-extensions', 'zap', '/uploads/categories/chargers.jpg', 'Portable EV fast chargers, extension cables & wall dock holders.', 1, 5, 'active'),
(6, 'Bags & Storage Solutions', 'bags-storage-solutions', 'shopping-bag', '/uploads/categories/bags.jpg', 'Under-seat organizers, front hooks, and boot liner bags.', 1, 6, 'active');

INSERT INTO `subcategories` (`id`, `category_id`, `name`, `slug`, `sort_order`, `status`) VALUES
(1, 1, 'Waterproof Touchscreen Holders', 'waterproof-touchscreen-holders', 1, 'active'),
(2, 1, 'Vibration Dampener Mounts', 'vibration-dampener-mounts', 2, 'active'),
(3, 2, 'Side Crash Guards', 'side-crash-guards', 1, 'active'),
(4, 2, 'Foldable Lady Footrests', 'foldable-lady-footrests', 2, 'active'),
(5, 3, 'All-Weather Body Covers', 'all-weather-body-covers', 1, 'active'),
(6, 3, 'Tempered Glass Screen Guards', 'tempered-glass-screen-guards', 2, 'active');

-- 7. Sample Products
INSERT INTO `products` (`id`, `name`, `slug`, `sku`, `barcode`, `hsn_code`, `category_id`, `subcategory_id`, `brand_id`, `price`, `sale_price`, `tax_percent`, `stock`, `main_image`, `description`, `warranty_info`, `tags`, `is_featured`, `is_best_seller`, `is_new_arrival`, `is_flash_sale`, `status`) VALUES
(1, 'Mudsor Heavy-Duty Stainless Steel All-Around Crash Guard with Lady Footrest', 'mudsor-heavy-duty-crash-guard-footrest', 'MUD-CG-001', '890123456781', '87141090', 2, 3, 1, 2999.00, 1899.00, 18.00, 150, '/uploads/products/crash_guard_ola.jpg', '<p>Protect your electric scooter from scratches and major drops with Mudsor premium grade stainless steel crash guards. Features built-in heavy duty foldable footrest for pillion riders.</p>', '1 Year Replacement Warranty against rusting and welds.', 'crash guard, footrest, ola, ather, tvs', 1, 1, 1, 1, 'active'),

(2, 'Mudsor 360 Rotation Waterproof Mobile Phone Holder with Anti-Vibration Mount', 'mudsor-360-waterproof-mobile-holder', 'MUD-MH-002', '890123456782', '87141090', 1, 1, 1, 1299.00, 799.00, 18.00, 300, '/uploads/products/mobile_holder.jpg', '<p>Rainproof touchscreen sensitive mobile pouch mount for handlebars and mirror stalks. Compatible with screens up to 7 inches.</p>', '6 Months Warranty', 'mobile holder, gps, waterproof', 1, 1, 0, 0, 'active'),

(3, 'Mudsor Ultra-Protection 9H Tempered Glass Screen Protector', 'mudsor-9h-tempered-glass-screen-protector', 'MUD-SP-003', '890123456783', '39199090', 3, 6, 1, 699.00, 349.00, 18.00, 500, '/uploads/products/screen_guard.jpg', '<p>Prevent scratches and sun yellowing on your smart scooter touchscreen display with anti-glare hydrophobic 9H tempered glass.</p>', '7 Days Replacement', 'screen guard, tempered glass, touchscreen', 1, 0, 1, 1, 'active'),

(4, 'Mudsor Deluxe Breathable Air Mesh Cushion Seat Cover', 'mudsor-deluxe-breathable-mesh-seat-cover', 'MUD-SC-004', '890123456784', '87149990', 4, NULL, 2, 899.00, 499.00, 18.00, 220, '/uploads/products/seat_cover.jpg', '<p>3D Honeycomb ventilation prevents heating up under direct sunlight. Anti-slip bottom elastic grip for snug fit.</p>', '6 Months Color & Fabric Warranty', 'seat cover, cushion, mesh', 0, 1, 0, 0, 'active'),

(5, 'Mudsor Heavy Duty 100% Waterproof Heavy Nylon Body Cover with Lock Bag', 'mudsor-waterproof-heavy-nylon-body-cover', 'MUD-BC-005', '890123456785', '63079090', 3, 5, 3, 1499.00, 899.00, 18.00, 180, '/uploads/products/body_cover.jpg', '<p>Dustproof, UV resistant, and 100% waterproof custom-cut scooter cover with mirror pockets and buckle strap.</p>', '1 Year Tear Warranty', 'body cover, rain cover', 1, 1, 1, 0, 'active'),

(6, 'Mudsor EV Portable Fast Charger Wall Mount Bracket Dock & Cable Organizer', 'mudsor-ev-charger-wall-mount-dock', 'MUD-CH-006', '890123456786', '87141090', 5, NULL, 1, 999.00, 449.00, 18.00, 100, '/uploads/products/charger_dock.jpg', '<p>Keep your EV home charger neatly organized and wall mounted safe from water drops and floor damage.</p>', '1 Year Warranty', 'charger, dock, holder', 0, 0, 1, 1, 'active');

-- 8. Product Scooter Compatibility Mapping (Linking Products to Scooter Models)
INSERT INTO `product_scooter_compatibilities` (`product_id`, `brand_id`, `scooter_model_id`) VALUES
-- Product 1 (Crash Guard) compatible with Ola S1 Pro, S1 X, S1 Air, Ather 450X, TVS iQube
(1, 1, 1), (1, 1, 2), (1, 1, 3), (1, 2, 4), (1, 3, 7),
-- Product 2 (Mobile Holder) compatible with ALL scooters!
(2, 1, 1), (2, 1, 2), (2, 1, 3), (2, 2, 4), (2, 2, 5), (2, 2, 6), (2, 3, 7), (2, 4, 8), (2, 5, 9), (2, 6, 10),
-- Product 3 (Screen Protector) compatible with Ola S1 Pro, Ather 450X
(3, 1, 1), (3, 2, 4),
-- Product 4 (Seat Cover) compatible with Ather 450X, Ather Rizta
(4, 2, 4), (4, 2, 5),
-- Product 5 (Body Cover) compatible with Ola S1 Pro, Ola S1 Air, TVS iQube, Chetak
(5, 1, 1), (5, 1, 3), (5, 3, 7), (5, 4, 8),
-- Product 6 (Charger Mount) compatible with Ola S1 Pro, S1 X, Ather 450X
(6, 1, 1), (6, 1, 2), (6, 2, 4);

-- 9. Homepage Dynamic Sections Config
INSERT INTO `homepage_sections` (`id`, `section_key`, `title`, `subtitle`, `status`, `sort_order`, `settings`) VALUES
(1, 'announcement_bar', 'Free Shipping Pan-India on Orders Above ₹999 | 100% Genuine Electric Scooter Accessories', NULL, 'active', 1, '{"bg_color": "#dc2626", "text_color": "#ffffff"}'),
(2, 'hero_slider', 'Upgrade Your Ride With Mudsor', 'Precision Engineered Accessories for Ola, Ather, TVS & Chetak', 'active', 2, '{"slides": [{"image": "/assets/images/banner1.jpg", "title": "Heavy-Duty Crash Guards", "link": "/shop?category=footrests-crash-guards"}, {"image": "/assets/images/banner2.jpg", "title": "Waterproof Mobile Holders", "link": "/shop?category=mobile-holders-mounts"}]}'),
(3, 'scooter_selector', 'Find Compatible Accessories For Your Scooter', 'Select your brand and model for 100% guaranteed fitment', 'active', 3, NULL),
(4, 'featured_categories', 'Explore Categories', 'Handpicked quality accessories for every electric vehicle', 'active', 4, NULL),
(5, 'featured_products', 'Featured Accessories', 'Top rated accessories built for longevity & performance', 'active', 5, NULL),
(6, 'best_sellers', 'Best Sellers', 'Customer favorite accessories selling fast', 'active', 6, NULL),
(7, 'brand_logos', 'Shop By Scooter Brand', 'Compatible with India\'s leading Electric Scooter brands', 'active', 7, NULL),
(8, 'customer_reviews', 'Backed Trust In Every Order', 'What real scooter owners say about Mudsor accessories', 'active', 8, NULL),
(9, 'newsletter', 'Subscribe & Save 10%', 'Get exclusive launch offers and compatibility guide updates direct to your inbox', 'active', 9, NULL);

-- 10. Sample Product Reviews
INSERT INTO `reviews` (`id`, `product_id`, `customer_name`, `rating`, `title`, `comment`, `admin_reply`, `status`) VALUES
(1, 1, 'Rahul Sharma (Ola S1 Pro Owner)', 5, 'Super Sturdy Fit!', 'Fitted this crash guard on my Ola S1 Pro Gen 2. Perfect hole alignment, heavy gauge steel pipe, and the footrest feels premium. Worth every rupee.', 'Thank you Rahul! We use high grade stainless steel for all Mudsor guards.', 'approved'),
(2, 2, 'Amit Patel (Ather 450X Owner)', 5, 'No vibration during high speed!', 'The anti-vibration rubber damper works really well. Navigation touchscreen is completely usable inside the waterproof bag.', NULL, 'approved');

-- 11. Static CMS Pages
INSERT INTO `cms_pages` (`id`, `title`, `slug`, `content`, `meta_title`, `meta_description`) VALUES
(1, 'About Mudsor', 'about-us', '<h2>Welcome to Mudsor</h2><p>Mudsor (Rughwani Enterprises) is India’s premier manufacturer and distributor of specialized electric scooter accessories. Built with a passion for innovation and durability, our products are engineered specifically for modern Indian EVs including Ola Electric, Ather Energy, TVS iQube, Bajaj Chetak, Hero Vida, and more.</p>', 'About Mudsor - Electric Scooter Accessories Leader', 'Learn about Mudsor premium EV accessories engineered for Ola, Ather, TVS, and Chetak scooters.'),

(2, 'Contact Us', 'contact-us', '<h2>Get In Touch</h2><p><strong>Mudsor (Rughwani Enterprises)</strong><br>Owner: Jass Rughwani<br>Address: Floor 3rd, M-192, Block M, Pocket N, Sector 3, Bawana Industrial Area, New Delhi - 110039<br>Email: mudsorinfo@gmail.com<br>Phone: +91 9217714452<br>GSTIN: 07FLOPR6641L1Z8</p>', 'Contact Mudsor Customer Support', 'Contact Mudsor for order support, dealership inquiries, and compatibility help.'),

(3, 'Privacy Policy', 'privacy-policy', '<h2>Privacy Policy</h2><p>Your privacy is important to us. Mudsor collects personal data strictly to process orders and deliver products efficiently. We never sell customer information to third parties.</p>', 'Privacy Policy - Mudsor', 'Mudsor official privacy policy.'),

(4, 'Terms & Conditions', 'terms-and-conditions', '<h2>Terms & Conditions</h2><p>By placing an order on Mudsor, you agree to our terms of service, payment processing policies, and shipping guidelines.</p>', 'Terms & Conditions - Mudsor', 'Mudsor terms and conditions.'),

(5, 'Refund & Return Policy', 'refund-policy', '<h2>Refund & Return Policy</h2><p>Mudsor offers a hassle-free 7-day return policy for unused, unopened accessories in original packaging. If you receive a damaged product, notify us within 48 hours for immediate replacement.</p>', 'Refund & Return Policy - Mudsor', 'Mudsor return and replacement guidelines.'),

(6, 'Shipping Policy', 'shipping-policy', '<h2>Shipping Policy</h2><p>Orders are dispatched within 24-48 hours. Express delivery available across India via premium courier partners (Delhivery, BlueDart, Bluedart, Bluedart, DTDC).</p>', 'Shipping Policy - Mudsor', 'Mudsor pan-India shipping timeline and details.');

-- 12. Global Website & Theme Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Mudsor'),
('site_tagline', 'Electric Scooter Accessories & Parts'),
('owner_name', 'Jass Rughwani'),
('company_legal_name', 'Rughwani Enterprises'),
('contact_email', 'mudsorinfo@gmail.com'),
('contact_phone', '+91 9217714452'),
('gstin', '07FLOPR6641L1Z8'),
('address', 'Floor 3rd, M-192, Block M, Pocket N, Sector 3, Bawana Industrial Area, New Delhi - 110039'),
('currency_symbol', '₹'),
('primary_color', '#dc2626'),
('secondary_color', '#1e293b'),
('free_shipping_threshold', '999'),
('shipping_charge', '79'),
('tax_rate', '18'),
('theme_logo', '/assets/images/mudsor-logo.png'),
('theme_favicon', '/assets/images/favicon.ico'),
('items_per_page_shop', '12'),
('items_per_page_admin', '20'),
('pagination_mode', 'pagination');

SET FOREIGN_KEY_CHECKS = 1;
