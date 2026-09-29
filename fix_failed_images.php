<?php
/**
 * fix_failed_images.php
 * Re-mirrors product_images that still have raw HTTP source URLs (not yet on R2).
 * Also handles the 2 failed images from the last import.
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

// Find ALL product_images still with raw http source URLs (not R2, not local)
$stmt = $db->query("
    SELECT pi.id, pi.product_id, pi.image_url, p.sku
    FROM product_images pi
    JOIN products p ON p.id = pi.product_id
    WHERE pi.image_url NOT LIKE '%r2.dev%'
      AND pi.image_url NOT LIKE '/uploads/%'
      AND pi.image_url NOT LIKE 'assets/%'
      AND pi.image_url LIKE 'http%'
    ORDER BY pi.product_id
");
$rows = $stmt->fetchAll();

echo count($rows) . " gallery images still need mirroring\n\n";

if (empty($rows)) {
    echo "All gallery images are already on R2!\n";
    exit;
}

$fixed = 0; $failed = 0;
foreach ($rows as $img) {
    // Clear stale mirror map entry if it exists
    $hash = hash('sha256', $img['image_url']);
    $db->prepare("DELETE FROM image_mirror_map WHERE source_url_hash = ?")->execute([$hash]);

    $result = $svc->mirrorImage($img['image_url'], $img['sku'], 'gallery');
    if ($result['success']) {
        $db->prepare("UPDATE product_images SET image_url = ? WHERE id = ?")->execute([$result['url'], $img['id']]);
        echo "  ✅ #{$img['id']} ({$img['sku']}): " . basename(parse_url($result['url'], PHP_URL_PATH)) . "\n";
        $fixed++;
    } else {
        echo "  ❌ #{$img['id']} ({$img['sku']}): {$result['error']} — URL: " . substr($img['image_url'], 0, 60) . "\n";
        $failed++;
    }
}

echo "\n============================\n";
echo "✅ Fixed:  $fixed\n";
echo "❌ Failed: $failed\n";
