<?php
require "vendor/autoload.php";
require "app/Core/Database.php";

$db = \App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM product_color_sizes LIMIT 1");
$existingSize = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$existingSize) {
    die("No size found to test with");
}

$colorId = $existingSize['color_id'];
$sizeId = $existingSize['id'];

// Get color
$stmt2 = $db->query("SELECT * FROM product_colors WHERE id = " . (int)$colorId);
$existingColor = $stmt2->fetch(PDO::FETCH_ASSOC);
$productId = $existingColor['product_id'];


require "app/Models/ProductColor.php";
require "app/Models/ProductColorSize.php";
require "app/Services/VariationService.php";

$vService = new \App\Services\VariationService();

$colorsData = [[
    "id" => $colorId,
    "color_name" => $existingColor['color_name'],
    "sizes" => [[
        "id" => $sizeId,
        "size_label" => "Test Size " . time(),
        "price" => 123.45,
        "stock_qty" => 50
    ]]
]];

try {
    $vService->saveNestedVariations($productId, "double", $colorsData);
    echo "Success\n";
    
    // Check DB
    $stmt = $db->query("SELECT price FROM product_color_sizes WHERE id = $sizeId");
    $updatedSize = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "New price in DB: " . $updatedSize['price'] . "\n";
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
