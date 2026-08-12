<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/app/Helpers/Functions.php';
$db = App\Core\Database::getInstance();

echo "=== product_images columns ===\n";
$stmt = $db->query('DESCRIBE product_images');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['Field'] . ' | ' . $row['Type'] . "\n";
}

echo "\n=== variation tables ===\n";
$stmt2 = $db->query("SHOW TABLES LIKE 'product_variation%'");
foreach ($stmt2->fetchAll(PDO::FETCH_COLUMN) as $t) {
    echo $t . "\n";
}

echo "\n=== products table (slug/oem_price columns) ===\n";
$stmt3 = $db->query("SHOW COLUMNS FROM products LIKE 'slug'");
$r = $stmt3->fetch(PDO::FETCH_ASSOC);
echo $r ? "slug: " . $r['Type'] . "\n" : "no slug column\n";
