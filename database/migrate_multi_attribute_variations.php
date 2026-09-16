<?php
/**
 * Migration: Multi-Attribute Product Variations
 *
 * Creates:
 *   - variation_attributes          (global attribute registry)
 *   - variation_attribute_values    (global attribute value registry)
 *   - product_variation_attribute_map (junction: variant ↔ attribute_value)
 *
 * Adds additive columns to:
 *   - product_variants  (sku alias, gst_percent, hsn_code, image_id)
 *   - order_items       (variant_id, variant_label)
 *   - wishlists         (variant_id)
 *
 * Backfills existing product_variants rows into the new junction table.
 *
 * Usage:
 *   php database/migrate_multi_attribute_variations.php            # commit
 *   php database/migrate_multi_attribute_variations.php --dry-run  # preview only
 *   http://localhost/importwala/database/migrate_multi_attribute_variations.php?dry_run=1
 *
 * SAFE TO RE-RUN — all steps are idempotent.
 */

declare(strict_types=1);

// ── Bootstrap ────────────────────────────────────────────────────────────────
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

spl_autoload_register(function (string $class): void {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}
require_once ROOT_PATH . '/app/Helpers/Functions.php';

$db = App\Core\Database::getInstance();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Detect dry-run mode
$isDryRun = (PHP_SAPI === 'cli')
    ? in_array('--dry-run', $argv ?? [], true)
    : !empty($_GET['dry_run']);

$isHtml = (PHP_SAPI !== 'cli');

if ($isHtml) {
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Variation Migration</title>";
    echo "<style>body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:2rem;line-height:1.7}
    .ok{color:#4ade80}.warn{color:#fbbf24}.err{color:#f87171}.head{color:#60a5fa;font-weight:bold;font-size:1.1rem}
    .dry{background:#1e293b;border:1px solid #f59e0b;padding:.5rem 1rem;border-radius:.5rem;margin-bottom:1rem}</style></head><body>";
}

function out(string $msg, string $type = 'info'): void
{
    global $isHtml;
    if ($isHtml) {
        $class = match($type) { 'ok' => 'ok', 'warn' => 'warn', 'err' => 'err', 'head' => 'head', default => '' };
        echo "<span class='{$class}'>" . htmlspecialchars($msg) . "</span><br>\n";
    } else {
        $prefix = match($type) { 'ok' => '✓ ', 'warn' => '⚠ ', 'err' => '✗ ', 'head' => "\n▶ ", default => '  ' };
        echo $prefix . $msg . "\n";
    }
}

function columnExists(PDO $db, string $table, string $col): bool
{
    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $col]);
    return (int)$stmt->fetchColumn() > 0;
}

function tableExists(PDO $db, string $table): bool
{
    $stmt = $db->query("SHOW TABLES LIKE " . $db->quote($table));
    return (bool)$stmt->fetchColumn();
}

function fkExists(PDO $db, string $table, string $col, string $refTable): bool
{
    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
        AND REFERENCED_TABLE_NAME = ?");
    $stmt->execute([$table, $col, $refTable]);
    return (int)$stmt->fetchColumn() > 0;
}

if ($isDryRun) {
    $msg = '🔍 DRY RUN MODE — no changes will be committed to the database.';
    if ($isHtml) {
        echo "<div class='dry'><strong>$msg</strong></div>\n";
    } else {
        echo "\n⚠  $msg\n\n";
    }
}

$changes = []; // Track all planned/executed DDL

// ─────────────────────────────────────────────────────────────────────────────
out('[1/8] variation_attributes table', 'head');
// ─────────────────────────────────────────────────────────────────────────────
if (!tableExists($db, 'variation_attributes')) {
    $sql = "CREATE TABLE `variation_attributes` (
        `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name`           VARCHAR(100) NOT NULL,
        `attribute_type` ENUM('swatch','button','text') NOT NULL DEFAULT 'button',
        `display_order`  INT NOT NULL DEFAULT 0,
        `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uq_attr_name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!$isDryRun) {
        $db->exec($sql);
    }
    $changes[] = 'CREATE TABLE variation_attributes';
    out('Created variation_attributes', 'ok');
} else {
    out('variation_attributes already exists — skipped', 'warn');
}

// ─────────────────────────────────────────────────────────────────────────────
out('[2/8] variation_attribute_values table', 'head');
// ─────────────────────────────────────────────────────────────────────────────
if (!tableExists($db, 'variation_attribute_values')) {
    $sql = "CREATE TABLE `variation_attribute_values` (
        `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `attribute_id`         INT UNSIGNED NOT NULL,
        `value`                VARCHAR(100) NOT NULL,
        `swatch_hex_or_image`  VARCHAR(255) NULL,
        `display_order`        INT NOT NULL DEFAULT 0,
        `created_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uq_attr_val` (`attribute_id`, `value`),
        FOREIGN KEY (`attribute_id`) REFERENCES `variation_attributes` (`id`) ON DELETE CASCADE,
        INDEX `idx_attribute_id` (`attribute_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!$isDryRun) {
        $db->exec($sql);
    }
    $changes[] = 'CREATE TABLE variation_attribute_values';
    out('Created variation_attribute_values', 'ok');
} else {
    out('variation_attribute_values already exists — skipped', 'warn');
}

// ─────────────────────────────────────────────────────────────────────────────
out('[3/8] product_variation_attribute_map junction table', 'head');
// ─────────────────────────────────────────────────────────────────────────────
if (!tableExists($db, 'product_variation_attribute_map')) {
    $sql = "CREATE TABLE `product_variation_attribute_map` (
        `variation_id`       INT UNSIGNED NOT NULL,
        `attribute_value_id` INT UNSIGNED NOT NULL,
        PRIMARY KEY (`variation_id`, `attribute_value_id`),
        FOREIGN KEY (`variation_id`)       REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`attribute_value_id`) REFERENCES `variation_attribute_values` (`id`) ON DELETE CASCADE,
        INDEX `idx_attr_value` (`attribute_value_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!$isDryRun) {
        $db->exec($sql);
    }
    $changes[] = 'CREATE TABLE product_variation_attribute_map';
    out('Created product_variation_attribute_map', 'ok');
} else {
    out('product_variation_attribute_map already exists — skipped', 'warn');
}

// ─────────────────────────────────────────────────────────────────────────────
out('[4/8] Additive columns on product_variants', 'head');
// ─────────────────────────────────────────────────────────────────────────────
$variantAlters = [
    // SKU alias (variant_code already exists; sku is new searchable alias)
    ['col' => 'sku',          'ddl' => "ALTER TABLE `product_variants` ADD COLUMN `sku` VARCHAR(100) NULL DEFAULT NULL AFTER `variant_code`"],
    ['col' => 'gst_percent',  'ddl' => "ALTER TABLE `product_variants` ADD COLUMN `gst_percent` DECIMAL(5,2) NULL DEFAULT NULL AFTER `one_piece_price`"],
    ['col' => 'hsn_code',     'ddl' => "ALTER TABLE `product_variants` ADD COLUMN `hsn_code` VARCHAR(30) NULL DEFAULT NULL AFTER `gst_percent`"],
    ['col' => 'image_id',     'ddl' => "ALTER TABLE `product_variants` ADD COLUMN `image_id` INT UNSIGNED NULL DEFAULT NULL AFTER `image_url`"],
];

foreach ($variantAlters as $alter) {
    if (!columnExists($db, 'product_variants', $alter['col'])) {
        if (!$isDryRun) {
            $db->exec($alter['ddl']);
        }
        $changes[] = "ALTER product_variants ADD {$alter['col']}";
        out("Added column product_variants.{$alter['col']}", 'ok');
    } else {
        out("Column product_variants.{$alter['col']} already exists — skipped", 'warn');
    }
}

// Sync sku from variant_code for existing rows
if (!$isDryRun && columnExists($db, 'product_variants', 'sku')) {
    $synced = $db->exec("UPDATE `product_variants` SET `sku` = `variant_code` WHERE `sku` IS NULL AND `variant_code` IS NOT NULL");
    if ($synced > 0) {
        out("Synced sku from variant_code for {$synced} rows", 'ok');
    }
}

// Add unique index on sku if not already there
try {
    $idxCheck = $db->query("SHOW INDEX FROM `product_variants` WHERE Key_name = 'uq_pv_sku'")->fetch();
    if (!$idxCheck) {
        if (!$isDryRun) {
            $db->exec("ALTER TABLE `product_variants` ADD UNIQUE KEY `uq_pv_sku` (`sku`)");
        }
        $changes[] = 'ADD UNIQUE KEY uq_pv_sku on product_variants.sku';
        out('Added unique index on product_variants.sku', 'ok');
    }
} catch (\Throwable $e) {
    out('Could not add unique index on sku (may have duplicates): ' . $e->getMessage(), 'warn');
}

// ─────────────────────────────────────────────────────────────────────────────
out('[5/8] Additive columns on order_items', 'head');
// ─────────────────────────────────────────────────────────────────────────────
if (!columnExists($db, 'order_items', 'variant_id')) {
    if (!$isDryRun) {
        $db->exec("ALTER TABLE `order_items` ADD COLUMN `variant_id` INT UNSIGNED NULL DEFAULT NULL AFTER `product_id`");
    }
    $changes[] = 'ALTER order_items ADD variant_id';
    out('Added column order_items.variant_id', 'ok');
} else {
    out('Column order_items.variant_id already exists — skipped', 'warn');
}

if (!columnExists($db, 'order_items', 'variant_label')) {
    if (!$isDryRun) {
        $db->exec("ALTER TABLE `order_items` ADD COLUMN `variant_label` VARCHAR(255) NULL DEFAULT NULL AFTER `variant_id`");
    }
    $changes[] = 'ALTER order_items ADD variant_label';
    out('Added column order_items.variant_label', 'ok');
} else {
    out('Column order_items.variant_label already exists — skipped', 'warn');
}

// ─────────────────────────────────────────────────────────────────────────────
out('[6/8] Additive column on wishlists', 'head');
// ─────────────────────────────────────────────────────────────────────────────
if (!columnExists($db, 'wishlists', 'variant_id')) {
    if (!$isDryRun) {
        $db->exec("ALTER TABLE `wishlists` ADD COLUMN `variant_id` INT UNSIGNED NULL DEFAULT NULL AFTER `product_id`");
    }
    $changes[] = 'ALTER wishlists ADD variant_id';
    out('Added column wishlists.variant_id', 'ok');
} else {
    out('Column wishlists.variant_id already exists — skipped', 'warn');
}

// ─────────────────────────────────────────────────────────────────────────────
out('[7/8] Backfill existing product_variants into junction tables', 'head');
// ─────────────────────────────────────────────────────────────────────────────

// Count existing variants
$totalVariants = (int)$db->query("SELECT COUNT(*) FROM `product_variants`")->fetchColumn();
out("Found {$totalVariants} existing product_variant rows to backfill", 'info');

if ($totalVariants === 0) {
    out('No existing variants to backfill — skipped', 'warn');
} else {
    if (!$isDryRun) {
        $db->beginTransaction();
    }

    try {
        // Step 1: Ensure "Color" attribute exists (id = ?), type = swatch
        $colorAttrId = null;
        if (!$isDryRun) {
            $stmt = $db->prepare("INSERT INTO `variation_attributes` (`name`, `attribute_type`, `display_order`) 
                VALUES ('Color', 'swatch', 1)
                ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(`id`)");
            $stmt->execute();
            $colorAttrId = (int)$db->lastInsertId();
            if (!$colorAttrId) {
                $r = $db->query("SELECT id FROM `variation_attributes` WHERE `name` = 'Color'")->fetch();
                $colorAttrId = (int)$r['id'];
            }
        } else {
            $colorAttrId = 0; // placeholder for dry run
        }
        out("Color attribute id = {$colorAttrId}", 'ok');

        // Step 2: Fetch all distinct attribute_values from product_variants
        $existingVariants = $db->query(
            "SELECT id, attribute_label, attribute_value FROM `product_variants` ORDER BY id ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $skipped   = 0;
        $processed = 0;

        foreach ($existingVariants as $v) {
            $variantId      = (int)$v['id'];
            $attrLabel      = trim($v['attribute_label'] ?? 'Color');
            $attrValue      = trim($v['attribute_value'] ?? '');

            if (empty($attrValue) || $attrValue === 'Default') {
                $skipped++;
                continue;
            }

            // Check if already mapped
            if (!$isDryRun) {
                $alreadyMapped = (int)$db->prepare(
                    "SELECT COUNT(*) FROM `product_variation_attribute_map` WHERE `variation_id` = ?"
                )->execute([$variantId]) ? $db->query(
                    "SELECT COUNT(*) FROM `product_variation_attribute_map` WHERE `variation_id` = {$variantId}"
                )->fetchColumn() : 0;
            } else {
                $alreadyMapped = false;
            }

            if ($alreadyMapped) {
                $skipped++;
                continue;
            }

            if (!$isDryRun) {
                // Determine attribute ID (use Color if label looks like color, otherwise create/find)
                $useAttrId = $colorAttrId;
                if (!empty($attrLabel) && strtolower($attrLabel) !== 'color' && strtolower($attrLabel) !== 'variant') {
                    // Check/create the attribute
                    $attrType = 'button'; // default for non-color attributes
                    $stmtAttr = $db->prepare("INSERT INTO `variation_attributes` (`name`, `attribute_type`, `display_order`)
                        VALUES (?, ?, 10) ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(`id`)");
                    $stmtAttr->execute([$attrLabel, $attrType]);
                    $useAttrId = (int)$db->lastInsertId();
                    if (!$useAttrId) {
                        $r2 = $db->prepare("SELECT id FROM `variation_attributes` WHERE `name` = ?")->execute([$attrLabel])
                            ? $db->query("SELECT id FROM `variation_attributes` WHERE `name` = " . $db->quote($attrLabel))->fetch()
                            : null;
                        $useAttrId = $r2 ? (int)$r2['id'] : $colorAttrId;
                    }
                }

                // Upsert attribute value
                $stmtVal = $db->prepare("INSERT INTO `variation_attribute_values` (`attribute_id`, `value`)
                    VALUES (?, ?) ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(`id`)");
                $stmtVal->execute([$useAttrId, $attrValue]);
                $attrValueId = (int)$db->lastInsertId();
                if (!$attrValueId) {
                    $r3 = $db->query("SELECT id FROM `variation_attribute_values` WHERE `attribute_id` = {$useAttrId} AND `value` = " . $db->quote($attrValue))->fetch();
                    $attrValueId = $r3 ? (int)$r3['id'] : 0;
                }

                if ($attrValueId) {
                    // Insert junction row
                    $stmtMap = $db->prepare("INSERT IGNORE INTO `product_variation_attribute_map` (`variation_id`, `attribute_value_id`) VALUES (?, ?)");
                    $stmtMap->execute([$variantId, $attrValueId]);
                }
            }

            $processed++;
        }

        if (!$isDryRun) {
            $db->commit();
        }

        out("Backfilled {$processed} variant ↔ attribute mappings", 'ok');
        if ($skipped > 0) {
            out("Skipped {$skipped} rows (already mapped or empty value)", 'warn');
        }

    } catch (\Throwable $e) {
        if (!$isDryRun) {
            $db->rollBack();
        }
        out('ERROR during backfill: ' . $e->getMessage(), 'err');
    }
}

// ─────────────────────────────────────────────────────────────────────────────
out('[8/8] Summary', 'head');
// ─────────────────────────────────────────────────────────────────────────────
if ($isDryRun) {
    out('DRY RUN complete — ' . count($changes) . ' changes would be applied:', 'warn');
    foreach ($changes as $c) {
        out('  · ' . $c);
    }
    out('Re-run without --dry-run to commit.', 'warn');
} else {
    out('Migration complete! Changes applied: ' . count($changes), 'ok');
    foreach ($changes as $c) {
        out('  · ' . $c, 'ok');
    }
}

// Quick verification: show table row counts
if (!$isDryRun) {
    $counts = [
        'variation_attributes'           => "SELECT COUNT(*) FROM `variation_attributes`",
        'variation_attribute_values'      => "SELECT COUNT(*) FROM `variation_attribute_values`",
        'product_variation_attribute_map' => "SELECT COUNT(*) FROM `product_variation_attribute_map`",
    ];
    out("\nTable row counts:", 'head');
    foreach ($counts as $tbl => $sql) {
        try {
            $n = (int)$db->query($sql)->fetchColumn();
            out("  {$tbl}: {$n} rows", 'ok');
        } catch (\Throwable $e) {
            out("  {$tbl}: ERROR — " . $e->getMessage(), 'err');
        }
    }
}

if ($isHtml) {
    echo "</body></html>";
}
