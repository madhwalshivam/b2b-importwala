<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Database.php';
$db = App\Core\Database::getInstance();

// Product 890 ke variant colors check karo
$stmt = $db->query("SELECT id, color_name, swatch_hex_or_image FROM product_colors WHERE product_id = 890");
$colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Product 890 colors: " . count($colors) . PHP_EOL;
foreach ($colors as $c) {
    echo "  id={$c['id']} name={$c['color_name']} swatch=" . substr($c['swatch_hex_or_image'] ?? 'NULL', 0, 80) . PHP_EOL;
}

// Global final counts
echo PHP_EOL . "=== GLOBAL COUNTS ===" . PHP_EOL;
$c1 = $db->query("SELECT COUNT(*) FROM product_images WHERE image_url LIKE '%bulkflowai.com%'")->fetchColumn();
$c2 = $db->query("SELECT COUNT(*) FROM product_colors WHERE swatch_hex_or_image LIKE '%bulkflowai.com%'")->fetchColumn();
$c3 = $db->query("SELECT COUNT(*) FROM products WHERE main_image LIKE '%bulkflowai.com%'")->fetchColumn();
echo "product_images with bulkflowai: {$c1}" . PHP_EOL;
echo "product_colors with bulkflowai: {$c2}" . PHP_EOL;
echo "products.main_image bulkflowai: {$c3}" . PHP_EOL;
