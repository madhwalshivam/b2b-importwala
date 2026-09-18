<?php
require "vendor/autoload.php";
require "app/Core/Database.php";
require "app/Core/Model.php";
require "app/Models/ProductColor.php";
require "app/Models/ProductColorSize.php";
require "app/Services/VariationService.php";

$db = \App\Core\Database::getInstance();
$color = $db->query("SELECT id, product_id, color_name FROM product_colors LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$colorId = $color['id'];
$productId = $color['product_id'];
$colorName = $color['color_name'];

$size = $db->query("SELECT id, size_label FROM product_color_sizes WHERE color_id = $colorId LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$sizeId = $size['id'];
$sizeLabel = $size['size_label'];

$payload = json_encode([
    'variation_mode' => 'double',
    'colors' => [
        [
            'id' => (string)$colorId, // Test with string ID from JSON
            'color_name' => $colorName,
            'sizes' => [
                [
                    'id' => (string)$sizeId, // Test with string ID from JSON
                    'size_label' => $sizeLabel,
                    'price' => "999.99",
                    'stock_qty' => "50",
                    'sku' => "TESTSKU"
                ]
            ]
        ]
    ]
]);

$data = json_decode($payload, true);
$vs = new \App\Services\VariationService();
try {
    $vs->saveNestedVariations($productId, $data['variation_mode'], $data['colors']);
    echo "Saved. ";
    $newSize = $db->query("SELECT * FROM product_color_sizes WHERE id = $sizeId")->fetch(PDO::FETCH_ASSOC);
    echo "New price: {$newSize['price']}\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
