<?php
$env = parse_ini_file(__DIR__ . '/../.env');
$db = new PDO(
    'mysql:host='.$env['DB_HOST'].';dbname='.$env['DB_DATABASE'].';charset=utf8mb4',
    $env['DB_USERNAME'],
    $env['DB_PASSWORD']
);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check for orphan variants
$r = $db->query("SELECT COUNT(*) FROM product_variants pv WHERE NOT EXISTS (SELECT 1 FROM products p WHERE p.id = pv.product_id)");
echo "Orphan product_variants: " . $r->fetchColumn() . "\n";

// Check product_colors orphans
$r2 = $db->query("SELECT COUNT(*) FROM product_colors pc WHERE NOT EXISTS (SELECT 1 FROM products p WHERE p.id = pc.product_id)");
echo "Orphan product_colors: " . $r2->fetchColumn() . "\n";

// Check product_color_sizes orphans
$r3 = $db->query("SELECT COUNT(*) FROM product_color_sizes pcs WHERE NOT EXISTS (SELECT 1 FROM product_colors pc WHERE pc.id = pcs.color_id)");
echo "Orphan product_color_sizes: " . $r3->fetchColumn() . "\n";

// Check product_variants unique key
$r4 = $db->query("SHOW INDEX FROM product_variants");
echo "\nproduct_variants indexes:\n";
foreach($r4->fetchAll(PDO::FETCH_ASSOC) as $idx) {
    echo "  {$idx['Key_name']} col={$idx['Column_name']} unique=" . ($idx['Non_unique'] ? 'NO' : 'YES') . "\n";
}

// Check product_colors unique key
$r5 = $db->query("SHOW INDEX FROM product_colors");
echo "\nproduct_colors indexes:\n";
foreach($r5->fetchAll(PDO::FETCH_ASSOC) as $idx) {
    echo "  {$idx['Key_name']} col={$idx['Column_name']} unique=" . ($idx['Non_unique'] ? 'NO' : 'YES') . "\n";
}

// Does products table have normalized_title or source_product_id index?
$r6 = $db->query("SHOW INDEX FROM products");
echo "\nproducts indexes:\n";
foreach($r6->fetchAll(PDO::FETCH_ASSOC) as $idx) {
    echo "  {$idx['Key_name']} col={$idx['Column_name']} unique=" . ($idx['Non_unique'] ? 'NO' : 'YES') . "\n";
}
