<?php
define('ROOT_PATH', dirname(__DIR__));
spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

use App\Core\Database;
use App\Services\VisualSearchService;

$db = Database::getInstance();

// 1. Fix Product Categories
$catUpdates = [
    1  => 2,  // Necklace -> Stainless Steel Necklace
    2  => 5,  // Ring -> Statement Rings
    3  => 3,  // Earrings -> Hoop & Stud Earrings
    4  => 10, // Sunglasses -> Sunglasses & Eyewear
    5  => 47, // Phone case -> Phone Cases & Protectors
    6  => 52, // Watch -> Luxury Quartz Watches
    16 => 2,  // Necklace -> Stainless Steel Necklace
    17 => 3,  // Earrings -> Hoop & Stud Earrings
    19 => 4,  // Bracelet -> Beaded & Charm Bracelets
];

foreach ($catUpdates as $pid => $catId) {
    $db->prepare("UPDATE products SET category_id = ? WHERE id = ?")->execute([$catId, $pid]);
}

// 2. Fix Product 17 main_image to single image
$db->prepare("UPDATE products SET main_image = 'uploads/products/url_1788414413_a6a05a173673afa5214b8fea0e87d7ee.jpg' WHERE id = 17")->execute();
$db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = 17")->execute();
$db->prepare("UPDATE product_images SET is_primary = 1 WHERE product_id = 17 AND image_url LIKE '%url_1788414413%'")->execute();

echo "Product categories and Product 17 main_image updated.\n";

// 3. Re-index embeddings
$vService = new VisualSearchService();
$res = $vService->indexAllProducts(true);
print_r($res);
