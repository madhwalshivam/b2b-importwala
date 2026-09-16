<?php

/**
 * Migration Script: Single vs Double (Nested) Variation Architecture
 *
 * Mode 'none': Base product only (no variations).
 * Mode 'single': Product -> ProductColors (Color name, Swatch, SKU, Price, Stock).
 * Mode 'double': Product -> ProductColors -> ProductColorSizes (Size label, SKU, Price, Stock, GST%, HSN).
 *
 * Run: php database/migrate_nested_variations.php [--dry-run]
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

date_default_timezone_set('Asia/Kolkata');

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

$isDryRun = (PHP_SAPI === 'cli')
    ? in_array('--dry-run', $argv ?? [], true)
    : !empty($_GET['dry_run']);

$isHtml = (PHP_SAPI !== 'cli');

if ($isHtml) {
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Nested Variations Migration</title>";
    echo "<style>body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:2rem;line-height:1.7}
    .ok{color:#4ade80}.warn{color:#fbbf24}.err{color:#f87171}.head{color:#60a5fa;font-weight:bold;font-size:1.1rem}</style></head><body>";
}

function logMsg(string $msg, string $type = 'info'): void {
    global $isHtml;
    $prefix = match ($type) {
        'ok' => '✓ ',
        'warn' => '⚠ ',
        'err' => '✖ ',
        'head' => "\n▶ ",
        default => '  ',
    };
    if ($isHtml) {
        $cls = $type;
        echo "<div class='{$cls}'>" . htmlspecialchars($prefix . $msg) . "</div>";
    } else {
        echo $prefix . $msg . "\n";
    }
}

logMsg("Single vs Double (Nested) Variation Migration", 'head');
if ($isDryRun) {
    logMsg("DRY RUN MODE ENABLED — NO CHANGES WILL BE COMMITTED", 'warn');
}

try {
    // 1. Add variation_mode column to products table if missing
    logMsg("Ensuring products.variation_mode column exists", 'head');
    $stmtCol = $db->query("SHOW COLUMNS FROM `products` LIKE 'variation_mode'");
    if (!$stmtCol->fetch()) {
        if (!$isDryRun) {
            $db->exec("ALTER TABLE `products` ADD COLUMN `variation_mode` ENUM('none','single','double') NOT NULL DEFAULT 'none' AFTER `status`");
        }
        logMsg("Added products.variation_mode column", 'ok');
    } else {
        logMsg("Column products.variation_mode already exists", 'ok');
    }

    // 2. Create product_colors table
    logMsg("Creating product_colors table if not exists", 'head');
    $createColorsSql = "
        CREATE TABLE IF NOT EXISTS `product_colors` (
            `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `product_id`          INT UNSIGNED NOT NULL,
            `color_name`          VARCHAR(100) NOT NULL,
            `swatch_hex_or_image` VARCHAR(255) NULL,
            `sku`                 VARCHAR(100) NULL,
            `price`               DECIMAL(10,2) NULL,
            `stock_qty`           INT NULL,
            `display_order`       INT DEFAULT 0,
            `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    if (!$isDryRun) {
        $db->exec($createColorsSql);
    }
    logMsg("Table product_colors ready", 'ok');

    // 3. Create product_color_sizes table
    logMsg("Creating product_color_sizes table if not exists", 'head');
    $createSizesSql = "
        CREATE TABLE IF NOT EXISTS `product_color_sizes` (
            `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `color_id`      INT UNSIGNED NOT NULL,
            `size_label`    VARCHAR(100) NOT NULL,
            `sku`           VARCHAR(100) NULL,
            `price`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `stock_qty`     INT NOT NULL DEFAULT 0,
            `gst_percent`   DECIMAL(5,2) NULL,
            `hsn_code`      VARCHAR(30) NULL,
            `image_id`      INT UNSIGNED NULL,
            `display_order` INT DEFAULT 0,
            `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`color_id`) REFERENCES `product_colors`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `uq_pcs_sku` (`sku`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    if (!$isDryRun) {
        $db->exec($createSizesSql);
    }
    logMsg("Table product_color_sizes ready", 'ok');

    // 4. Backfill existing product_variants into product_colors and product_color_sizes
    logMsg("Migrating existing variant data", 'head');
    $stmtVar = $db->query("SELECT DISTINCT pv.product_id FROM `product_variants` pv JOIN `products` p ON p.id = pv.product_id WHERE pv.is_active = 1");
    $productIds = $stmtVar->fetchAll(PDO::FETCH_COLUMN);

    logMsg("Found " . count($productIds) . " products with existing variants to process");

    if (!$isDryRun) {
        $db->beginTransaction();
    }

    $colorInsStmt = $db->prepare("
        INSERT INTO `product_colors` (product_id, color_name, swatch_hex_or_image, sku, price, stock_qty, display_order)
        VALUES (:product_id, :color_name, :swatch, :sku, :price, :stock_qty, :display_order)
    ");

    $sizeInsStmt = $db->prepare("
        INSERT INTO `product_color_sizes` (color_id, size_label, sku, price, stock_qty, gst_percent, hsn_code, display_order)
        VALUES (:color_id, :size_label, :sku, :price, :stock_qty, :gst_percent, :hsn_code, :display_order)
    ");

    $modeUpdStmt = $db->prepare("UPDATE `products` SET `variation_mode` = ? WHERE `id` = ?");

    $migratedColors = 0;
    $migratedSizes = 0;

    foreach ($productIds as $pid) {
        $pid = (int)$pid;

        // Check if already migrated
        $checkExisting = $db->prepare("SELECT COUNT(*) FROM `product_colors` WHERE `product_id` = ?");
        $checkExisting->execute([$pid]);
        if ((int)$checkExisting->fetchColumn() > 0) {
            continue;
        }

        // Fetch variants for this product
        $vStmt = $db->prepare("SELECT * FROM `product_variants` WHERE `product_id` = ? AND `is_active` = 1 ORDER BY `sort_order` ASC, `id` ASC");
        $vStmt->execute([$pid]);
        $variants = $vStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($variants)) {
            continue;
        }

        // Check if multi-attribute mapping exists (Color + Size)
        $hasMultiAttr = false;
        foreach ($variants as $v) {
            $mapStmt = $db->prepare("
                SELECT va.name, vav.value
                FROM `product_variation_attribute_map` pvam
                JOIN `variation_attribute_values` vav ON vav.id = pvam.attribute_value_id
                JOIN `variation_attributes` va ON va.id = vav.attribute_id
                WHERE pvam.variation_id = ?
            ");
            $mapStmt->execute([(int)$v['id']]);
            $attrs = $mapStmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($attrs) > 1) {
                $hasMultiAttr = true;
                break;
            }
        }

        if (!$hasMultiAttr) {
            // Mode = SINGLE (Color variations only)
            foreach ($variants as $idx => $v) {
                $colorName = !empty($v['attribute_value']) ? $v['attribute_value'] : 'Default Color';
                $sku = !empty($v['sku']) ? $v['sku'] : $v['variant_code'];
                $price = (float)($v['one_piece_price'] > 0 ? $v['one_piece_price'] : $v['wholesale_price']);
                $stock = (int)$v['stock_quantity'];

                if (!$isDryRun) {
                    $colorInsStmt->execute([
                        ':product_id'    => $pid,
                        ':color_name'    => $colorName,
                        ':swatch'        => null,
                        ':sku'           => $sku,
                        ':price'         => $price,
                        ':stock_qty'     => $stock,
                        ':display_order' => $idx + 1,
                    ]);
                }
                $migratedColors++;
            }
            if (!$isDryRun) {
                $modeUpdStmt->execute(['single', $pid]);
            }
        } else {
            // Mode = DOUBLE (Color + Size nested)
            // Group variants by Color
            $colorMap = []; // colorName => colorId
            $colorIdx = 0;

            foreach ($variants as $v) {
                $mapStmt = $db->prepare("
                    SELECT va.name, vav.value, vav.swatch_hex_or_image
                    FROM `product_variation_attribute_map` pvam
                    JOIN `variation_attribute_values` vav ON vav.id = pvam.attribute_value_id
                    JOIN `variation_attributes` va ON va.id = vav.attribute_id
                    WHERE pvam.variation_id = ?
                ");
                $mapStmt->execute([(int)$v['id']]);
                $attrs = $mapStmt->fetchAll(PDO::FETCH_ASSOC);

                $colorName = 'Default Color';
                $sizeLabel = 'Standard';
                $swatchHex = null;

                foreach ($attrs as $a) {
                    if (strtolower($a['name']) === 'color') {
                        $colorName = $a['value'];
                        $swatchHex = $a['swatch_hex_or_image'];
                    } else {
                        $sizeLabel = $a['value'];
                    }
                }

                if (!isset($colorMap[$colorName])) {
                    $colorIdx++;
                    if (!$isDryRun) {
                        $colorInsStmt->execute([
                            ':product_id'    => $pid,
                            ':color_name'    => $colorName,
                            ':swatch'        => $swatchHex,
                            ':sku'           => null,
                            ':price'         => null,
                            ':stock_qty'     => null,
                            ':display_order' => $colorIdx,
                        ]);
                        $colorId = (int)$db->lastInsertId();
                    } else {
                        $colorId = $colorIdx;
                    }
                    $colorMap[$colorName] = $colorId;
                    $migratedColors++;
                }

                $cId = $colorMap[$colorName];
                $sku = !empty($v['sku']) ? $v['sku'] : $v['variant_code'];
                $price = (float)($v['one_piece_price'] > 0 ? $v['one_piece_price'] : $v['wholesale_price']);
                $stock = (int)$v['stock_quantity'];

                if (!$isDryRun) {
                    $sizeInsStmt->execute([
                        ':color_id'      => $cId,
                        ':size_label'    => $sizeLabel,
                        ':sku'           => $sku,
                        ':price'         => $price,
                        ':stock_qty'     => $stock,
                        ':gst_percent'   => $v['gst_percent'] ?? null,
                        ':hsn_code'      => $v['hsn_code'] ?? null,
                        ':display_order' => 1,
                    ]);
                }
                $migratedSizes++;
            }
            if (!$isDryRun) {
                $modeUpdStmt->execute(['double', $pid]);
            }
        }
    }

    if (!$isDryRun && $db->inTransaction()) {
        $db->commit();
    }

    logMsg("Migration Summary", 'head');
    logMsg("Migrated " . $migratedColors . " color records", 'ok');
    logMsg("Migrated " . $migratedSizes . " size records", 'ok');

} catch (\Throwable $e) {
    if (!$isDryRun && $db->inTransaction()) {
        $db->rollBack();
    }
    logMsg("Migration failed: " . $e->getMessage(), 'err');
    logMsg($e->getTraceAsString());
    exit(1);
}

if ($isHtml) {
    echo "</body></html>";
}
