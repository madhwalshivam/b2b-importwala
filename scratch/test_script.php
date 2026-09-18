<?php
require "vendor/autoload.php";
require "app/Core/Database.php";
require "app/Core/Model.php";
require "app/Models/ProductColor.php";
require "app/Models/ProductColorSize.php";
require "app/Models/ProductSpecification.php";
require "app/Models/Product.php";
require "app/Services/VariationService.php";

$db = \App\Core\Database::getInstance();
$vs = new \App\Services\VariationService();
try {
    $vs->saveNestedVariations(510, "double", [
        [
            "id" => 0,
            "color_name" => "Test Color",
            "color_code" => "#ff0000",
            "sizes" => [
                [
                    "id" => 0,
                    "size_label" => "M",
                    "price" => 15.5
                ]
            ]
        ]
    ]);
    echo "Variation Success\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$specModel = new \App\Models\ProductSpecification();
try {
    $savedId = $specModel->saveSingleSpec(510, 0, "Test Key", "Test Value");
    echo "Saved spec ID: $savedId\n";
} catch (Exception $e) {
    echo "Spec Error: " . $e->getMessage() . "\n";
}
