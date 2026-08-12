<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Helpers/Functions.php';
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/ProductImage.php';

$productModel = new App\Models\Product();
$imageModel = new App\Models\ProductImage();

echo "=== CHECKING PRODUCT FOR SLUG: mudsor-ev-charger-wall-mount-dock ===\n";
$p = $productModel->findBy('slug', 'mudsor-ev-charger-wall-mount-dock');
if ($p) {
    print_r($p);
    $images = $imageModel->getByProduct($p['id']);
    echo "=== GALLERY IMAGES ===\n";
    print_r($images);
} else {
    echo "PRODUCT NOT FOUND WITH EXACT SLUG 'mudsor-ev-charger-wall-mount-dock'!\n";
    echo "Searching LIKE 'mudsor-ev-charger%':\n";
    $db = App\Core\Database::getInstance();
    $stmt = $db->query("SELECT id, name, slug, main_image FROM products WHERE slug LIKE '%charger%' OR name LIKE '%charger%'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
