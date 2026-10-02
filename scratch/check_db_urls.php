<?php
require_once __DIR__ . '/../app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(dirname(__DIR__));
require_once dirname(__DIR__) . '/vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=importwala;charset=utf8', 'root', '');

echo "=== products.main_image (last 5) ===" . PHP_EOL;
$rows = $pdo->query('SELECT id, sku, LEFT(main_image,90) as img FROM products ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $type = str_starts_with($r['img'], 'https://pub-') ? '[R2]' : (str_starts_with($r['img'], 'http') ? '[EXT]' : '[KEY]');
    echo "ID:{$r['id']} SKU:{$r['sku']} $type | {$r['img']}" . PHP_EOL;
}

echo PHP_EOL . "=== product_images.image_url (last 5) ===" . PHP_EOL;
$rows = $pdo->query('SELECT id, product_id, LEFT(image_url,90) as url FROM product_images ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $type = str_starts_with($r['url'], 'https://pub-') ? '[R2]' : (str_starts_with($r['url'], 'http') ? '[EXT]' : '[KEY]');
    echo "ID:{$r['id']} PID:{$r['product_id']} $type | {$r['url']}" . PHP_EOL;
}

echo PHP_EOL . "=== image_mirror_queue stats ===" . PHP_EOL;
$rows = $pdo->query('SELECT status, COUNT(*) as cnt FROM image_mirror_queue GROUP BY status')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "{$r['status']}: {$r['cnt']}" . PHP_EOL;
}

echo PHP_EOL . "=== Any EXT URLs still in products? ===" . PHP_EOL;
$cnt = $pdo->query("SELECT COUNT(*) FROM products WHERE main_image LIKE 'http%' AND main_image NOT LIKE 'https://pub-%'")->fetchColumn();
echo "External (non-R2) main_image count: $cnt" . PHP_EOL;

$cnt2 = $pdo->query("SELECT COUNT(*) FROM product_images WHERE image_url LIKE 'http%' AND image_url NOT LIKE 'https://pub-%'")->fetchColumn();
echo "External (non-R2) product_images.image_url count: $cnt2" . PHP_EOL;
