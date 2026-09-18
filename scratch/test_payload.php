<?php
require "vendor/autoload.php";
require "app/Core/Database.php";
$db = \App\Core\Database::getInstance();

require "app/Models/ProductColor.php";
require "app/Models/ProductColorSize.php";
require "app/Services/VariationService.php";

$vs = new \App\Services\VariationService();
$color = $db->query("SELECT id, product_id FROM product_colors LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$colorId = $color['id'];
$productId = $color['product_id'];

$sizeId = $db->query("SELECT id FROM product_color_sizes WHERE color_id = $colorId LIMIT 1")->fetchColumn();

if (!$sizeId) {
    die("No sizes for color $colorId\n");
}

$colorsData = [[ 
    "id" => $colorId, 
    "color_name" => "Test Color", 
    "sizes" => [[ 
        "id" => $sizeId, 
        "size_label" => "Test Size", 
        "price" => 999.99 
    ]] 
]]; 

try { 
    $vs->saveNestedVariations($productId, "double", $colorsData); 
    echo "Success!\n"; 
    $price = $db->query("SELECT price FROM product_color_sizes WHERE id = $sizeId")->fetchColumn(); 
    echo "Price in DB: $price\n"; 
} catch (Throwable $e) { 
    echo "Error: " . $e->getMessage() . "\n"; 
}
