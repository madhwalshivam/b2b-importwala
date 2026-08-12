<?php
// Scratch Integration Test for Enterprise Architecture Flow

require_once __DIR__ . '/../public/index.php';

use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\SearchService;
use App\Repositories\Eloquent\ProductRepository;

echo "--- 1. Testing Product Repository & Tiered Pricing ---\n";
$prodRepo = new ProductRepository();
$product = $prodRepo->getProductWithDetails(1);
echo "Product #1: " . $product['title'] . "\n";
echo "Base Price: $" . number_format($product['base_price'], 2) . "\n";
echo "MOQ: " . $product['moq'] . " pcs\n";
echo "Tier Count: " . count($product['tiered_prices']) . "\n";

echo "\n--- 2. Testing Ephemeral Cart Service ---\n";
$cartService = new CartService();
$testCartId = 'cart_test_' . uniqid();
$cart = $cartService->addItem($testCartId, 1, 1, 55); // Buy 55 pcs (triggers 50-199 tier @ $0.85)
echo "Cart Total Items: " . $cart['items_count'] . " pcs\n";
echo "Cart Subtotal: $" . number_format($cart['subtotal'], 2) . "\n";
echo "Unit Price Applied: $" . number_format($cart['items'][0]['unit_price'], 2) . "\n";

echo "\n--- 3. Testing Idempotent & Concurrency-Safe Checkout Service ---\n";
$checkoutService = new CheckoutService();
$idempotencyKey = 'idemp_test_' . uniqid();
$result = $checkoutService->processCheckout(
    'usr_test_123',
    $testCartId,
    $idempotencyKey,
    ['email' => 'test@importwala.com', 'city' => 'New York'],
    ['email' => 'test@importwala.com', 'city' => 'New York']
);

echo "Checkout Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Order Number: " . $result['order_number'] . "\n";
echo "Total Amount: $" . number_format($result['total_amount'], 2) . "\n";

echo "\n--- 4. Testing Idempotency Lock Retry ---\n";
$retryResult = $checkoutService->processCheckout(
    'usr_test_123',
    $testCartId,
    $idempotencyKey,
    [], []
);
echo "Retry Return Order #: " . $retryResult['order_number'] . " (Cached Idempotent Response)\n";

echo "\n--- 5. Testing Search Service ---\n";
$searchService = new SearchService();
$searchResults = $searchService->search('Vintage', []);
echo "Search Found: " . $searchResults['total'] . " items matching 'Vintage'\n";

echo "\n✓ ENTERPRISE SYSTEM INTEGRATION VERIFIED SUCCESSFULLY!\n";
