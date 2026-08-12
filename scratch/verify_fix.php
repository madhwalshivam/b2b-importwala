<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});
require_once __DIR__ . '/../app/Helpers/Functions.php';

$productModel = new App\Models\Product();
$products = $productModel->getComparisonData([7, 9]);

echo "Fetched Products Count: " . count($products) . "\n";
foreach ($products as $p) {
    echo "ID: {$p['id']} | Name: {$p['name']} | Price: {$p['price']} | Sale: {$p['sale_price']} | Image: {$p['main_image']}\n";
    echo "Specs Count: " . count($p['specifications'] ?? []) . "\n";
    echo "Description snippet: " . substr(strip_tags($p['description']), 0, 50) . "...\n";
    echo "-------------------------------\n";
}

$specKeysMap = [];
$standardSpecs = [
    'material' => 'Material',
    'finish' => 'Finish',
    'weight_grams' => 'Weight',
    'warranty_months' => 'Warranty',
    'hsn_code' => 'HSN Code',
    'tax_percent' => 'GST Tax Rate'
];

foreach ($products as $p) {
    foreach ($standardSpecs as $col => $label) {
        if (!empty($p[$col])) {
            $specKeysMap[$label] = true;
        }
    }
    if (!empty($p['specifications']) && is_array($p['specifications'])) {
        foreach ($p['specifications'] as $spec) {
            if (!empty($spec['spec_key'])) {
                $specKeysMap[trim($spec['spec_key'])] = true;
            }
        }
    }
}
$specKeys = array_keys($specKeysMap);
echo "Dynamic Spec Keys Union: " . implode(', ', $specKeys) . "\n";
