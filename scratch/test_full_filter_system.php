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
    if (strncmp('Lib\\', $class, 4) === 0) {
        $relPath = str_replace('\\', '/', substr($class, 4));
        $file = ROOT_PATH . '/lib/' . $relPath . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

require_once ROOT_PATH . '/app/Helpers/Functions.php';
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

echo "=== TESTING FILTER ATTRIBUTE SERVICE ===\n";
$filterService = new \App\Services\FilterAttributeService();

$attrs = $filterService->getAttributesForCategory(null);
echo "Fetched " . count($attrs) . " active global attributes:\n";
foreach ($attrs as $a) {
    echo " - Attribute [ID {$a['id']}]: {$a['name']} ({$a['type']}) -> " . count($a['options']) . " options\n";
}

// Assign sample attribute values to Product #1
$db = \App\Core\Database::getInstance();
$firstProdId = $db->query("SELECT id FROM products LIMIT 1")->fetchColumn();

if ($firstProdId) {
    echo "\nAssigning sample 'Material: Stainless Steel' to Product ID {$firstProdId}...\n";
    $matAttr = $attrs[0] ?? null;
    if ($matAttr) {
        $optId = $matAttr['options'][0]['id'] ?? null;
        if ($optId) {
            $filterService->saveProductAttributeValues((int)$firstProdId, [
                $matAttr['id'] => [$optId]
            ]);
            echo "SUCCESS: Saved option_id {$optId} for product {$firstProdId}!\n";
        }
    }
}

echo "\n=== TESTING SEARCH SERVICE WITH COMBINED FILTERS ===\n";
$searchService = new \App\Services\SearchService();
$resCombined = $searchService->search('', [
    'attr' => [
        1 => [1, 2]
    ]
], 24, 0);

echo "Search result count with Material attribute filter: " . ($resCombined['total'] ?? 0) . "\n";
echo "Option counts facets:\n";
print_r($resCombined['facets']['option_counts'] ?? []);

echo "\n=== TESTING STOREFRONT SHOP PAGE RENDER ===\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/shop?attr[1]=1';
$_SERVER['HTTP_HOST'] = 'localhost';
$_GET['attr'] = [1 => [1]];

$controller = new \App\Controllers\Web\CatalogController();
ob_start();
try {
    $controller->index();
    $html = ob_get_clean();
    echo "Rendered HTML length: " . strlen($html) . " bytes\n";
    $hasChips = strpos($html, 'shop-chip') !== false;
    $hasAccordions = strpos($html, 'shop-accordion-header') !== false;
    $hasOptionCounts = strpos($html, 'shop-scroll-area') !== false;
    echo "  - Filter Chips Rendered: " . ($hasChips ? 'YES' : 'NO') . "\n";
    echo "  - Accordions Rendered: " . ($hasAccordions ? 'YES' : 'NO') . "\n";
    echo "  - Live Option Counts Rendered: " . ($hasOptionCounts ? 'YES' : 'NO') . "\n";
} catch (\Throwable $e) {
    ob_end_clean();
    echo "ERROR during render: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
