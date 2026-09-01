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

// 1. Add variant_id column to tiered_prices if missing
$cols = $db->query("SHOW COLUMNS FROM tiered_prices LIKE 'variant_id'")->fetchAll();
if (empty($cols)) {
    $db->exec("ALTER TABLE `tiered_prices` ADD COLUMN `variant_id` INT(10) UNSIGNED NULL AFTER `product_id`");
    echo "Added variant_id column to tiered_prices table.\n";
} else {
    echo "variant_id column already exists in tiered_prices.\n";
}

// 2. Insert test tiers for Product ID 5
$productId = 5;
$db->exec("DELETE FROM `tiered_prices` WHERE `product_id` = {$productId}");

$tiers = [
    // Product-level tiers (variant_id = NULL)
    ['product_id' => 5, 'variant_id' => null, 'min_qty' => 2,  'max_qty' => 9,    'unit_price' => 899.00],
    ['product_id' => 5, 'variant_id' => null, 'min_qty' => 10, 'max_qty' => 49,   'unit_price' => 849.00],
    ['product_id' => 5, 'variant_id' => null, 'min_qty' => 50, 'max_qty' => null, 'unit_price' => 799.00],

    // Variant-specific tiers for Crystal Clear Matte (Variant ID 8)
    ['product_id' => 5, 'variant_id' => 8,    'min_qty' => 2,  'max_qty' => 9,    'unit_price' => 849.00],
    ['product_id' => 5, 'variant_id' => 8,    'min_qty' => 10, 'max_qty' => 49,   'unit_price' => 799.00],
    ['product_id' => 5, 'variant_id' => 8,    'min_qty' => 50, 'max_qty' => null, 'unit_price' => 749.00],
];

$stmt = $db->prepare("INSERT INTO `tiered_prices` (`product_id`, `variant_id`, `min_qty`, `max_qty`, `unit_price`) VALUES (:product_id, :variant_id, :min_qty, :max_qty, :unit_price)");

foreach ($tiers as $t) {
    $stmt->execute($t);
}

echo "Seeded " . count($tiers) . " tiered prices for Product 5.\n";

try {
    \App\Infrastructure\Cache\CacheManager::getInstance()->flush();
    echo "Cache flushed.\n";
} catch (\Throwable $e) {}
