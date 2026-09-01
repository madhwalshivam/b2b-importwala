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

$productModel = new App\Models\Product();
$product = $productModel->find($productId);

$variantModel = new App\Models\ProductVariant();
$variants = $variantModel->getByProduct($productId, false);

$specModel = new App\Models\ProductSpecification();
$specifications = $specModel->getByProduct($productId);

echo "=== VERIFICATION SUMMARY ===\n";
echo "Product ID: " . $product['id'] . "\n";
echo "Product Name: " . $product['name'] . "\n";
echo "Total Specifications: " . count($specifications) . "\n";
foreach ($specifications as $i => $s) {
    echo "  " . ($i+1) . ". {$s['spec_key']}: {$s['spec_value']}\n";
}

echo "\nTotal Variants: " . count($variants) . "\n";
foreach ($variants as $i => $v) {
    echo "  " . ($i+1) . ". [{$v['variant_code']}] {$v['attribute_label']} - {$v['attribute_value']} | Wholesale: ₹{$v['wholesale_price']} | Retail: ₹{$v['one_piece_price']} | Stock: {$v['stock_quantity']}\n";
}
