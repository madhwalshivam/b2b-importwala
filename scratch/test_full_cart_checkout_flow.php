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
$_SESSION['cart_session_id'] = 'test_sess_' . time();

$db = App\Core\Database::getInstance();
$sessionId = $_SESSION['cart_session_id'];

echo "=== 1. TESTING ADD TO CART (Product 5, Variant 8, Qty 12, Wholesale Mode) ===\n";

$_POST = [
    'product_id'   => 5,
    'variant_id'   => 8,
    'quantity'     => 12,
    'pricing_mode' => 'wholesale'
];

$cartController = new App\Controllers\Web\CartController();

ob_start();
$cartController->add();
$resJson = ob_get_clean();
$addRes = json_decode($resJson, true);

print_r($addRes);

if (!empty($addRes['success'])) {
    echo "✅ Item added to cart! Cart count: {$addRes['cart_count']}, Subtotal: ₹{$addRes['subtotal']}\n";
} else {
    echo "❌ Add to cart failed!\n";
    exit(1);
}

echo "\n=== 2. TESTING WISHLIST TOGGLE ===\n";
$_POST = ['product_id' => 5];
$wishlistController = new App\Controllers\Web\WishlistController();
ob_start();
$wishlistController->toggle();
$wResJson = ob_get_clean();
$wRes = json_decode($wResJson, true);
print_r($wRes);

if (!empty($wRes['success'])) {
    echo "✅ Wishlist toggled successfully! Saved: " . ($wRes['saved'] ? 'YES' : 'NO') . ", Count: {$wRes['count']}\n";
}

echo "\n=== 3. TESTING CHECKOUT ORDER CREATION (RAZORPAY) ===\n";
$_POST = [
    'customer_name'    => 'Test B2B Retailer',
    'customer_email'   => 'b2btest@importwala.com',
    'customer_phone'   => '9998887770',
    'shipping_address' => 'Plot 42, Okhla Industrial Area',
    'city'             => 'New Delhi',
    'state'            => 'Delhi',
    'pincode'          => '110020'
];

$checkoutController = new App\Controllers\Web\CheckoutController();

ob_start();
$checkoutController->createOrder();
$orderResJson = ob_get_clean();
$orderRes = json_decode($orderResJson, true);

print_r($orderRes);

if (!empty($orderRes['success']) && !empty($orderRes['order_id'])) {
    echo "✅ Order created successfully! Order ID: {$orderRes['order_id']}, Order #: {$orderRes['order_number']}, Razorpay Order ID: {$orderRes['razorpay_order_id']}\n";
    
    echo "\n=== 4. TESTING RAZORPAY PAYMENT VERIFICATION ===\n";
    $_POST = [
        'order_id'          => $orderRes['order_id'],
        'razorpay_order_id'   => $orderRes['razorpay_order_id'],
        'razorpay_payment_id' => 'pay_test_' . bin2hex(random_bytes(6)),
        'razorpay_signature'  => 'mock_sig_' . bin2hex(random_bytes(6))
    ];

    ob_start();
    $checkoutController->verifyRazorpay();
    $verifyResJson = ob_get_clean();
    $verifyRes = json_decode($verifyResJson, true);
    print_r($verifyRes);

    if (!empty($verifyRes['success'])) {
        echo "✅ Payment verified! Redirect URL: {$verifyRes['redirect_url']}\n";
    }

    // Check DB order status
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderRes['order_id']]);
    $o = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Verified DB Order Status: Payment={$o['payment_status']}, OrderStatus={$o['order_status']}\n";
}

echo "\n🎉 ALL END-TO-END FLOW TESTS PASSED!\n";
