<?php
require "vendor/autoload.php";
require "app/Core/Database.php";
require "app/Models/ProductColorSize.php";
$db = \App\Core\Database::getInstance();
$model = new \App\Models\ProductColorSize();
$sizes = $db->query("SELECT * FROM product_color_sizes LIMIT 1")->fetchAll(PDO::FETCH_ASSOC);

if(!empty($sizes)) {
    $id = $sizes[0]["id"];
    $data = $sizes[0];
    $data["price"] = 999.99;
    $res = $model->updateSize($id, $data);
    echo "Update Result: " . ($res ? "true" : "false") . "\n";
    $check = $db->query("SELECT price FROM product_color_sizes WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
    echo "New Price: " . $check["price"] . "\n";
}
