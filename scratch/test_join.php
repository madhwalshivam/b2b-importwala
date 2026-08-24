<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';

use App\Core\Database;

Database::init(require ROOT_PATH . '/config/database.php');
$db = Database::getInstance();

$sql = "
    SELECT fc.*, 
           COALESCE(NULLIF(c.image, ''), NULLIF(c.custom_icon, ''), fc.image) AS main_category_image
    FROM featured_categories fc
    LEFT JOIN categories c ON (LOWER(fc.slug) = LOWER(c.slug) OR LOWER(fc.name) = LOWER(c.name)) AND c.parent_id IS NULL
    WHERE fc.is_active = 1 
    ORDER BY fc.sort_order ASC, fc.id ASC
";
$stmt = $db->query($sql);
$rows = $stmt->fetchAll();

foreach ($rows as $r) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Main Category Image: {$r['main_category_image']}\n";
}
