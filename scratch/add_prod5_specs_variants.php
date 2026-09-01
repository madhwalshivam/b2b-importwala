<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

require_once ROOT_PATH . '/app/Helpers/Functions.php';
require_once ROOT_PATH . '/config/app.php';

$productId = 5;

// 1. Save 8 Specifications
$specModel = new App\Models\ProductSpecification();
$specs = [
    ['key' => 'Material & Build', 'value' => 'Military-Grade Polycarbonate + Flexible Impact TPU Bumper'],
    ['key' => 'Drop Protection', 'value' => '10 Feet (3 Meters) Shockproof Certified Standard'],
    ['key' => 'Surface Finish', 'value' => 'Anti-Scratch Matte Coating with Oil-Resistant Oleophobic Shield'],
    ['key' => 'Screen & Camera Guard', 'value' => '1.5mm Raised Screen Bezel & 2.0mm Camera Ring Protection'],
    ['key' => 'MagSafe & Wireless', 'value' => 'Built-in 36 N52 Neodymium Magnets • Full Qi Wireless Compatibility'],
    ['key' => 'Weight & Profile', 'value' => 'Ultra-Light 40 Grams • 1.2mm Slim Edge Ergonomic Grip'],
    ['key' => 'Tactile Buttons', 'value' => 'Independent Metallic Responsive Click Action Buttons'],
    ['key' => 'Warranty Coverage', 'value' => '12 Months Replacement Warranty Against Yellowing & Manufacturing Defects']
];
$specModel->saveSpecifications($productId, $specs);
echo "Added 8 specifications for product ID {$productId}.\n";

// 2. Add 2 New Variants (Product 5 currently has variants 7, 8, 9)
$variantModel = new App\Models\ProductVariant();

// Variant 4: Emerald Pine Green
$v4 = [
    'product_id'      => $productId,
    'variant_code'    => 'MUD-BC-EMR',
    'image_url'       => 'uploads/products/phone-cases.jpg',
    'attribute_label' => 'Color Edition',
    'attribute_value' => 'Emerald Pine Green',
    'weight'          => '0.04 kg',
    'dimensions'      => '6.7 inch Fit',
    'stock_quantity'  => 80,
    'wholesale_price' => 899.00,
    'one_piece_price' => 1499.00,
    'sort_order'      => 4,
    'is_active'       => 1
];

// Variant 5: Deep Lavender Purple
$v5 = [
    'product_id'      => $productId,
    'variant_code'    => 'MUD-BC-PPL',
    'image_url'       => 'uploads/products/phone-cases.jpg',
    'attribute_label' => 'Color Edition',
    'attribute_value' => 'Deep Lavender Purple',
    'weight'          => '0.04 kg',
    'dimensions'      => '6.7 inch Fit',
    'stock_quantity'  => 55,
    'wholesale_price' => 949.00,
    'one_piece_price' => 1599.00,
    'sort_order'      => 5,
    'is_active'       => 1
];

$db = App\Core\Database::getInstance();
$check4 = $db->prepare("SELECT id FROM product_variants WHERE product_id = ? AND variant_code = ?");
$check4->execute([$productId, 'MUD-BC-EMR']);
if (!$check4->fetch()) {
    $id4 = $variantModel->createVariant($v4);
    echo "Created variant 4: Emerald Pine Green (ID: {$id4})\n";
} else {
    echo "Variant MUD-BC-EMR already exists.\n";
}

$check5 = $db->prepare("SELECT id FROM product_variants WHERE product_id = ? AND variant_code = ?");
$check5->execute([$productId, 'MUD-BC-PPL']);
if (!$check5->fetch()) {
    $id5 = $variantModel->createVariant($v5);
    echo "Created variant 5: Deep Lavender Purple (ID: {$id5})\n";
} else {
    echo "Variant MUD-BC-PPL already exists.\n";
}

try {
    \App\Infrastructure\Cache\CacheManager::getInstance()->flush();
    echo "Cache flushed successfully.\n";
} catch (\Throwable $e) {}
