<?php
/**
 * cleanup_bulkflowai_images.php
 * ─────────────────────────────────────────────────────────────────────────────
 * One-time cleanup script:
 *   1. Remove product_images rows that still have bulkflowai URLs but whose
 *      R2 mirror already exists in another row for the same product.
 *   2. Mirror remaining bulkflowai gallery rows to R2 (replace in place).
 *   3. Mirror bulkflowai variant swatch URLs (product_colors) to R2.
 *   4. Mirror products.main_image bulkflowai URLs to R2.
 *   5. Remove any leftover duplicate image rows.
 *   6. Final count verification.
 *
 * Run: php cleanup_bulkflowai_images.php [--dry-run]
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Services/CloudflareR2.php';
require_once __DIR__ . '/app/Services/ImageMirrorService.php';

$dryRun = in_array('--dry-run', $argv ?? []);
echo ($dryRun ? "=== DRY RUN MODE ===" : "=== LIVE MODE ===") . PHP_EOL;

$db = \App\Core\Database::getInstance();
$r2 = new \App\Services\CloudflareR2();
$mirror = new \App\Services\ImageMirrorService();

if ($r2->getClient() === null) {
    echo "ERROR: R2 Client not initialized. Check config.\n";
    exit(1);
}

$isBulkflowai = fn($url) => strpos((string)$url, 'bulkflowai.com') !== false;
$isR2 = fn($url) => strpos((string)$url, 'r2.dev') !== false || strpos((string)$url, 'cloudflare') !== false;

// ─────────────────────────────────────────────────────────────────────────────
// STEP 0: Count before
// ─────────────────────────────────────────────────────────────────────────────
echo PHP_EOL . "=== BEFORE COUNTS ===" . PHP_EOL;

$cnt = $db->query("SELECT COUNT(*) FROM product_images WHERE image_url LIKE '%bulkflowai.com%'")->fetchColumn();
echo "product_images with bulkflowai URL: {$cnt}" . PHP_EOL;

$cntV = $db->query("SELECT COUNT(*) FROM product_colors WHERE swatch_hex_or_image LIKE '%bulkflowai.com%'")->fetchColumn();
echo "product_colors (variants) with bulkflowai URL: {$cntV}" . PHP_EOL;

$cntM = $db->query("SELECT COUNT(*) FROM products WHERE main_image LIKE '%bulkflowai.com%'")->fetchColumn();
echo "products.main_image with bulkflowai URL: {$cntM}" . PHP_EOL;

$cntDup = $db->query("SELECT COUNT(*) FROM product_images pi WHERE image_url LIKE '%bulkflowai.com%' AND EXISTS (SELECT 1 FROM image_mirror_map m WHERE m.source_url_hash = SHA2(pi.image_url, 256))")->fetchColumn();
echo "product_images rows whose R2 mirror already cached: {$cntDup}" . PHP_EOL;

// ─────────────────────────────────────────────────────────────────────────────
// STEP 1: Delete product_images rows that already have an R2 mirror row
//         for the same product (the mirror row exists = R2 row already present)
// ─────────────────────────────────────────────────────────────────────────────
echo PHP_EOL . "=== STEP 1: Remove redundant bulkflowai rows (R2 copy exists) ===" . PHP_EOL;

$stmt = $db->query("
    SELECT pi.id, pi.product_id, pi.image_url
    FROM product_images pi
    JOIN image_mirror_map m ON m.source_url_hash = SHA2(pi.image_url, 256)
    WHERE pi.image_url LIKE '%bulkflowai.com%'
");
$toDelete = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Rows to delete (R2 copy exists): " . count($toDelete) . PHP_EOL;

foreach ($toDelete as $row) {
    echo "  [DEL] product_id={$row['product_id']} id={$row['id']} url=" . substr($row['image_url'], 0, 70) . PHP_EOL;
    if (!$dryRun) {
        $db->prepare("DELETE FROM product_images WHERE id = ?")->execute([$row['id']]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// STEP 2: Mirror remaining bulkflowai product_images rows to R2
// ─────────────────────────────────────────────────────────────────────────────
echo PHP_EOL . "=== STEP 2: Mirror remaining bulkflowai gallery rows ===" . PHP_EOL;

$stmtG = $db->query("
    SELECT pi.id, pi.image_url, p.sku, p.id as product_id
    FROM product_images pi
    JOIN products p ON p.id = pi.product_id
    WHERE pi.image_url LIKE '%bulkflowai.com%'
");
$galleryRows = $stmtG->fetchAll(PDO::FETCH_ASSOC);
echo "Rows to mirror: " . count($galleryRows) . PHP_EOL;

$galleryMirrored = 0;
$galleryFailed = 0;
foreach ($galleryRows as $g) {
    $res = $mirror->mirrorImage($g['image_url'], $g['sku'], 'gallery');
    if ($res['success']) {
        echo "  [OK]  id={$g['id']} sku={$g['sku']} -> " . $res['url'] . PHP_EOL;
        if (!$dryRun) {
            $db->prepare("UPDATE product_images SET image_url = ?, image_path = ? WHERE id = ?")
               ->execute([$res['url'], $res['url'], $g['id']]);
            // Remove any duplicate R2 row that might exist for same product
            $db->prepare("DELETE FROM product_images WHERE product_id = ? AND image_url = ? AND id != ?")
               ->execute([$g['product_id'], $res['url'], $g['id']]);
        }
        $galleryMirrored++;
    } else {
        echo "  [FAIL] id={$g['id']} sku={$g['sku']} err=" . ($res['error'] ?? '?') . PHP_EOL;
        $galleryFailed++;
    }
}
echo "Gallery: mirrored={$galleryMirrored}, failed={$galleryFailed}" . PHP_EOL;

// ─────────────────────────────────────────────────────────────────────────────
// STEP 3: Mirror bulkflowai variant swatch images
// ─────────────────────────────────────────────────────────────────────────────
echo PHP_EOL . "=== STEP 3: Mirror bulkflowai variant swatch images ===" . PHP_EOL;

$stmtC = $db->query("
    SELECT pc.id, pc.swatch_hex_or_image, p.sku
    FROM product_colors pc
    JOIN products p ON p.id = pc.product_id
    WHERE pc.swatch_hex_or_image LIKE '%bulkflowai.com%'
");
$colorRows = $stmtC->fetchAll(PDO::FETCH_ASSOC);
echo "Variant rows to mirror: " . count($colorRows) . PHP_EOL;

$varMirrored = 0;
$varFailed = 0;
foreach ($colorRows as $c) {
    $res = $mirror->mirrorImage($c['swatch_hex_or_image'], $c['sku'], 'variant');
    if ($res['success']) {
        echo "  [OK]  color_id={$c['id']} sku={$c['sku']} -> " . $res['url'] . PHP_EOL;
        if (!$dryRun) {
            $db->prepare("UPDATE product_colors SET swatch_hex_or_image = ? WHERE id = ?")
               ->execute([$res['url'], $c['id']]);
        }
        $varMirrored++;
    } else {
        echo "  [FAIL] color_id={$c['id']} sku={$c['sku']} err=" . ($res['error'] ?? '?') . PHP_EOL;
        $varFailed++;
    }
}
echo "Variants: mirrored={$varMirrored}, failed={$varFailed}" . PHP_EOL;

// ─────────────────────────────────────────────────────────────────────────────
// STEP 4: Mirror bulkflowai products.main_image
// ─────────────────────────────────────────────────────────────────────────────
echo PHP_EOL . "=== STEP 4: Mirror bulkflowai products.main_image ===" . PHP_EOL;

$stmtM = $db->query("SELECT id, sku, main_image FROM products WHERE main_image LIKE '%bulkflowai.com%'");
$mainRows = $stmtM->fetchAll(PDO::FETCH_ASSOC);
echo "Products.main_image to mirror: " . count($mainRows) . PHP_EOL;

$mainMirrored = 0;
$mainFailed = 0;
foreach ($mainRows as $p) {
    $res = $mirror->mirrorImage($p['main_image'], $p['sku'], 'main');
    if ($res['success']) {
        echo "  [OK]  product_id={$p['id']} sku={$p['sku']} -> " . $res['url'] . PHP_EOL;
        if (!$dryRun) {
            $oldUrl = $p['main_image'];
            $newUrl = $res['url'];
            $pid = $p['id'];

            $db->prepare("UPDATE products SET main_image = ? WHERE id = ?")->execute([$newUrl, $pid]);
            // Update matching product_images row
            $db->prepare("UPDATE product_images SET image_url = ?, image_path = ? WHERE product_id = ? AND image_url = ?")
               ->execute([$newUrl, $newUrl, $pid, $oldUrl]);
            // If no row existed for the old URL, ensure main_image has a row
            $chk = $db->prepare("SELECT id FROM product_images WHERE product_id = ? AND image_url = ?");
            $chk->execute([$pid, $newUrl]);
            if (!$chk->fetch()) {
                $db->prepare("INSERT INTO product_images (product_id, image_url, image_path, sort_order, is_primary) VALUES (?, ?, ?, 0, 1)")
                   ->execute([$pid, $newUrl, $newUrl]);
            }
            // Remove any duplicate rows for this new URL
            $db->prepare("
                DELETE FROM product_images WHERE product_id = ? AND image_url = ? AND id NOT IN (
                    SELECT id FROM (SELECT MIN(id) as id FROM product_images WHERE product_id = ? AND image_url = ?) t
                )
            ")->execute([$pid, $newUrl, $pid, $newUrl]);
        }
        $mainMirrored++;
    } else {
        echo "  [FAIL] product_id={$p['id']} sku={$p['sku']} err=" . ($res['error'] ?? '?') . PHP_EOL;
        $mainFailed++;
    }
}
echo "Main image: mirrored={$mainMirrored}, failed={$mainFailed}" . PHP_EOL;

// ─────────────────────────────────────────────────────────────────────────────
// STEP 5: Final deduplication pass
// ─────────────────────────────────────────────────────────────────────────────
echo PHP_EOL . "=== STEP 5: Final deduplication ===" . PHP_EOL;

$dupStmt = $db->query("
    SELECT product_id, image_url, MIN(id) as keep_id, COUNT(*) as cnt
    FROM product_images
    WHERE image_url IS NOT NULL AND image_url != ''
    GROUP BY product_id, image_url
    HAVING cnt > 1
");
$dups = $dupStmt->fetchAll(PDO::FETCH_ASSOC);
echo "Duplicate groups found: " . count($dups) . PHP_EOL;
foreach ($dups as $dup) {
    echo "  [DUP] product_id={$dup['product_id']} count={$dup['cnt']} url=" . substr($dup['image_url'], 0, 70) . PHP_EOL;
    if (!$dryRun) {
        $db->prepare("DELETE FROM product_images WHERE product_id = ? AND image_url = ? AND id != ?")
           ->execute([$dup['product_id'], $dup['image_url'], $dup['keep_id']]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// STEP 6: Final verification
// ─────────────────────────────────────────────────────────────────────────────
echo PHP_EOL . "=== FINAL COUNTS (target: all zeros) ===" . PHP_EOL;
$cnt  = $db->query("SELECT COUNT(*) FROM product_images WHERE image_url LIKE '%bulkflowai.com%'")->fetchColumn();
$cntV = $db->query("SELECT COUNT(*) FROM product_colors WHERE swatch_hex_or_image LIKE '%bulkflowai.com%'")->fetchColumn();
$cntM = $db->query("SELECT COUNT(*) FROM products WHERE main_image LIKE '%bulkflowai.com%'")->fetchColumn();
echo "product_images with bulkflowai URL: {$cnt}" . ($cnt == 0 ? " ✓" : " ✗ NEEDS ATTENTION") . PHP_EOL;
echo "product_colors  with bulkflowai URL: {$cntV}" . ($cntV == 0 ? " ✓" : " ✗ NEEDS ATTENTION") . PHP_EOL;
echo "products.main_image bulkflowai URL:  {$cntM}" . ($cntM == 0 ? " ✓" : " ✗ NEEDS ATTENTION") . PHP_EOL;

echo PHP_EOL . ($dryRun ? "Dry run complete. Run without --dry-run to apply." : "Cleanup complete!") . PHP_EOL;
