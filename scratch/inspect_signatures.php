<?php
require_once __DIR__ . '/../public/index.php';

use App\Core\Database;

$db = Database::getInstance();
$stmt = $db->query("
    SELECT s.product_id, s.image_path, s.dhash, s.color_sig, p.name 
    FROM product_visual_signatures s
    JOIN products p ON s.product_id = p.id
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total indexed signatures: " . count($rows) . "\n\n";
foreach ($rows as $r) {
    echo "ID: {$r['product_id']} | Name: {$r['name']}\n";
    echo "  Image: {$r['image_path']}\n";
    echo "  dHash: {$r['dhash']}\n";
    $color = json_decode($r['color_sig'], true);
    echo "  Color Sig sample (first 6 values): " . implode(', ', array_slice($color ?: [], 0, 6)) . "\n";
    echo "---------------------------------------------------------\n";
}
