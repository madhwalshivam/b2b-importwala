<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();

// Select all products between IW-JW-101 and IW-JW-147
$stmt = $db->prepare("SELECT id, sku, name FROM products WHERE sku >= 'IW-JW-101' AND sku <= 'IW-JW-147' ORDER BY id ASC");
$stmt->execute();
$productsToDelete = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($productsToDelete) . " products to delete:\n";
$ids = [];
foreach ($productsToDelete as $p) {
    echo " - ID: {$p['id']} | SKU: {$p['sku']} | Name: {$p['name']}\n";
    $ids[] = (int) $p['id'];
}

if (empty($ids)) {
    echo "No products found in range IW-JW-101 to IW-JW-147.\n";
    exit;
}

$inClause = implode(',', array_fill(0, count($ids), '?'));

// Child tables to clean up
$childTables = [
    'cart_items',
    'collection_card_products',
    'coupon_products',
    'homepage_compare_products',
    'homepage_section_products',
    'inquiry_items',
    'order_items',
    'product_badges',
    'product_brands',
    'product_categories',
    'product_faqs',
    'product_filter_attribute_values',
    'product_image_embeddings',
    'product_images',
    'product_included_items',
    'product_related',
    'product_scooter_compatibilities',
    'product_specifications',
    'product_variants',
    'product_variation_types',
    'product_variations',
    'product_vehicle_compatibility',
    'product_vehicle_images',
    'product_visual_signatures',
    'reviews',
    'testimonials',
    'tiered_prices',
    'top_deals_products',
    'wishlist',
    'wishlists'
];

foreach ($childTables as $t) {
    try {
        $delChild = $db->prepare("DELETE FROM `$t` WHERE product_id IN ($inClause)");
        $delChild->execute($ids);
        $count = $delChild->rowCount();
        if ($count > 0) {
            echo "Deleted $count rows from `$t`.\n";
        }
    } catch (\Throwable $e) {
        // Table might not exist or fail silently
    }
}

// Now delete from products table
$delProducts = $db->prepare("DELETE FROM products WHERE id IN ($inClause)");
$delProducts->execute($ids);
echo "SUCCESS: Deleted " . $delProducts->rowCount() . " products from `products` table.\n";

// Flush cache if CacheManager exists
if (class_exists('\App\Infrastructure\Cache\CacheManager')) {
    try {
        \App\Infrastructure\Cache\CacheManager::getInstance()->flush();
        echo "Cache flushed successfully.\n";
    } catch (\Throwable $e) {
        echo "Cache flush warning: " . $e->getMessage() . "\n";
    }
}
