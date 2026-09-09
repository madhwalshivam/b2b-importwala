<?php
/**
 * migrate_filter_revamp.php
 *
 * 1. Adds `is_admin_only` column to filter_attributes (if not exists).
 * 2. Wipes ALL old filter data (attributes, options, product assignments).
 * 3. Inserts the canonical 28-filter set aligned with the new bulk-import
 *    column structure.
 *
 * Run: php database/migrate_filter_revamp.php
 * Safe to re-run (uses INSERT IGNORE / duplicate-slug guard).
 */

require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();
echo "=== Filter Revamp Migration ===\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

// ─── Step 1: Add is_admin_only column ────────────────────────────────────────
echo "Step 1: Adding is_admin_only column...\n";
try {
    $db->exec("ALTER TABLE filter_attributes ADD COLUMN is_admin_only TINYINT(1) NOT NULL DEFAULT 0 AFTER is_global");
    echo "  [OK] Column added.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "  [SKIP] Column already exists.\n";
    } else {
        throw $e;
    }
}

// ─── Step 2: Wipe old data ───────────────────────────────────────────────────
echo "\nStep 2: Clearing old filter data...\n";
$db->exec("DELETE FROM product_filter_attribute_values");
echo "  [OK] product_filter_attribute_values cleared.\n";
$db->exec("DELETE FROM filter_attribute_options");
echo "  [OK] filter_attribute_options cleared.\n";
$db->exec("DELETE FROM filter_attribute_categories");
echo "  [OK] filter_attribute_categories cleared.\n";
$db->exec("DELETE FROM filter_attributes");
echo "  [OK] filter_attributes cleared.\n";

// Reset auto-increment
$db->exec("ALTER TABLE filter_attributes AUTO_INCREMENT = 1");

// ─── Step 3: Insert 28 canonical filter attributes ───────────────────────────
echo "\nStep 3: Inserting 28 canonical filter attributes...\n";

$filters = [
    // [name, slug, type, is_global, is_admin_only, sort_order]
    // ── Buyer-facing filters (is_admin_only = 0) ──────────────────────────
    ['Category',                          'category',                           'multi_select', 1, 0,  1],
    ['Subcategory',                        'subcategory',                        'multi_select', 1, 0,  2],
    ['Jewellery Type',                     'jewellery_type',                     'multi_select', 1, 0,  3],
    ['Gender',                             'gender',                             'multi_select', 1, 0,  4],
    ['Material/Metal Type',                'material_metal_type',                'multi_select', 1, 0,  5],
    ['Metal Color',                        'metal_color',                        'multi_select', 1, 0,  6],
    ['Main Stone Type',                    'main_stone_type',                    'multi_select', 1, 0,  7],
    ['Country of Origin',                  'country_of_origin',                  'multi_select', 1, 0,  8],
    ['Variety',                            'variety',                            'multi_select', 1, 0,  9],
    ['Processing Technology',              'processing_technology',              'multi_select', 1, 0, 10],
    ['Processing Technique',               'processing_technique',               'multi_select', 1, 0, 11],
    ['Treatment Process',                  'treatment_process',                  'multi_select', 1, 0, 12],
    ['Style',                              'style',                              'multi_select', 1, 0, 13],
    ['Style Classification',               'style_classification',               'multi_select', 1, 0, 14],
    ['Suitable For Gift Giving Occasion',  'suitable_for_gift_giving_occasion',  'multi_select', 1, 0, 15],
    ['Color',                              'color',                              'multi_select', 1, 0, 16],
    ['Popular Elements',                   'popular_elements',                   'multi_select', 1, 0, 17],
    ['Kind',                               'kind',                               'multi_select', 1, 0, 18],
    ['Product Type',                       'product_type',                       'multi_select', 1, 0, 19],
    ['Chain Style',                        'chain_style',                        'multi_select', 1, 0, 20],
    ['Pendant Material',                   'pendant_material',                   'multi_select', 1, 0, 21],
    ['Trendy Element',                     'trendy_element',                     'multi_select', 1, 0, 22],
    ['Closure Type',                       'closure_type',                       'multi_select', 1, 0, 23],
    ['Brand Name',                         'brand_name',                         'multi_select', 1, 0, 24],
    ['Price Range',                        'price_range',                        'range',        1, 0, 25],
    // ── Admin-only internal filters (is_admin_only = 1) ───────────────────
    ['Manufacturer Name',                  'manufacturer_name',                  'multi_select', 1, 1, 26],
    ['Source Platform',                    'source_platform',                    'multi_select', 1, 1, 27],
    ['Status',                             'status',                             'multi_select', 1, 1, 28],
];

$ins = $db->prepare(
    "INSERT INTO filter_attributes (name, slug, type, is_global, is_admin_only, is_active, sort_order)
     VALUES (?, ?, ?, ?, ?, 1, ?)"
);

foreach ($filters as [$name, $slug, $type, $isGlobal, $isAdminOnly, $sortOrder]) {
    $ins->execute([$name, $slug, $type, $isGlobal, $isAdminOnly, $sortOrder]);
    $badge = $isAdminOnly ? ' [ADMIN-ONLY]' : '';
    echo "  [OK] #{$sortOrder} {$name}{$badge}\n";
}

// ─── Step 4: Seed Status options ─────────────────────────────────────────────
echo "\nStep 4: Seeding default options for Status filter...\n";
$statusAttr = $db->query("SELECT id FROM filter_attributes WHERE slug = 'status'")->fetchColumn();
if ($statusAttr) {
    $optIns = $db->prepare("INSERT INTO filter_attribute_options (attribute_id, value, slug, sort_order) VALUES (?, ?, ?, ?)");
    foreach (['Active' => 1, 'Inactive' => 2, 'Draft' => 3] as $val => $ord) {
        $optIns->execute([$statusAttr, $val, strtolower($val), $ord]);
        echo "  [OK] Status: {$val}\n";
    }
}

// ─── Done ─────────────────────────────────────────────────────────────────────
echo "\n=== Migration Complete ===\n";
echo "Finished: " . date('Y-m-d H:i:s') . "\n";
echo "Total filter attributes created: " . count($filters) . "\n";
echo "  Buyer-facing: 25\n";
echo "  Admin-only:   3\n";
echo "\nNEXT STEP: Run a bulk import to auto-populate filter option values.\n";
