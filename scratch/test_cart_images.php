<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/ecommerce/cart';
$_SERVER['REQUEST_METHOD'] = 'GET';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

require_once __DIR__ . '/../app/Helpers/Functions.php';

$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;

session_start();

// Add product #6 to session cart to simulate cart item
$db = App\Core\Database::getInstance();
$stmt = $db->prepare("SELECT * FROM products WHERE id = 6");
$stmt->execute();
$product = $stmt->fetch();

$_SESSION['cart'][6] = [
    'id' => $product['id'],
    'category_id' => $product['category_id'] ?? null,
    'name' => $product['name'],
    'sku' => $product['sku'],
    'price' => (float)($product['sale_price'] ?: $product['price']),
    'tax_percent' => (float)$product['tax_percent'],
    'image' => asset($product['main_image']),
    'quantity' => 1,
    'slug' => $product['slug']
];

echo "=== CART ITEM IMAGE URL ===\n";
echo "Cart item image: " . $_SESSION['cart'][6]['image'] . "\n\n";

echo "=== TESTING CART CONTROLLER RENDER ===\n";
$cartController = new App\Controllers\CartController();
ob_start();
$htmlCart = $cartController->index();
$lenCart = strlen($htmlCart);
ob_end_clean();

echo "Cart page rendered successfully ({$lenCart} bytes).\n";
echo "Image present in rendered cart page HTML: " . (str_contains($htmlCart, $_SESSION['cart'][6]['image']) ? "YES" : "NO") . "\n";
