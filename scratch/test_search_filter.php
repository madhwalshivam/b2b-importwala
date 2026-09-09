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

use App\Services\SearchService;
use App\Services\FilterAttributeService;

$searchService = new SearchService();
$filterService = new FilterAttributeService();

echo "=== TESTING FILTER ATTRIBUTES STOREFRONT DATA ===\n";
$attrs = $filterService->getAttributesForCategory(null);
echo "Total Active Storefront Attributes: " . count($attrs) . "\n";
foreach ($attrs as $a) {
    if (!empty($a['options'])) {
        echo "Attr: {$a['name']} (ID: {$a['id']}) - Options Count: " . count($a['options']) . "\n";
        foreach ($a['options'] as $o) {
            echo "   -> Option: {$o['value']} (ID: {$o['id']})\n";
        }
    }
}

echo "\n=== TESTING SEARCH WITHOUT FILTERS ===\n";
$res1 = $searchService->search('', [], 10, 0);
echo "Total products: {$res1['total']}\n";
echo "Option counts sample: \n";
print_r(array_slice($res1['facets']['option_counts'], 0, 10, true));

echo "\n=== TESTING SEARCH WITH ATTR FILTER (Jewellery Type = Bracelet, option 61) ===\n";
$res2 = $searchService->search('', ['attr' => [3 => [61]]], 10, 0);
echo "Filtered total products: {$res2['total']}\n";
foreach ($res2['items'] as $item) {
    echo "  - Product ID: {$item['id']} | Name: {$item['name']}\n";
}

echo "\n=== TESTING SEARCH WITH ATTR FILTER (Gender = Men, option 67) ===\n";
$res3 = $searchService->search('', ['attr' => [4 => [67]]], 10, 0);
echo "Filtered total products: {$res3['total']}\n";
foreach ($res3['items'] as $item) {
    echo "  - Product ID: {$item['id']} | Name: {$item['name']}\n";
}
