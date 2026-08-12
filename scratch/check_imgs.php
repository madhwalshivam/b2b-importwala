<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/Functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query('SELECT id, name, slug, main_image, status FROM products ORDER BY id DESC');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total products: " . count($products) . "\n\n";
foreach ($products as $p) {
    echo "ID: {$p['id']} | Status: {$p['status']} | MainImg: {$p['main_image']} | Name: {$p['name']}\n";
    $imgStmt = $db->prepare('SELECT id, image_url, image_path, is_primary FROM product_images WHERE product_id = ?');
    $imgStmt->execute([$p['id']]);
    $imgs = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "  Product_images count: " . count($imgs) . "\n";
    foreach ($imgs as $img) {
        $u = $img['image_url'] ?: $img['image_path'];
        $parsed = asset($u);
        echo "   - [id:{$img['id']}] primary:{$img['is_primary']} | raw: {$u} | asset(): {$parsed}\n";
    }
    echo "---------------------------------------------------------\n";
}
