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

// 1. Add variant_id to cart_items if missing
$cols = $db->query("SHOW COLUMNS FROM cart_items LIKE 'variant_id'")->fetchAll();
if (empty($cols)) {
    $db->exec("ALTER TABLE `cart_items` ADD COLUMN `variant_id` INT(10) UNSIGNED NULL AFTER `product_id`");
    echo "Added variant_id to cart_items.\n";
}

// 2. Add pricing_mode to cart_items if missing
$cols = $db->query("SHOW COLUMNS FROM cart_items LIKE 'pricing_mode'")->fetchAll();
if (empty($cols)) {
    $db->exec("ALTER TABLE `cart_items` ADD COLUMN `pricing_mode` ENUM('wholesale', 'onepiece') DEFAULT 'wholesale' AFTER `variant_id`");
    echo "Added pricing_mode to cart_items.\n";
}

// 3. Add unit_price to cart_items if missing
$cols = $db->query("SHOW COLUMNS FROM cart_items LIKE 'unit_price'")->fetchAll();
if (empty($cols)) {
    $db->exec("ALTER TABLE `cart_items` ADD COLUMN `unit_price` DECIMAL(10,2) NULL AFTER `quantity`");
    echo "Added unit_price to cart_items.\n";
}

echo "Database Schema Migration Complete.\n";
