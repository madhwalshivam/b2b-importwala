<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Services/FilterAttributeService.php';

use App\Core\Database;
use App\Services\FilterAttributeService;

$db = Database::getInstance();
$filterService = new FilterAttributeService();

echo "=== SYNCING SPECIFICATIONS TO FILTER ATTRIBUTES ===\n";

// Fetch all filter attributes indexed by slug and name
$attrStmt = $db->query("SELECT * FROM filter_attributes WHERE is_active = 1");
$existingAttrs = $attrStmt->fetchAll(PDO::FETCH_ASSOC);

$attrMap = [];
foreach ($existingAttrs as $a) {
    $attrMap[strtolower(trim($a['name']))] = (int)$a['id'];
    $attrMap[strtolower(trim($a['slug']))] = (int)$a['id'];
}

// Special alias mappings for spec_key -> filter_attribute
$aliases = [
    'brand' => 'brand_name',
    'kind / product type' => 'kind',
    'material' => 'material_metal_type',
    'metal' => 'material_metal_type',
    'material/metal type' => 'material_metal_type',
    'style' => 'style',
    'style classification' => 'style_classification',
    'trendy element' => 'trendy_element',
    'popular elements' => 'popular_elements',
];

$specsStmt = $db->query("SELECT * FROM product_specifications");
$allSpecs = $specsStmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($allSpecs) . " specifications.\n";

$insertedValues = 0;
$createdOptions = 0;

$delPfav = $db->prepare("DELETE FROM product_filter_attribute_values");
$delPfav->execute();

$insPfav = $db->prepare("
    INSERT IGNORE INTO product_filter_attribute_values (product_id, attribute_id, option_id, value)
    VALUES (?, ?, ?, ?)
");

foreach ($allSpecs as $spec) {
    $productId = (int)$spec['product_id'];
    $rawKey = trim($spec['spec_key']);
    $rawValue = trim($spec['spec_value']);

    if (empty($rawKey) || empty($rawValue)) continue;

    $keyLower = strtolower($rawKey);
    $mappedKey = $aliases[$keyLower] ?? $keyLower;

    $attrId = $attrMap[$mappedKey] ?? null;

    // If not found in map, check by slugify
    if (!$attrId) {
        $slugKey = preg_replace('/[^a-z0-9]+/', '_', $keyLower);
        $attrId = $attrMap[$slugKey] ?? null;
    }

    // If still not found and valid name, auto-create filter_attribute
    if (!$attrId && strlen($rawKey) >= 2 && !in_array($keyLower, ['moq (wholesale)', 'package dimensions', 'item net weight', 'weight & profile'])) {
        $slug = preg_replace('/[^a-z0-9]+/', '_', trim(strtolower($rawKey)));
        $insAttr = $db->prepare("INSERT INTO filter_attributes (name, slug, type, is_global, is_admin_only, is_active, sort_order) VALUES (?, ?, 'multi_select', 1, 0, 1, 50)");
        $insAttr->execute([$rawKey, $slug]);
        $attrId = (int)$db->lastInsertId();
        $attrMap[$keyLower] = $attrId;
        $attrMap[$slug] = $attrId;
        echo "Auto-created filter attribute: {$rawKey} (ID: {$attrId})\n";
    }

    if (!$attrId) continue;

    // Split multi-values if separated by || or comma (for sizes/colors)
    $vals = [];
    if (strpos($rawValue, '||') !== false) {
        $vals = array_map('trim', explode('||', $rawValue));
    } elseif (in_array($keyLower, ['color', 'metal color', 'size', 'popular elements', 'trendy element']) && strpos($rawValue, ',') !== false) {
        $vals = array_map('trim', explode(',', $rawValue));
    } else {
        $vals = [$rawValue];
    }

    foreach ($vals as $val) {
        if (empty($val)) continue;
        $optId = $filterService->getOrCreateOption($attrId, $val, true);
        if ($optId) {
            $insPfav->execute([$productId, $attrId, $optId, $val]);
            $insertedValues++;
        }
    }
}

echo "Successfully inserted $insertedValues product attribute values!\n";

echo "\n=== FILTER ATTRIBUTE OPTIONS AFTER SYNC ===\n";
$opts = $db->query("SELECT fa.name, fao.id as option_id, fao.value, COUNT(pfav.product_id) as prod_count 
                    FROM filter_attribute_options fao 
                    JOIN filter_attributes fa ON fao.attribute_id = fa.id 
                    LEFT JOIN product_filter_attribute_values pfav ON fao.id = pfav.option_id 
                    GROUP BY fao.id ORDER BY fa.name, fao.value")->fetchAll(PDO::FETCH_ASSOC);

foreach ($opts as $o) {
    echo "Attribute: {$o['name']} | Option: {$o['value']} (ID: {$o['option_id']}) -> Assigned Products: {$o['prod_count']}\n";
}
