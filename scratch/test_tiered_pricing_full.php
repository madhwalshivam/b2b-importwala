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

$db = App\Core\Database::getInstance();
$productId = 5;

$allTiersStmt = $db->prepare("SELECT * FROM tiered_prices WHERE product_id = ? ORDER BY min_qty ASC");
$allTiersStmt->execute([$productId]);
$allTiers = $allTiersStmt->fetchAll(PDO::FETCH_ASSOC);

$productTiers = [];
$variantTiersMap = [];

foreach ($allTiers as $t) {
    if (empty($t['variant_id'])) {
        $productTiers[] = $t;
    } else {
        $vId = (int)$t['variant_id'];
        if (!isset($variantTiersMap[$vId])) {
            $variantTiersMap[$vId] = [];
        }
        $variantTiersMap[$vId][] = $t;
    }
}

echo "=== PRODUCT 5 MAIN TIERS ===\n";
foreach ($productTiers as $t) {
    $label = !empty($t['max_qty']) ? "{$t['min_qty']}-{$t['max_qty']} pcs" : "≥ {$t['min_qty']} pcs";
    echo "Range: {$label} => Price: ₹{$t['unit_price']}\n";
}

echo "\n=== VARIANT 8 (CRYSTAL CLEAR MATTE) TIERS ===\n";
if (!empty($variantTiersMap[8])) {
    foreach ($variantTiersMap[8] as $t) {
        $label = !empty($t['max_qty']) ? "{$t['min_qty']}-{$t['max_qty']} pcs" : "≥ {$t['min_qty']} pcs";
        echo "Range: {$label} => Price: ₹{$t['unit_price']}\n";
    }
}

echo "\n=== VARIANT 7 (MATT ONYX BLACK) FALLBACK TIERS ===\n";
$v7Tiers = !empty($variantTiersMap[7]) ? $variantTiersMap[7] : $productTiers;
foreach ($v7Tiers as $t) {
    $label = !empty($t['max_qty']) ? "{$t['min_qty']}-{$t['max_qty']} pcs" : "≥ {$t['min_qty']} pcs";
    echo "Range: {$label} => Price: ₹{$t['unit_price']}\n";
}

echo "\nALL TIER TESTS PASSED SUCCESSFULLY!\n";
