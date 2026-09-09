<?php
/**
 * migrate_split_columns.php
 *
 * Cleans up existing `product_specifications` rows after the bulk-import
 * template was restructured (Sep 2026):
 *
 *  1. Renames old combined spec key  "Processing Technology / Technique"
 *     → "Processing Technology"  (value kept as-is; technique/treatment left
 *       blank for manual re-entry).
 *
 *  2. Renames old combined spec key  "Kind / Product Type"
 *     → "Kind"  (value kept as-is; product type / jewellery category left
 *       blank for manual re-entry).
 *
 *  3. Consolidates the duplicate "Material" spec:
 *     a) If a product has "Material/Metal Type" AND "Material":
 *          – Merges values  (comma-separated, deduped) into "Material/Metal Type".
 *          – Deletes the "Material" row.
 *     b) If a product has only "Material" (no "Material/Metal Type"):
 *          – Renames key to "Material/Metal Type".
 *
 *  4. Outputs a full report to stdout and saves a log file alongside this
 *     script for audit purposes.
 *
 * Safe to run multiple times (idempotent after first run).
 * Run from the project root:  php database/migrate_split_columns.php
 */

require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$db  = Database::getInstance();
$log = [];

$log[] = "=== Bulk Import Column Split Migration ===";
$log[] = "Started: " . date('Y-m-d H:i:s');
$log[] = "";

// ─────────────────────────────────────────────────────────────────────────────
// Step 1 — Rename "Processing Technology / Technique" → "Processing Technology"
// ─────────────────────────────────────────────────────────────────────────────
$log[] = "--- Step 1: Rename Processing Technology combined spec key ---";

$stmt = $db->prepare(
    "SELECT id, product_id, spec_value FROM product_specifications
     WHERE TRIM(spec_key) IN (
         'Processing Technology / Technique',
         'Processing Technology / Processing Technique / Treatment Process'
     )"
);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count1 = 0;
foreach ($rows as $row) {
    // Only rename if "Processing Technology" does not already exist for this product
    $chk = $db->prepare(
        "SELECT id FROM product_specifications
         WHERE product_id = ? AND TRIM(spec_key) = 'Processing Technology'"
    );
    $chk->execute([$row['product_id']]);
    if (!$chk->fetch()) {
        $upd = $db->prepare(
            "UPDATE product_specifications SET spec_key = 'Processing Technology' WHERE id = ?"
        );
        $upd->execute([$row['id']]);
        $log[] = "  [RENAMED] product_id={$row['product_id']}  old key -> 'Processing Technology'  value='{$row['spec_value']}'";
        $count1++;
    } else {
        // Already exists — just delete the old combined row to avoid duplicate
        $del = $db->prepare("DELETE FROM product_specifications WHERE id = ?");
        $del->execute([$row['id']]);
        $log[] = "  [DELETED-DUP] product_id={$row['product_id']}  removed old combined processing key (new key already present)";
        $count1++;
    }
}
$log[] = "  Total affected: {$count1}";
$log[] = "";

// ─────────────────────────────────────────────────────────────────────────────
// Step 2 — Rename "Kind / Product Type" → "Kind"
// ─────────────────────────────────────────────────────────────────────────────
$log[] = "--- Step 2: Rename Kind/Product Type combined spec key ---";

$stmt = $db->prepare(
    "SELECT id, product_id, spec_value FROM product_specifications
     WHERE TRIM(spec_key) IN (
         'Kind / Product Type',
         'Kind/Product Type',
         'Kind/Product Type/Jewellery Type'
     )"
);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count2 = 0;
foreach ($rows as $row) {
    $chk = $db->prepare(
        "SELECT id FROM product_specifications
         WHERE product_id = ? AND TRIM(spec_key) = 'Kind'"
    );
    $chk->execute([$row['product_id']]);
    if (!$chk->fetch()) {
        $upd = $db->prepare(
            "UPDATE product_specifications SET spec_key = 'Kind' WHERE id = ?"
        );
        $upd->execute([$row['id']]);
        $log[] = "  [RENAMED] product_id={$row['product_id']}  old key -> 'Kind'  value='{$row['spec_value']}'";
        $count2++;
    } else {
        $del = $db->prepare("DELETE FROM product_specifications WHERE id = ?");
        $del->execute([$row['id']]);
        $log[] = "  [DELETED-DUP] product_id={$row['product_id']}  removed old combined kind key (new key already present)";
        $count2++;
    }
}
$log[] = "  Total affected: {$count2}";
$log[] = "";

// ─────────────────────────────────────────────────────────────────────────────
// Step 3 — Consolidate duplicate "Material" spec into "Material/Metal Type"
// ─────────────────────────────────────────────────────────────────────────────
$log[] = "--- Step 3: Consolidate 'Material' spec into 'Material/Metal Type' ---";

$stmtMat = $db->query(
    "SELECT id, product_id, spec_value FROM product_specifications
     WHERE TRIM(spec_key) = 'Material'"
);
$materialRows = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

$count3a = 0; // merged
$count3b = 0; // renamed

foreach ($materialRows as $matRow) {
    $pid       = $matRow['product_id'];
    $matValue  = trim($matRow['spec_value']);

    // Check if Material/Metal Type already exists for this product
    $chkMmt = $db->prepare(
        "SELECT id, spec_value FROM product_specifications
         WHERE product_id = ? AND TRIM(spec_key) = 'Material/Metal Type'"
    );
    $chkMmt->execute([$pid]);
    $mmtRow = $chkMmt->fetch(PDO::FETCH_ASSOC);

    if ($mmtRow) {
        // Case a: Both exist — merge values (comma-separated, deduped)
        $existingValue = trim($mmtRow['spec_value']);

        if (strtolower($existingValue) !== strtolower($matValue) && $matValue !== '') {
            // Merge: split both by comma, combine, deduplicate (case-insensitive)
            $parts = array_merge(
                array_map('trim', explode(',', $existingValue)),
                array_map('trim', explode(',', $matValue))
            );
            $seen    = [];
            $deduped = [];
            foreach ($parts as $part) {
                $key = strtolower($part);
                if ($part !== '' && !isset($seen[$key])) {
                    $seen[$key] = true;
                    $deduped[]  = $part;
                }
            }
            $mergedValue = implode(', ', $deduped);

            $upd = $db->prepare(
                "UPDATE product_specifications SET spec_value = ? WHERE id = ?"
            );
            $upd->execute([$mergedValue, $mmtRow['id']]);
            $log[] = "  [MERGED] product_id={$pid}  'Material/Metal Type' updated: '{$existingValue}' + '{$matValue}' -> '{$mergedValue}'";
        } else {
            $log[] = "  [SKIPPED-SAME] product_id={$pid}  'Material' value identical to 'Material/Metal Type', just deleting Material row.";
        }

        // Delete the old Material row
        $del = $db->prepare("DELETE FROM product_specifications WHERE id = ?");
        $del->execute([$matRow['id']]);
        $count3a++;
    } else {
        // Case b: Only "Material" exists — rename key to "Material/Metal Type"
        $upd = $db->prepare(
            "UPDATE product_specifications SET spec_key = 'Material/Metal Type' WHERE id = ?"
        );
        $upd->execute([$matRow['id']]);
        $log[] = "  [RENAMED] product_id={$pid}  'Material' -> 'Material/Metal Type'  value='{$matValue}'";
        $count3b++;
    }
}
$log[] = "  Merged (had both): {$count3a}";
$log[] = "  Renamed (only Material): {$count3b}";
$log[] = "";

// ─────────────────────────────────────────────────────────────────────────────
// Summary
// ─────────────────────────────────────────────────────────────────────────────
$totalAffected = $count1 + $count2 + $count3a + $count3b;
$log[] = "=== Migration Complete ===";
$log[] = "Finished: " . date('Y-m-d H:i:s');
$log[] = "Processing Technology rows renamed/cleaned: {$count1}";
$log[] = "Kind/Product Type rows renamed/cleaned:     {$count2}";
$log[] = "Material rows merged into Material/Metal Type: {$count3a}";
$log[] = "Material rows renamed to Material/Metal Type:  {$count3b}";
$log[] = "Total spec rows affected: {$totalAffected}";
$log[] = "";
$log[] = "NOTE: Existing combined values (e.g. 'Vacuum Electroplating & Hand Weaving')";
$log[] = "      have been preserved in the 'Processing Technology' spec key.";
$log[] = "      The new 'Processing Technique' and 'Treatment Process' spec keys are";
$log[] = "      empty for these products — manual re-entry or a new bulk import is needed.";
$log[] = "";
$log[] = "NOTE: Existing combined values (e.g. \"Men's Leather Bracelet\")";
$log[] = "      have been preserved in the 'Kind' spec key.";
$log[] = "      The new 'Product Type' and 'Jewellery Category' spec keys are empty";
$log[] = "      for these products — manual re-entry or a new bulk import is needed.";

$output = implode("\n", $log);
echo $output . "\n";

// Save log file
$logPath = __DIR__ . '/migrate_split_columns_' . date('Ymd_His') . '.log';
file_put_contents($logPath, $output);
echo "\nLog saved to: {$logPath}\n";
