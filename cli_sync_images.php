<?php
/**
 * cli_sync_images.php
 * Background job to mirror pending raw image URLs to Cloudflare R2.
 * Run this via CLI or spawned background process.
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Services/CloudflareR2.php';
require_once __DIR__ . '/app/Services/ImageMirrorService.php';

$db = \App\Core\Database::getInstance();
$r2 = new \App\Services\CloudflareR2();
$mirrorService = new \App\Services\ImageMirrorService();

if ($r2->getClient() === null) {
    echo "R2 Client not initialized.\n";
    exit(1);
}

// 1. Sync Main Images (products.main_image) + keep product_images in sync
$stmtMain = $db->query("SELECT id, sku, main_image FROM products WHERE main_image IS NOT NULL AND main_image != '' AND main_image NOT LIKE '%r2.dev%' AND main_image NOT LIKE '%cloudflare%'");
$mainProducts = $stmtMain->fetchAll();
foreach ($mainProducts as $p) {
    $res = $mirrorService->mirrorImage($p['main_image'], $p['sku'], 'main');
    if ($res['success']) {
        $oldUrl = $p['main_image'];
        $newUrl = $res['url'];
        $pid    = $p['id'];

        // Update products table
        $db->prepare("UPDATE products SET main_image = ? WHERE id = ?")->execute([$newUrl, $pid]);

        // Update matching row in product_images (replace old URL with new R2 URL)
        $db->prepare("UPDATE product_images SET image_url = ?, image_path = ? WHERE product_id = ? AND image_url = ?")
           ->execute([$newUrl, $newUrl, $pid, $oldUrl]);

        // Deduplicate: if a row with newUrl already existed (before the update), remove the extra
        $db->prepare("
            DELETE FROM product_images
            WHERE product_id = ? AND image_url = ? AND id NOT IN (
                SELECT id FROM (
                    SELECT MIN(id) as id FROM product_images WHERE product_id = ? AND image_url = ?
                ) t
            )
        ")->execute([$pid, $newUrl, $pid, $newUrl]);
    }
}

// 2. Sync Gallery Images (product_images rows that are NOT main_image)
$stmtGallery = $db->query("
    SELECT pi.id, pi.image_url, p.sku, p.id as product_id
    FROM product_images pi
    JOIN products p ON p.id = pi.product_id
    WHERE pi.image_url IS NOT NULL AND pi.image_url != ''
      AND pi.image_url NOT LIKE '%r2.dev%' AND pi.image_url NOT LIKE '%cloudflare%'
");
$galleryImages = $stmtGallery->fetchAll();
foreach ($galleryImages as $g) {
    $res = $mirrorService->mirrorImage($g['image_url'], $g['sku'], 'gallery');
    if ($res['success']) {
        $newUrl = $res['url'];
        $pid    = $g['product_id'];

        // Update this row
        $db->prepare("UPDATE product_images SET image_url = ?, image_path = ? WHERE id = ?")
           ->execute([$newUrl, $newUrl, $g['id']]);

        // Deduplicate: delete any OTHER rows with same product_id + new URL
        $db->prepare("DELETE FROM product_images WHERE product_id = ? AND image_url = ? AND id != ?")
           ->execute([$pid, $newUrl, $g['id']]);
    }
}

// 3. Sync Variant Images (product_colors.image_url)
$stmtVariants = $db->query("
    SELECT pc.id, pc.image_url, p.sku
    FROM product_colors pc
    JOIN products p ON p.id = pc.product_id
    WHERE pc.image_url IS NOT NULL AND pc.image_url != ''
      AND pc.image_url NOT LIKE '%r2.dev%' AND pc.image_url NOT LIKE '%cloudflare%'
");
$variantImages = $stmtVariants->fetchAll();
foreach ($variantImages as $v) {
    $res = $mirrorService->mirrorImage($v['image_url'], $v['sku'], 'variant');
    if ($res['success']) {
        $db->prepare("UPDATE product_colors SET image_url = ? WHERE id = ?")
           ->execute([$res['url'], $v['id']]);
    }
}

// 4. Final deduplication pass — catch any remaining duplicates across all products
$dupStmt = $db->query("
    SELECT product_id, image_url, MIN(id) as keep_id
    FROM product_images
    WHERE image_url IS NOT NULL AND image_url != ''
    GROUP BY product_id, image_url
    HAVING COUNT(*) > 1
");
$duplicates = $dupStmt->fetchAll();
foreach ($duplicates as $dup) {
    $db->prepare("DELETE FROM product_images WHERE product_id = ? AND image_url = ? AND id != ?")
       ->execute([$dup['product_id'], $dup['image_url'], $dup['keep_id']]);
}

echo "Image sync completed.\n";

