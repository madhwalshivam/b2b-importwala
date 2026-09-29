<?php
/**
 * remirror_all.php
 * Re-mirrors all product images that are broken (404 in R2 or not yet mirrored).
 * Run: php remirror_all.php
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

$db   = App\Core\Database::getInstance();
$svc  = new App\Services\ImageMirrorService();
$r2   = new App\Services\CloudflareR2();
$client = $r2->getClient();
$bucket = $r2->getBucketName();
$publicBase = rtrim($r2->getPublicBaseUrl(), '/');

$fixed  = 0;
$failed = 0;
$skipped = 0;

// ── Helper: check if R2 object exists ─────────────────────────────────────
function r2Exists($client, $bucket, $url, $publicBase) {
    $key = ltrim(parse_url($url, PHP_URL_PATH), '/');
    try {
        $client->headObject(['Bucket' => $bucket, 'Key' => $key]);
        return true;
    } catch (\Aws\Exception\AwsException $e) {
        return false;
    }
}

// ── Helper: update product_images row ─────────────────────────────────────
function updateProductImageUrl($db, $oldUrl, $newUrl) {
    $db->prepare("UPDATE product_images SET image_url = ? WHERE image_url = ?")->execute([$newUrl, $oldUrl]);
}

function updateProductMainImage($db, $productId, $newUrl) {
    $db->prepare("UPDATE products SET main_image = ? WHERE id = ?")->execute([$newUrl, $productId]);
}

function updateColorSwatch($db, $colorId, $newUrl) {
    $db->prepare("UPDATE product_colors SET swatch_hex_or_image = ? WHERE id = ?")->execute([$newUrl, $colorId]);
}

echo "=== ImportWala R2 Remirror ===\n\n";

// ── 1. Fix product_images with broken R2 URLs ─────────────────────────────
echo "Checking product_images table...\n";
$images = $db->query("SELECT pi.id, pi.image_url, pi.product_id FROM product_images pi WHERE pi.image_url LIKE '%r2.dev%'")->fetchAll();

foreach ($images as $img) {
    $url = $img['image_url'];
    if (r2Exists($client, $bucket, $url, $publicBase)) {
        $skipped++;
        continue;
    }
    
    // Look up source URL in mirror map
    $src = $db->prepare("SELECT source_url FROM image_mirror_map WHERE r2_url = ?")->execute([$url]) 
           ? $db->query("SELECT source_url FROM image_mirror_map WHERE r2_url = " . $db->quote($url))->fetchColumn() 
           : null;
    
    if (empty($src)) {
        echo "  SKIP (no source): $url\n";
        $failed++;
        continue;
    }
    
    // Clear old cached map entry
    $db->prepare("DELETE FROM image_mirror_map WHERE r2_url = ?")->execute([$url]);
    
    $result = $svc->mirrorImage($src, 'remirror', 'gallery');
    if ($result['success']) {
        updateProductImageUrl($db, $url, $result['url']);
        echo "  ✅ gallery img #{$img['id']}: " . basename(parse_url($result['url'], PHP_URL_PATH)) . "\n";
        $fixed++;
    } else {
        echo "  ❌ gallery img #{$img['id']} FAILED: {$result['error']}\n";
        $failed++;
    }
}

// ── 2. Fix main_image on products table ───────────────────────────────────
echo "\nChecking products.main_image...\n";
$products = $db->query("SELECT id, sku, main_image, main_image_source_url FROM products WHERE main_image LIKE '%r2.dev%'")->fetchAll();

foreach ($products as $p) {
    $url = $p['main_image'];
    if (r2Exists($client, $bucket, $url, $publicBase)) {
        $skipped++;
        continue;
    }
    
    // Try source_url from products table first, then from mirror map
    $src = $p['main_image_source_url'];
    if (empty($src)) {
        $src = $db->query("SELECT source_url FROM image_mirror_map WHERE r2_url = " . $db->quote($url))->fetchColumn();
    }
    
    if (empty($src)) {
        echo "  SKIP (no source) product #{$p['id']} ({$p['sku']}): $url\n";
        $failed++;
        continue;
    }
    
    $db->prepare("DELETE FROM image_mirror_map WHERE r2_url = ?")->execute([$url]);
    $result = $svc->mirrorImage($src, $p['sku'] ?? 'prod', 'main');
    if ($result['success']) {
        updateProductMainImage($db, $p['id'], $result['url']);
        // Also update product_images if that row exists
        updateProductImageUrl($db, $url, $result['url']);
        echo "  ✅ main img product #{$p['id']} ({$p['sku']}): " . basename(parse_url($result['url'], PHP_URL_PATH)) . "\n";
        $fixed++;
    } else {
        echo "  ❌ main img product #{$p['id']} ({$p['sku']}) FAILED: {$result['error']}\n";
        $failed++;
    }
}

// ── 3. Fix variant swatch images ──────────────────────────────────────────
echo "\nChecking product_colors.swatch_hex_or_image...\n";
$colors = $db->query("SELECT id, swatch_hex_or_image FROM product_colors WHERE swatch_hex_or_image LIKE '%r2.dev%'")->fetchAll();

foreach ($colors as $c) {
    $url = $c['swatch_hex_or_image'];
    if (r2Exists($client, $bucket, $url, $publicBase)) {
        $skipped++;
        continue;
    }
    
    $src = $db->query("SELECT source_url FROM image_mirror_map WHERE r2_url = " . $db->quote($url))->fetchColumn();
    
    if (empty($src)) {
        echo "  SKIP (no source) color #{$c['id']}: $url\n";
        $failed++;
        continue;
    }
    
    $db->prepare("DELETE FROM image_mirror_map WHERE r2_url = ?")->execute([$url]);
    $result = $svc->mirrorImage($src, 'color-' . $c['id'], 'variant');
    if ($result['success']) {
        updateColorSwatch($db, $c['id'], $result['url']);
        echo "  ✅ swatch color #{$c['id']}: " . basename(parse_url($result['url'], PHP_URL_PATH)) . "\n";
        $fixed++;
    } else {
        echo "  ❌ swatch color #{$c['id']} FAILED: {$result['error']}\n";
        $failed++;
    }
}

// ── Summary ───────────────────────────────────────────────────────────────
echo "\n============================\n";
echo "✅ Fixed:   $fixed\n";
echo "⏭️  Skipped: $skipped (already in R2)\n";
echo "❌ Failed:  $failed (no source URL or download error)\n";
echo "============================\n";
