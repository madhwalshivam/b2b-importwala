<?php
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Model.php';
require __DIR__ . '/../app/Models/Product.php';
require __DIR__ . '/../app/Models/ProductVariant.php';
require __DIR__ . '/../app/Models/ProductColor.php';
require __DIR__ . '/../app/Models/ProductColorSize.php';
require __DIR__ . '/../app/Services/VariationService.php';

$config = require __DIR__ . '/../config/database.php';
\App\Core\Database::init($config);

$pvModel = new \App\Models\ProductVariant();
$productModel = new \App\Models\Product();
$p = $productModel->find(521);
if (!$p) {
    $stmt = \App\Core\Database::getInstance()->query("SELECT * FROM products WHERE id = 586 OR slug LIKE '%586%'");
    $p = $stmt->fetch(\PDO::FETCH_ASSOC);
}

if ($p) {
    echo "Product ID: {$p['id']}, Name: {$p['name']}, Mode: {$p['variation_mode']}, Price: {$p['price']}, SalePrice: {$p['sale_price']}\n";
    $matrix = $pvModel->getVariantMatrix($p['id']);
    echo json_encode($matrix, JSON_PRETTY_PRINT) . "\n";
}
