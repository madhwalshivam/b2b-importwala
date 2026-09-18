<?php
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance();

$productId = 510;

// Check product variation_mode
$stmt = $db->prepare("SELECT id, name, variation_mode FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
echo "=== PRODUCT ===\n";
print_r($product);

// Check product_colors
$stmt2 = $db->prepare("SELECT * FROM product_colors WHERE product_id = ? ORDER BY display_order ASC, id ASC");
$stmt2->execute([$productId]);
$colors = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== product_colors (" . count($colors) . " rows) ===\n";
print_r($colors);

// Check sizes for each color
foreach ($colors as $c) {
    $stmt3 = $db->prepare("SELECT * FROM product_color_sizes WHERE color_id = ? ORDER BY display_order ASC");
    $stmt3->execute([$c['id']]);
    $sizes = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    echo "\n=== Sizes for color_id={$c['id']} ({$c['color_name']}) ===\n";
    print_r($sizes);
}

// Check product_specifications
$stmt4 = $db->prepare("SELECT * FROM product_specifications WHERE product_id = ?");
$stmt4->execute([$productId]);
$specs = $stmt4->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== product_specifications (" . count($specs) . " rows) ===\n";
print_r($specs);
