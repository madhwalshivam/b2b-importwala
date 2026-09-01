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

echo "=== CART_ITEMS STRUCTURE ===\n";
try {
    $cols = $db->query("DESCRIBE cart_items")->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
} catch (Exception $e) {
    echo "cart_items error: " . $e->getMessage() . "\n";
}

echo "\n=== ORDERS STRUCTURE ===\n";
try {
    $cols = $db->query("DESCRIBE orders")->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
} catch (Exception $e) {
    echo "orders error: " . $e->getMessage() . "\n";
}

echo "\n=== ORDER_ITEMS STRUCTURE ===\n";
try {
    $cols = $db->query("DESCRIBE order_items")->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
} catch (Exception $e) {
    echo "order_items error: " . $e->getMessage() . "\n";
}

echo "\n=== WISHLIST STRUCTURE ===\n";
try {
    $cols = $db->query("DESCRIBE wishlist")->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
} catch (Exception $e) {
    echo "wishlist error: " . $e->getMessage() . "\n";
}

echo "\n=== PAYMENT GATEWAY SETTINGS ===\n";
try {
    $rows = $db->query("SELECT * FROM payment_gateway_settings")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo "payment_gateway_settings error: " . $e->getMessage() . "\n";
}
