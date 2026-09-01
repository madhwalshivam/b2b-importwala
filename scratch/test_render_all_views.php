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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = App\Core\Database::getInstance();

echo "=== RENDER CART PAGE ===\n";
ob_start();
$cartItems = [
    [
        'id' => 1,
        'product_id' => 5,
        'variant_id' => 8,
        'name' => 'Mudsor Ultra-Slim Case',
        'slug' => 'mudsor-protective-phone-case',
        'sku' => 'MUD-BC-CLR',
        'image' => asset('uploads/products/phone-cases.jpg'),
        'variant_title' => 'Crystal Clear Matte',
        'pricing_mode' => 'wholesale',
        'quantity' => 12,
        'unit_price' => 799.00,
        'item_total' => 9588.00
    ]
];
$cartCount = 12;
$subtotal = 9588.00;
$tax = 1725.84;
$total = 11313.84;

include ROOT_PATH . '/views/web/cart.php';
$cartHtml = ob_get_clean();
echo "Cart Page Rendered: " . strlen($cartHtml) . " bytes\n";

echo "=== RENDER CHECKOUT PAGE ===\n";
ob_start();
$user = null;
$razorpayKeyId = 'rzp_test_5x1Z9312345';
include ROOT_PATH . '/views/web/checkout.php';
$checkoutHtml = ob_get_clean();
echo "Checkout Page Rendered: " . strlen($checkoutHtml) . " bytes\n";

echo "=== RENDER ORDER SUCCESS PAGE ===\n";
ob_start();
$order = [
    'id' => 1,
    'order_number' => 'ORD-TEST1234',
    'total_amount' => 11313.84,
    'customer_name' => 'Test Customer',
    'customer_phone' => '9998887770',
    'shipping_address' => json_encode(['address' => 'Plot 42', 'city' => 'New Delhi', 'state' => 'Delhi', 'pincode' => '110020'])
];
$items = [
    [
        'product_name' => 'Mudsor Ultra-Slim Case (Crystal Clear Matte)',
        'sku' => 'MUD-BC-CLR',
        'quantity' => 12,
        'total_amount' => 9588.00
    ]
];
include ROOT_PATH . '/views/web/order_success.php';
$successHtml = ob_get_clean();
echo "Order Success Page Rendered: " . strlen($successHtml) . " bytes\n";

echo "=== RENDER ADMIN ORDERS INDEX PAGE ===\n";
ob_start();
$orders = [
    [
        'id' => 1,
        'order_number' => 'ORD-TEST1234',
        'customer_name' => 'Test Customer',
        'customer_phone' => '9998887770',
        'total_amount' => 11313.84,
        'payment_status' => 'paid',
        'order_status' => 'confirmed',
        'razorpay_payment_id' => 'pay_test_999',
        'created_at' => date('Y-m-d H:i:s')
    ]
];
$currentStatus = '';
include ROOT_PATH . '/app/Views/admin/orders/index.php';
$adminOrdersHtml = ob_get_clean();
echo "Admin Orders Page Rendered: " . strlen($adminOrdersHtml) . " bytes\n";

echo "ALL VIEW RENDER TESTS PASSED CLEANLY!\n";
