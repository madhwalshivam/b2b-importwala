<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();

echo "=== UNIQUE SPEC_KEYS IN product_specifications ===\n";
$specs = $db->query("SELECT spec_key, COUNT(*) as cnt, GROUP_CONCAT(DISTINCT spec_value SEPARATOR ' || ') as sample_values FROM product_specifications GROUP BY spec_key")->fetchAll(PDO::FETCH_ASSOC);
foreach ($specs as $s) {
    echo "Key: {$s['spec_key']} (Count: {$s['cnt']})\n";
    echo "  Sample Values: {$s['sample_values']}\n\n";
}

echo "\n=== ALL FILTER ATTRIBUTES ===\n";
$fa = $db->query("SELECT id, name, slug, type FROM filter_attributes")->fetchAll(PDO::FETCH_ASSOC);
foreach ($fa as $f) {
    echo "ID: {$f['id']} | Name: {$f['name']} | Slug: {$f['slug']}\n";
}
