<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    echo "=== TABLES ===\n";
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    print_r(array_values(array_filter($tables, fn($t) => strpos($t, 'filter') !== false || strpos($t, 'product') !== false || strpos($t, 'spec') !== false || strpos($t, 'attr') !== false)));

    echo "\n=== FILTER ATTRIBUTES ===\n";
    $attrs = $db->query("SELECT * FROM filter_attributes")->fetchAll(PDO::FETCH_ASSOC);
    print_r($attrs);

    echo "\n=== FILTER ATTRIBUTE CATEGORIES ===\n";
    $fac = $db->query("SELECT * FROM filter_attribute_categories")->fetchAll(PDO::FETCH_ASSOC);
    print_r($fac);

    echo "\n=== FILTER ATTRIBUTE OPTIONS COUNT ===\n";
    $faoCount = $db->query("SELECT attribute_id, COUNT(*) as cnt FROM filter_attribute_options GROUP BY attribute_id")->fetchAll(PDO::FETCH_ASSOC);
    print_r($faoCount);

    echo "\n=== COUNT OF PRODUCT FILTER ATTRIBUTE VALUES ===\n";
    $pfavCount = $db->query("SELECT COUNT(*) FROM product_filter_attribute_values")->fetchColumn();
    echo "Total product_filter_attribute_values: $pfavCount\n";

    echo "\n=== SAMPLE PRODUCTS WITH ATTRIBUTES OR SPECIFICATIONS OR COLUMNS ===\n";
    $cols = $db->query("DESCRIBE products")->fetchAll(PDO::FETCH_COLUMN);
    print_r(array_values(array_filter($cols, fn($c) => strpos($c, 'attr') !== false || strpos($c, 'spec') !== false || strpos($c, 'json') !== false || strpos($c, 'detail') !== false)));

    echo "\n=== SAMPLE PRODUCT ROW COLUMNS ===\n";
    $sampleProduct = $db->query("SELECT id, name, category_id, subcategory_id FROM products LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    print_r($sampleProduct);

    echo "\n=== PRODUCT ATTRIBUTES TABLE OR RELATED TABLES ===\n";
    foreach ($tables as $t) {
        if (strpos($t, 'attribute') !== false || strpos($t, 'spec') !== false || strpos($t, 'filter') !== false) {
            echo "--- Table: $t ---\n";
            $cnt = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            echo "Row count: $cnt\n";
            $desc = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($desc as $d) {
                echo "  {$d['Field']} ({$d['Type']})\n";
            }
        }
    }

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
