<?php
// Seeder for Enterprise Production Database (ImportWala / Everful Wholesale Clone)

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $dbConfig = require __DIR__ . '/../config/database.php';
    Database::init(['connections' => ['mysql' => $dbConfig]]);
    $db = Database::getWriteConnection();

    echo "Seeding Enterprise Wholesale Sample Data...\n";

    // 1. Currencies
    $db->exec("INSERT INTO `currencies` (`code`, `symbol`, `name`, `exchange_rate`, `is_default`) VALUES
        ('USD', '$', 'US Dollar', 1.000000, 1),
        ('INR', '₹', 'Indian Rupee', 83.500000, 0),
        ('EUR', '€', 'Euro', 0.920000, 0),
        ('GBP', '£', 'British Pound', 0.780000, 0)
        ON DUPLICATE KEY UPDATE `exchange_rate` = VALUES(`exchange_rate`);");

    // 2. Categories
    $categories = [
        ['id' => 1, 'slug' => 'jewelry-accessories', 'name' => 'Jewelry & Accessories', 'image_url' => '/assets/images/placeholder.jpg', 'sort_order' => 1, 'is_featured' => 1],
        ['id' => 2, 'slug' => 'hats-headwear', 'name' => 'Hats & Headwear', 'image_url' => '/assets/images/placeholder.jpg', 'sort_order' => 2, 'is_featured' => 1],
        ['id' => 3, 'slug' => 'stationery-office', 'name' => 'Stationery & Office', 'image_url' => '/assets/images/placeholder.jpg', 'sort_order' => 3, 'is_featured' => 1],
        ['id' => 4, 'slug' => 'socks-apparel', 'name' => 'Socks & Apparel', 'image_url' => '/assets/images/placeholder.jpg', 'sort_order' => 4, 'is_featured' => 1],
    ];

    foreach ($categories as $cat) {
        $stmt = $db->prepare("INSERT INTO `categories` (`id`, `slug`, `name`, `image_url`, `sort_order`, `is_featured`) 
            VALUES (:id, :slug, :name, :image_url, :sort_order, :is_featured)
            ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `image_url` = VALUES(`image_url`);");
        $stmt->execute($cat);
    }

    // 3. Products
    $products = [
        [
            'id' => 1,
            'sku' => 'JEW-GOLD-HEART-01',
            'slug' => 'vintage-stainless-steel-18k-gold-heart-pendant-necklace',
            'title' => 'Vintage Stainless Steel 18K Gold Plated Heart Animal Alphabet Pendant Necklace',
            'category_id' => 1,
            'short_description' => 'Waterproof, tarnish-free 18K gold plated stainless steel wholesale jewelry.',
            'full_description' => '<p>High-grade 316L stainless steel with 18K real gold PVD plating. Ideal for retail boutiques and online wholesale stores.</p>',
            'main_image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=600&q=80',
            'base_price' => 0.9200,
            'moq' => 5,
            'status' => 'active',
            'is_featured' => 1,
            'is_best_seller' => 1,
            'sales_count' => 1420,
        ],
        [
            'id' => 2,
            'sku' => 'HAT-COWBOY-FEDORA-02',
            'slug' => 'retro-wide-brim-felt-cowboy-fedora-hat',
            'title' => 'Retro Wide Brim Felt Cowboy Fedora Hat For Men Women Vintage Party Jazz Cap',
            'category_id' => 2,
            'short_description' => 'Unisex retro Western cowboy hat with belt buckle trim.',
            'full_description' => '<p>Premium felt material wide brim Western cowboy jazz hat. Perfect autumn/winter accessory.</p>',
            'main_image' => 'https://images.unsplash.com/photo-1534215754734-18e52d13e346?auto=format&fit=crop&w=600&q=80',
            'base_price' => 3.5000,
            'moq' => 2,
            'status' => 'active',
            'is_featured' => 1,
            'is_new_arrival' => 1,
            'sales_count' => 890,
        ],
        [
            'id' => 3,
            'sku' => 'STAT-PASTEL-GELPEN-03',
            'slug' => 'creative-6pcs-pastel-gel-pen-set-05mm',
            'title' => 'Creative 6pcs Set Soft Pastel Morandi Color Gel Pens 0.5mm Quick Dry Ink',
            'category_id' => 3,
            'short_description' => '0.5mm black ink quick-drying soft tactile body aesthetic office gel pens.',
            'full_description' => '<p>Smooth writing, 0.5mm tip size gel pen set with soft-touch rubberized barrel.</p>',
            'main_image' => 'https://images.unsplash.com/photo-1583485088034-697b5bc54ccd?auto=format&fit=crop&w=600&q=80',
            'base_price' => 1.2000,
            'moq' => 10,
            'status' => 'active',
            'is_featured' => 1,
            'sales_count' => 3100,
        ],
        [
            'id' => 4,
            'sku' => 'SOCK-CREW-VINTAGE-04',
            'slug' => 'vintage-ribbed-cotton-crew-socks-pack',
            'title' => 'Unisex Vintage Ribbed Solid Color Cotton Crew Socks Comfortable Breathable',
            'category_id' => 4,
            'short_description' => 'High elasticity comb cotton athletic casual daily crew socks.',
            'full_description' => '<p>Breathable soft combed cotton crew socks available in multiple earth tones.</p>',
            'main_image' => 'https://images.unsplash.com/photo-1586350977771-b3b0abd50c82?auto=format&fit=crop&w=600&q=80',
            'base_price' => 0.7500,
            'moq' => 12,
            'status' => 'active',
            'is_featured' => 1,
            'is_best_seller' => 1,
            'sales_count' => 5400,
        ]
    ];

    foreach ($products as $prod) {
        $stmt = $db->prepare("INSERT INTO `products` (`id`, `sku`, `slug`, `title`, `category_id`, `short_description`, `full_description`, `main_image`, `base_price`, `moq`, `status`, `is_featured`, `is_new_arrival`, `is_best_seller`, `sales_count`) 
            VALUES (:id, :sku, :slug, :title, :category_id, :short_description, :full_description, :main_image, :base_price, :moq, :status, :is_featured, :is_new_arrival, :is_best_seller, :sales_count)
            ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `base_price` = VALUES(`base_price`), `main_image` = VALUES(`main_image`);");
        $stmt->execute([
            'id' => $prod['id'],
            'sku' => $prod['sku'],
            'slug' => $prod['slug'],
            'title' => $prod['title'],
            'category_id' => $prod['category_id'],
            'short_description' => $prod['short_description'],
            'full_description' => $prod['full_description'],
            'main_image' => $prod['main_image'],
            'base_price' => $prod['base_price'],
            'moq' => $prod['moq'],
            'status' => $prod['status'],
            'is_featured' => $prod['is_featured'],
            'is_new_arrival' => $prod['is_new_arrival'] ?? 0,
            'is_best_seller' => $prod['is_best_seller'] ?? 0,
            'sales_count' => $prod['sales_count'],
        ]);
    }

    // 4. Product Variations
    $variations = [
        ['id' => 1, 'product_id' => 1, 'sku' => 'JEW-GOLD-HEART-M', 'color_name' => 'Letter M', 'stock_qty' => 500],
        ['id' => 2, 'product_id' => 1, 'sku' => 'JEW-GOLD-HEART-A', 'color_name' => 'Letter A', 'stock_qty' => 450],
        ['id' => 3, 'product_id' => 1, 'sku' => 'JEW-GOLD-HEART-S', 'color_name' => 'Letter S', 'stock_qty' => 600],
        ['id' => 4, 'product_id' => 2, 'sku' => 'HAT-COWBOY-BROWN', 'color_name' => 'Camel Brown', 'stock_qty' => 200],
        ['id' => 5, 'product_id' => 2, 'sku' => 'HAT-COWBOY-BLACK', 'color_name' => 'Matte Black', 'stock_qty' => 180],
        ['id' => 6, 'product_id' => 3, 'sku' => 'STAT-PEN-SET-MORANDI', 'color_name' => 'Morandi Set (6 Colors)', 'stock_qty' => 1200],
        ['id' => 7, 'product_id' => 4, 'sku' => 'SOCK-CREW-WHITE', 'color_name' => 'Off White', 'stock_qty' => 3000],
        ['id' => 8, 'product_id' => 4, 'sku' => 'SOCK-CREW-BEIGE', 'color_name' => 'Beige', 'stock_qty' => 2500],
    ];

    foreach ($variations as $var) {
        $stmt = $db->prepare("INSERT INTO `product_variations` (`id`, `product_id`, `sku`, `color_name`, `stock_qty`, `status`) 
            VALUES (:id, :product_id, :sku, :color_name, :stock_qty, 'active')
            ON DUPLICATE KEY UPDATE `stock_qty` = VALUES(`stock_qty`);");
        $stmt->execute($var);
    }

    // 5. Wholesale Tiered Prices
    $tieredPrices = [
        ['product_id' => 1, 'min_qty' => 5, 'max_qty' => 49, 'unit_price' => 0.9200],
        ['product_id' => 1, 'min_qty' => 50, 'max_qty' => 199, 'unit_price' => 0.8500],
        ['product_id' => 1, 'min_qty' => 200, 'max_qty' => 499, 'unit_price' => 0.7800],
        ['product_id' => 1, 'min_qty' => 500, 'max_qty' => null, 'unit_price' => 0.7200],

        ['product_id' => 2, 'min_qty' => 2, 'max_qty' => 19, 'unit_price' => 3.5000],
        ['product_id' => 2, 'min_qty' => 20, 'max_qty' => 99, 'unit_price' => 3.1000],
        ['product_id' => 2, 'min_qty' => 100, 'max_qty' => null, 'unit_price' => 2.8000],
    ];

    $db->exec("TRUNCATE TABLE `tiered_prices`");
    foreach ($tieredPrices as $tier) {
        $stmt = $db->prepare("INSERT INTO `tiered_prices` (`product_id`, `min_qty`, `max_qty`, `unit_price`) VALUES (:product_id, :min_qty, :max_qty, :unit_price)");
        $stmt->execute($tier);
    }

    echo "Enterprise Wholesale Sample Data Seeded Successfully!\n";

} catch (\Throwable $e) {
    echo "Seeding Error: " . $e->getMessage() . "\n";
    exit(1);
}
