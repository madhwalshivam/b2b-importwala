<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "1. Checking column link_url in featured_categories...\n";
$cols = $db->query("SHOW COLUMNS FROM featured_categories LIKE 'link_url'")->fetchAll();
if (empty($cols)) {
    $db->exec("ALTER TABLE featured_categories ADD COLUMN link_url VARCHAR(255) NULL AFTER image");
    echo "Added link_url column to featured_categories.\n";
} else {
    echo "link_url column already exists.\n";
}

echo "2. Checking Section 1 items (featured_categories)...\n";
$section1Count = (int)$db->query("SELECT COUNT(*) FROM featured_categories")->fetchColumn();

// If count is 0 or low, let's ensure we have high quality items matching Jumia design
$section1Items = [
    [
        'name' => 'Home Essentials',
        'slug' => 'home-essentials',
        'image' => '/uploads/featured_categories/home-decor.jpg',
        'link_url' => '/category/home-decor',
        'sort_order' => 1,
        'is_active' => 1
    ],
    [
        'name' => 'Beauty Corner',
        'slug' => 'beauty-corner',
        'image' => '/uploads/featured_categories/viewall-beauty.jpg',
        'link_url' => '/category/beauty',
        'sort_order' => 2,
        'is_active' => 1
    ],
    [
        'name' => 'Appliance Deals',
        'slug' => 'appliance-deals',
        'image' => '/uploads/featured_categories/kitchen-tools.jpg',
        'link_url' => '/category/kitchen-dining',
        'sort_order' => 3,
        'is_active' => 1
    ],
    [
        'name' => 'Men\'s Fashion',
        'slug' => 'mens-fashion',
        'image' => '/uploads/featured_categories/backpacks.jpg',
        'link_url' => '/category/fashion-accessories',
        'sort_order' => 4,
        'is_active' => 1
    ],
    [
        'name' => 'Tech Deals',
        'slug' => 'tech-deals',
        'image' => '/uploads/featured_categories/viewall-electronics.jpg',
        'link_url' => '/category/consumer-electronics',
        'sort_order' => 5,
        'is_active' => 1
    ],
    [
        'name' => 'Women\'s Fashion',
        'slug' => 'womens-fashion',
        'image' => '/uploads/featured_categories/shoulder-bags.jpg',
        'link_url' => '/category/bags-luggage',
        'sort_order' => 6,
        'is_active' => 1
    ],
];

foreach ($section1Items as $item) {
    $stmt = $db->prepare("SELECT id FROM featured_categories WHERE name = ? OR slug = ?");
    $stmt->execute([$item['name'], $item['slug']]);
    $existing = $stmt->fetch();
    if (!$existing) {
        $ins = $db->prepare("INSERT INTO featured_categories (name, slug, image, link_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([$item['name'], $item['slug'], $item['image'], $item['link_url'], $item['sort_order'], $item['is_active']]);
        echo "Inserted Section 1 item: {$item['name']}\n";
    } else {
        $upd = $db->prepare("UPDATE featured_categories SET link_url = COALESCE(NULLIF(link_url, ''), ?) WHERE id = ?");
        $upd->execute([$item['link_url'], $existing['id']]);
    }
}

echo "3. Checking Section 2 items (featured_subcategories)...\n";
$section2Items = [
    [
        'name' => 'Mobile Phones',
        'slug' => 'mobile-phones',
        'image' => '/uploads/featured_categories/phone-cases.jpg',
        'link_url' => '/category/consumer-electronics',
        'sort_order' => 1,
        'is_active' => 1
    ],
    [
        'name' => 'Mobile Accessories',
        'slug' => 'mobile-accessories',
        'image' => '/uploads/featured_categories/wireless-earbuds.jpg',
        'link_url' => '/category/consumer-electronics',
        'sort_order' => 2,
        'is_active' => 1
    ],
    [
        'name' => 'Computing',
        'slug' => 'computing',
        'image' => '/uploads/featured_categories/usb-cables.jpg',
        'link_url' => '/category/consumer-electronics',
        'sort_order' => 3,
        'is_active' => 1
    ],
    [
        'name' => 'Electronics',
        'slug' => 'electronics',
        'image' => '/uploads/featured_categories/viewall-electronics.jpg',
        'link_url' => '/category/consumer-electronics',
        'sort_order' => 4,
        'is_active' => 1
    ],
    [
        'name' => 'Men\'s Clothing',
        'slug' => 'mens-clothing',
        'image' => '/uploads/featured_categories/scarves.jpg',
        'link_url' => '/category/fashion-accessories',
        'sort_order' => 5,
        'is_active' => 1
    ],
    [
        'name' => 'Men\'s Shoes',
        'slug' => 'mens-shoes',
        'image' => '/uploads/featured_categories/sports-watches.jpg',
        'link_url' => '/category/fashion-accessories',
        'sort_order' => 6,
        'is_active' => 1
    ],
    [
        'name' => 'Women\'s Clothing',
        'slug' => 'womens-clothing',
        'image' => '/uploads/featured_categories/hair-accessories.jpg',
        'link_url' => '/category/fashion-accessories',
        'sort_order' => 7,
        'is_active' => 1
    ],
    [
        'name' => 'Women\'s Shoes',
        'slug' => 'womens-shoes',
        'image' => '/uploads/featured_categories/tote-bags.jpg',
        'link_url' => '/category/bags-luggage',
        'sort_order' => 8,
        'is_active' => 1
    ],
    [
        'name' => 'Kid\'s Fashion',
        'slug' => 'kids-fashion',
        'image' => '/uploads/featured_categories/kids-hair.jpg',
        'link_url' => '/category/mother-baby',
        'sort_order' => 9,
        'is_active' => 1
    ],
    [
        'name' => 'Generators & Inverters',
        'slug' => 'generators-inverters',
        'image' => '/uploads/featured_categories/portable-fans.jpg',
        'link_url' => '/category/consumer-electronics',
        'sort_order' => 10,
        'is_active' => 1
    ],
    [
        'name' => 'Health & Beauty',
        'slug' => 'health-beauty',
        'image' => '/uploads/featured_categories/skincare-tools.jpg',
        'link_url' => '/category/beauty',
        'sort_order' => 11,
        'is_active' => 1
    ],
    [
        'name' => 'Home & Office',
        'slug' => 'home-office',
        'image' => '/uploads/featured_categories/desk-organizers.jpg',
        'link_url' => '/category/office-school-supplies',
        'sort_order' => 12,
        'is_active' => 1
    ],
];

// Get first Section 1 category ID to assign as default featured_category_id if needed
$firstCatId = (int)$db->query("SELECT id FROM featured_categories ORDER BY sort_order ASC, id ASC LIMIT 1")->fetchColumn();

foreach ($section2Items as $sub) {
    $stmt = $db->prepare("SELECT id FROM featured_subcategories WHERE name = ? OR slug = ?");
    $stmt->execute([$sub['name'], $sub['slug']]);
    $existing = $stmt->fetch();
    if (!$existing) {
        $ins = $db->prepare("INSERT INTO featured_subcategories (featured_category_id, name, slug, image, link_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$firstCatId, $sub['name'], $sub['slug'], $sub['image'], $sub['link_url'], $sub['sort_order'], $sub['is_active']]);
        echo "Inserted Section 2 item: {$sub['name']}\n";
    }
}

echo "Migration and Seeding complete!\n";
