<?php
define('ROOT_PATH', __DIR__ . '/..');
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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['cart_session_id'] = 'test_session_sync_' . time();

$cartController = new App\Controllers\Web\CartController();

echo "=== STEP 1: Add 5 units of Product 5 (Variant 8) ===\n";
$_POST = [
    'product_id' => 5,
    'variant_id' => 8,
    'quantity'   => 5,
    'set_exact_qty' => 1,
    'pricing_mode' => 'wholesale'
];
ob_start();
$cartController->add();
$res1 = json_decode(ob_get_clean(), true);
print_r($res1);

echo "=== STEP 2: Decrease Qty to 0 (Set Exact Qty 0) ===\n";
$_POST = [
    'product_id' => 5,
    'variant_id' => 8,
    'quantity'   => 0,
    'set_exact_qty' => 1,
    'pricing_mode' => 'wholesale'
];
ob_start();
$cartController->add();
$res2 = json_decode(ob_get_clean(), true);
print_r($res2);

if ($res2['cart_count'] === 0 && $res2['subtotal'] === "0.00") {
    echo "✅ SUCCESS! Setting Qty 0 successfully removed item and reduced Order Summary count/amount to 0!\n";
} else {
    echo "❌ FAILED!\n";
}
