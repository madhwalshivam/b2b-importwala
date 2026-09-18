<?php
require "vendor/autoload.php";
require "app/Core/Database.php";
require "app/Core/Model.php";
require "app/Models/ProductColor.php";
require "app/Models/ProductColorSize.php";
require "app/Models/ProductSpecification.php";
require "app/Models/Product.php";
require "app/Models/Category.php";
require "app/Models/Brand.php";
require "app/Services/VariationService.php";

$db = \App\Core\Database::getInstance();

// 1. Initial State
$productId = 510;
$db->query("DELETE FROM product_colors WHERE product_id=510");
$db->query("DELETE FROM product_specifications WHERE product_id=510");

// 2. Save Variations
$vs = new \App\Services\VariationService();
$vs->saveNestedVariations($productId, "double", [
    [
        "id" => 0,
        "color_name" => "Flow Color",
        "color_code" => "#ff00ff",
        "sizes" => [
            [
                "id" => 0,
                "size_label" => "L",
                "price" => 99.9
            ]
        ]
    ]
]);

// 3. Save Spec
$specModel = new \App\Models\ProductSpecification();
$specModel->saveSingleSpec($productId, 0, "Flow Spec", "Flow Val");

// 4. Print DB State
echo "--- DB AFTER AJAX ---\n";
var_dump($db->query("SELECT * FROM product_colors WHERE product_id=510")->fetchAll(PDO::FETCH_ASSOC));
var_dump($db->query("SELECT * FROM product_specifications WHERE product_id=510")->fetchAll(PDO::FETCH_ASSOC));

// 5. Run Update (Mocking ProductController::update)
// We'll mimic what update() does to the product record
$productModel = new \App\Models\Product();
$productModel->update($productId, [
    'name' => 'Updated Product',
    'price' => 100,
    // (mocking the other fields here)
]);

// 6. Print DB State again
echo "--- DB AFTER UPDATE ---\n";
var_dump($db->query("SELECT * FROM product_colors WHERE product_id=510")->fetchAll(PDO::FETCH_ASSOC));
var_dump($db->query("SELECT * FROM product_specifications WHERE product_id=510")->fetchAll(PDO::FETCH_ASSOC));
