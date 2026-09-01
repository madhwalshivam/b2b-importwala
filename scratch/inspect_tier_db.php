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

echo "=== TABLES IN DATABASE ===\n";
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);

echo "\n=== STRUCTURE OF product_price_tiers OR SIMILAR ===\n";
if (in_array('product_price_tiers', $tables)) {
    $cols = $db->query("DESCRIBE product_price_tiers")->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
    
    $tiers = $db->query("SELECT * FROM product_price_tiers LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSample Tiers Data:\n";
    print_r($tiers);
} else if (in_array('product_tiered_prices', $tables)) {
    $cols = $db->query("DESCRIBE product_tiered_prices")->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
} else {
    echo "No tiered price table found.\n";
}

echo "\n=== PRODUCT 5 DETAILS ===\n";
$prod = $db->query("SELECT * FROM products WHERE id = 5")->fetch(PDO::FETCH_ASSOC);
print_r($prod);

echo "\n=== PRODUCT 5 VARIANTS ===\n";
if (in_array('product_variants', $tables)) {
    $vars = $db->query("SELECT * FROM product_variants WHERE product_id = 5")->fetchAll(PDO::FETCH_ASSOC);
    print_r($vars);
}
