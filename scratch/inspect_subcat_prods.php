<?php
define('ROOT_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance();

echo "=== Products per subcategory ===\n";
$stmt = $db->query("
    SELECT s.id, s.name, s.slug, COUNT(p.id) as count
    FROM subcategories s
    LEFT JOIN products p ON p.subcategory_id = s.id
    GROUP BY s.id, s.name, s.slug
    ORDER BY s.category_id, s.name
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Slug: {$r['slug']} | Products: {$r['count']}\n";
}
