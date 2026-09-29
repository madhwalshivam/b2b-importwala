<?php
/**
 * fix_gallery_images.php
 * Finds all product_images rows where image_url is NOT an r2.dev URL,
 * downloads and uploads them to R2, and updates the DB record.
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Services/CloudflareR2.php';
require_once __DIR__ . '/app/Services/ImageMirrorService.php';

if (!class_exists('Auth')) {
    class Auth {
        public static function check() { return true; }
        public static function user() { return ['id' => 1]; }
    }
}

$db  = App\Core\Database::getInstance();
$svc = new App\Services\ImageMirrorService();

// Find all gallery images that are NOT already in R2
$stmt = $db->query("
    SELECT pi.id, pi.product_id, pi.image_url, p.sku
    FROM product_images pi
    JOIN products p ON p.id = pi.product_id
    WHERE pi.image_url NOT LIKE '%r2.dev%'
      AND pi.image_url NOT LIKE '/uploads/%'
      AND pi.image_url LIKE 'http%'
    ORDER BY pi.product_id
");
$rows = $stmt->fetchAll();

echo count($rows) . " gallery images need mirroring\n\n";

$fixed = 0; $failed = 0;
foreach ($rows as $img) {
    $result = $svc->mirrorImage($img['image_url'], $img['sku'], 'gallery');
    if ($result['success']) {
        $db->prepare("UPDATE product_images SET image_url = ? WHERE id = ?")->execute([$result['url'], $img['id']]);
        echo "  ✅ #{$img['id']} ({$img['sku']}): " . basename(parse_url($result['url'], PHP_URL_PATH)) . "\n";
        $fixed++;
    } else {
        echo "  ❌ #{$img['id']} ({$img['sku']}): {$result['error']}\n";
        $failed++;
    }
}

// Also fix main_image in products that is still a raw URL
$prodStmt = $db->query("
    SELECT id, sku, main_image, main_image_source_url 
    FROM products 
    WHERE main_image NOT LIKE '%r2.dev%'
      AND main_image NOT LIKE '/uploads/%'
      AND main_image NOT LIKE 'assets/%'
      AND main_image LIKE 'http%'
");
$prodRows = $prodStmt->fetchAll();
echo "\n" . count($prodRows) . " product main_images need mirroring\n\n";
foreach ($prodRows as $p) {
    $src = !empty($p['main_image_source_url']) ? $p['main_image_source_url'] : $p['main_image'];
    $result = $svc->mirrorImage($src, $p['sku'], 'main');
    if ($result['success']) {
        $db->prepare("UPDATE products SET main_image = ? WHERE id = ?")->execute([$result['url'], $p['id']]);
        $db->prepare("UPDATE product_images SET image_url = ? WHERE product_id = ? AND is_primary = 1")->execute([$result['url'], $p['id']]);
        echo "  ✅ product #{$p['id']} ({$p['sku']}): " . basename(parse_url($result['url'], PHP_URL_PATH)) . "\n";
        $fixed++;
    } else {
        echo "  ❌ product #{$p['id']} ({$p['sku']}): {$result['error']}\n";
        $failed++;
    }
}

echo "\n============================\n";
echo "✅ Fixed:  $fixed\n";
echo "❌ Failed: $failed\n";
