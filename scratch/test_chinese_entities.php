<?php
require __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';
\App\Core\Database::init($config);
$db = \App\Core\Database::getInstance();

echo "=== CHECKING PRODUCTS WITH SPECIAL / CHINESE / UNICODE / ENTITY SYMBOLS ===\n";

$stmt = $db->query("SELECT id, name, title, sku FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    $title = $p['name'] ?? $p['title'] ?? '';
    if (preg_match('/[\x{4e00}-\x{9fff}]/u', $title) || str_contains($title, '&amp;') || str_contains($title, '&#') || str_contains($title, '&quot;') || str_contains($title, '?')) {
        echo "PRODUCT ID {$p['id']} [SKU: {$p['sku']}]: {$title}\n";
    }
}

echo "\n=== CHECKING PRODUCT VARIANTS / COLOR SIZES WITH SPECIAL / CHINESE SYMBOLS ===\n";
$stmt2 = $db->query("SELECT * FROM product_color_sizes LIMIT 1000");
$variants = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($variants as $v) {
    $c = $v['color_name'] ?? '';
    $s = $v['size_label'] ?? '';
    if (preg_match('/[\x{4e00}-\x{9fff}]/u', $c . $s) || str_contains($c . $s, '&amp;') || str_contains($c . $s, '&#')) {
        echo "VARIANT ID {$v['id']}: Color='{$c}', Size='{$s}'\n";
    }
}
