<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Database.php';
$db = App\Core\Database::getInstance();

$rows = $db->query('SELECT id, color_name, LEFT(swatch_hex_or_image,70) as sw FROM product_colors WHERE product_id = 890')->fetchAll(PDO::FETCH_ASSOC);
echo "Product 890 colors:" . PHP_EOL;
foreach($rows as $x) echo "  {$x['id']} {$x['color_name']} => {$x['sw']}" . PHP_EOL;

$c = $db->query("SELECT COUNT(*) FROM product_colors WHERE swatch_hex_or_image LIKE '%bulkflowai%'")->fetchColumn();
echo PHP_EOL . "Remaining bulkflowai in product_colors: {$c}" . PHP_EOL;

$c2 = $db->query("SELECT COUNT(*) FROM product_images WHERE image_url LIKE '%bulkflowai%'")->fetchColumn();
echo "Remaining bulkflowai in product_images: {$c2}" . PHP_EOL;

$c3 = $db->query("SELECT COUNT(*) FROM products WHERE main_image LIKE '%bulkflowai%'")->fetchColumn();
echo "Remaining bulkflowai in products.main_image: {$c3}" . PHP_EOL;
