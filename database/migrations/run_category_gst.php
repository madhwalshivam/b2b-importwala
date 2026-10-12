<?php

/**
 * Category-wise GST migration. Safe to run more than once.
 * Usage: php database/migrations/run_category_gst.php
 */
define('ROOT_PATH', dirname(__DIR__, 2));
require ROOT_PATH . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_PATH);
require ROOT_PATH . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

$db = \App\Core\Database::getInstance();

function gst_column_exists(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$table, $column]);
    return ((int) $stmt->fetchColumn()) > 0;
}

function gst_add_column(PDO $db, string $table, string $column, string $definition): void
{
    if (gst_column_exists($db, $table, $column)) {
        echo "Column {$table}.{$column} already exists.\n";
        return;
    }
    $db->exec("ALTER TABLE `{$table}` ADD COLUMN {$definition}");
    echo "Added {$table}.{$column}.\n";
}

echo "Applying category GST migration...\n";

gst_add_column($db, 'categories', 'hsn_code', '`hsn_code` VARCHAR(8) NULL DEFAULT NULL');
gst_add_column($db, 'categories', 'gst_rate', '`gst_rate` DECIMAL(5,2) NULL DEFAULT 18.00');
gst_add_column($db, 'categories', 'is_gst_active', '`is_gst_active` TINYINT(1) NOT NULL DEFAULT 1');

gst_add_column($db, 'subcategories', 'hsn_code', '`hsn_code` VARCHAR(8) NULL DEFAULT NULL');
gst_add_column($db, 'subcategories', 'gst_rate', '`gst_rate` DECIMAL(5,2) NULL DEFAULT NULL');
gst_add_column($db, 'subcategories', 'is_gst_active', '`is_gst_active` TINYINT(1) NOT NULL DEFAULT 1');

gst_add_column($db, 'products', 'gst_rate', '`gst_rate` DECIMAL(5,2) NULL DEFAULT NULL AFTER `hsn_code`');

gst_add_column($db, 'order_items', 'hsn_code', '`hsn_code` VARCHAR(8) NULL DEFAULT NULL');
gst_add_column($db, 'order_items', 'gst_rate', '`gst_rate` DECIMAL(5,2) NULL DEFAULT NULL');
gst_add_column($db, 'order_items', 'taxable_amount', '`taxable_amount` DECIMAL(12,2) NULL DEFAULT NULL');
gst_add_column($db, 'order_items', 'cgst_amount', '`cgst_amount` DECIMAL(12,2) NULL DEFAULT NULL');
gst_add_column($db, 'order_items', 'sgst_amount', '`sgst_amount` DECIMAL(12,2) NULL DEFAULT NULL');
gst_add_column($db, 'order_items', 'igst_amount', '`igst_amount` DECIMAL(12,2) NULL DEFAULT NULL');
gst_add_column($db, 'order_items', 'total_gst_amount', '`total_gst_amount` DECIMAL(12,2) NULL DEFAULT NULL');

gst_add_column($db, 'orders', 'total_taxable_amount', '`total_taxable_amount` DECIMAL(12,2) NULL DEFAULT NULL');
gst_add_column($db, 'orders', 'total_cgst', '`total_cgst` DECIMAL(12,2) NULL DEFAULT NULL');
gst_add_column($db, 'orders', 'total_sgst', '`total_sgst` DECIMAL(12,2) NULL DEFAULT NULL');
gst_add_column($db, 'orders', 'total_igst', '`total_igst` DECIMAL(12,2) NULL DEFAULT NULL');
gst_add_column($db, 'orders', 'total_gst', '`total_gst` DECIMAL(12,2) NULL DEFAULT NULL');
gst_add_column($db, 'orders', 'shipping_state', '`shipping_state` VARCHAR(80) NULL DEFAULT NULL');

$db->exec("CREATE TABLE IF NOT EXISTS `gst_settings` (
  `id` TINYINT UNSIGNED NOT NULL,
  `seller_state` VARCHAR(80) NOT NULL DEFAULT 'Delhi',
  `seller_gstin` VARCHAR(15) NULL DEFAULT NULL,
  `default_gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 18.00,
  `price_mode` ENUM('exclusive','inclusive') NOT NULL DEFAULT 'exclusive',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$db->exec("CREATE TABLE IF NOT EXISTS `gst_rate_audit_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type` VARCHAR(20) NOT NULL,
  `entity_id` INT UNSIGNED NOT NULL,
  `old_hsn` VARCHAR(30) NULL DEFAULT NULL,
  `new_hsn` VARCHAR(30) NULL DEFAULT NULL,
  `old_rate` DECIMAL(5,2) NULL DEFAULT NULL,
  `new_rate` DECIMAL(5,2) NULL DEFAULT NULL,
  `changed_by` INT UNSIGNED NULL DEFAULT NULL,
  `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gst_audit_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$updated = $db->exec("UPDATE categories SET gst_rate = 18.00 WHERE gst_rate IS NULL");
echo "Backfilled category GST rates: {$updated} row(s) set to 18.00 where empty.\n";

$gstin = '07FLOPR6641L1Z8';
$db->prepare(
    "INSERT INTO gst_settings (id, seller_state, seller_gstin, default_gst_rate, price_mode)
     VALUES (1, 'Delhi', ?, 18.00, 'exclusive')
     ON DUPLICATE KEY UPDATE id = id"
)->execute([$gstin]);
echo "GST settings row is present (seller state Delhi, exclusive prices, 18% default).\n";

$permissions = [
    ['gst', 'view', 'View GST settings', 'gst.view'],
    ['gst', 'edit', 'Edit GST settings', 'gst.edit'],
];
$permCheck = $db->query("SHOW TABLES LIKE 'permissions'")->fetch();
if ($permCheck) {
    foreach ($permissions as $perm) {
        $stmt = $db->prepare('SELECT id FROM permissions WHERE key_code = ?');
        $stmt->execute([$perm[3]]);
        $existing = $stmt->fetchColumn();
        if (!$existing) {
            $db->prepare('INSERT INTO permissions (module, action, name, key_code) VALUES (?, ?, ?, ?)')
                ->execute($perm);
            $existing = (int) $db->lastInsertId();
            echo "Added permission {$perm[3]}.\n";
        }
        $db->prepare('INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (1, ?)')->execute([(int) $existing]);
    }
}

echo "Category GST migration finished.\n";
