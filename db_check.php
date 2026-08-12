<?php
define('ROOT_PATH', __DIR__);
require_once 'config/app.php';
require_once 'app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "=== Merged products check ===\n";
$stmt = $db->query("SELECT id, name, status FROM products WHERE status = 'merged'");
$merged = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Merged count: " . count($merged) . "\n";
foreach ($merged as $m) {
    echo "ID: {$m['id']} - {$m['name']}\n";
}

$updated = $db->exec("UPDATE products SET status = 'active' WHERE status = 'merged'");
echo "Updated {$updated} products from 'merged' back to 'active'.\n";
