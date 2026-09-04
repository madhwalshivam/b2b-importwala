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

// Insert a genuine matching bracelet product (e.g. Leather Rope Braided Wristband for Men)
$stmt = $db->prepare("SELECT id FROM products WHERE name LIKE '%Men Leather Wristband%'");
$stmt->execute();
$existing = $stmt->fetchColumn();

if (!$existing) {
    $db->prepare("
        INSERT INTO products (name, slug, category_id, price, sale_price, moq, status, main_image, is_new, created_at)
        VALUES (
            'Men Leather Rope Braided Wristband with Stainless Steel Clasp',
            'men-leather-rope-braided-wristband',
            4,
            3.50,
            2.99,
            1,
            'active',
            'uploads/products/url_1788414413_a6a05a173673afa5214b8fea0e87d7ee.jpg',
            1,
            NOW()
        )
    ")->execute();
    $newId = $db->lastInsertId();
    echo "Created new product #{$newId}\n";
} else {
    $newId = $existing;
    echo "Existing product #{$newId}\n";
}

$service = new VisualSearchService();
$service->indexProduct((int)$newId);

$res = $service->searchByProductId(19, 8);
print_r($res);
