<?php
/**
 * Apply PDP performance indexes, skipping ones that already exist.
 * Usage: php database/migrations/run_pdp_performance_indexes.php
 */
define('ROOT_PATH', dirname(__DIR__, 2));
require ROOT_PATH . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_PATH);
require ROOT_PATH . '/app/Helpers/Functions.php';
spl_autoload_register(function ($c) {
    if (strncmp('App\\', $c, 4) === 0) {
        $f = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($c, 4)) . '.php';
        if (file_exists($f)) require $f;
    }
});

$db = \App\Core\Database::getInstance();

$indexes = [
    ['products', 'idx_prod_status_subcat_feat', '(`status`, `subcategory_id`, `is_featured`, `id`)'],
    ['products', 'idx_prod_status_cat_feat', '(`status`, `category_id`, `is_featured`, `id`)'],
    ['products', 'idx_prod_slug_status', '(`slug`, `status`)'],
    ['product_variants', 'idx_pv_product_active_sort', '(`product_id`, `is_active`, `sort_order`, `id`)'],
    ['product_images', 'idx_pi_product_primary_sort', '(`product_id`, `is_primary`, `sort_order`, `id`)'],
    ['product_specifications', 'idx_ps_product_sort', '(`product_id`, `sort_order`, `id`)'],
    ['wishlist', 'idx_wishlist_session', '(`session_id`)'],
    ['wishlist', 'idx_wishlist_session_product', '(`session_id`, `product_id`)'],
    ['cart_items', 'idx_cart_session_product', '(`session_id`, `product_id`)'],
    ['settings', 'idx_settings_key', '(`setting_key`)'],
    ['nav_links', 'idx_nav_parent_active_sort', '(`parent_id`, `is_active`, `sort_order`)'],
];

$check = $db->prepare("
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?
    LIMIT 1
");

foreach ($indexes as [$table, $name, $cols]) {
    try {
        $tableExists = $db->query("SHOW TABLES LIKE " . $db->quote($table))->fetchColumn();
        if (!$tableExists) {
            echo "SKIP {$table}.{$name} (table missing)\n";
            continue;
        }
        $check->execute([$table, $name]);
        if ($check->fetchColumn()) {
            echo "EXISTS {$table}.{$name}\n";
            continue;
        }
        $db->exec("ALTER TABLE `{$table}` ADD INDEX `{$name}` {$cols}");
        echo "ADDED {$table}.{$name}\n";
    } catch (\Throwable $e) {
        echo "ERROR {$table}.{$name}: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
