<?php
$env = parse_ini_file(__DIR__ . '/../.env');
$db = new PDO(
    'mysql:host='.$env['DB_HOST'].';dbname='.$env['DB_DATABASE'].';charset=utf8mb4',
    $env['DB_USERNAME'],
    $env['DB_PASSWORD']
);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = ['products', 'product_variants', 'product_colors', 'product_color_sizes'];
foreach ($tables as $t) {
    $r = $db->query("SHOW CREATE TABLE `$t`");
    $row = $r->fetch(PDO::FETCH_NUM);
    echo "=== $t ===\n" . $row[1] . "\n\n";
}

// Also show products columns
$r2 = $db->query("DESCRIBE products");
echo "=== products columns ===\n";
foreach ($r2->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . ' ' . $col['Type'] . ' ' . $col['Null'] . ' ' . $col['Key'] . "\n";
}
