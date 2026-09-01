<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "=== PRODUCT 5 DETAILS ===\n";
$stmt = $db->prepare("SELECT * FROM products WHERE id = 5");
$stmt->execute();
$prod = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($prod);

echo "\n=== PRODUCT 5 VARIANTS ===\n";
$stmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = 5");
$stmt->execute();
$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($variants);

echo "\n=== PRODUCT 5 SPECIFICATIONS ===\n";
$stmt = $db->prepare("SELECT * FROM product_specifications WHERE product_id = 5");
$stmt->execute();
$specs = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($specs);
