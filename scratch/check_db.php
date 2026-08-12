<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});
require_once __DIR__ . '/../app/Helpers/Functions.php';

$db = App\Core\Database::getInstance();
echo "--- PRODUCTS COLUMNS ---\n";
$cols = $db->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_COLUMN);
print_r($cols);

echo "--- PRODUCT SPECIFICATIONS TABLE ---\n";
try {
    $specsCols = $db->query('SHOW COLUMNS FROM product_specifications')->fetchAll(PDO::FETCH_ASSOC);
    print_r($specsCols);
    $specsCount = $db->query('SELECT COUNT(*) FROM product_specifications')->fetchColumn();
    echo "Total rows in product_specifications: $specsCount\n";
    $sampleSpecs = $db->query('SELECT * FROM product_specifications LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
    print_r($sampleSpecs);
} catch (Exception $e) {
    echo "product_specifications error: " . $e->getMessage() . "\n";
}

echo "--- PRODUCT VEHICLE COMPATIBILITY ---\n";
try {
    $comp = $db->query('SELECT * FROM product_vehicle_compatibility LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
    print_r($comp);
} catch (Exception $e) {
    echo "vehicle_compatibility error: " . $e->getMessage() . "\n";
}

echo "--- PRODUCT SCOOTER COMPATIBILITIES (PIVOT) ---\n";
try {
    $scooters = $db->query('SELECT psc.*, sm.name as model_name, b.name as brand_name FROM product_scooter_compatibilities psc JOIN scooter_models sm ON psc.scooter_model_id = sm.id JOIN brands b ON psc.brand_id = b.id LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
    print_r($scooters);
} catch (Exception $e) {
    echo "scooter compatibilities error: " . $e->getMessage() . "\n";
}
