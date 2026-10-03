<?php
$env = parse_ini_file(__DIR__ . '/../.env');
$db = new PDO('mysql:host='.$env['DB_HOST'].';dbname='.$env['DB_DATABASE'], $env['DB_USERNAME'], $env['DB_PASSWORD']);
$stmt = $db->query("
    SELECT LOWER(TRIM(name)) as clean_name, COUNT(*) as cnt, GROUP_CONCAT(id ORDER BY id ASC) as ids, GROUP_CONCAT(sku ORDER BY id ASC) as skus 
    FROM products 
    GROUP BY LOWER(TRIM(name)) 
    HAVING COUNT(*) > 1
");
$dups = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($dups) . " duplicate product groups:\n\n";
foreach ($dups as $d) {
    echo "Name: {$d['clean_name']}\n";
    echo "Count: {$d['cnt']}\n";
    echo "IDs: {$d['ids']}\n";
    echo "SKUs: {$d['skus']}\n\n";
}
