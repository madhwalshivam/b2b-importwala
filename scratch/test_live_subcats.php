<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';

use App\Core\Database;

Database::init(require ROOT_PATH . '/config/database.php');
$db = Database::getInstance();

$stmt = $db->query("
    SELECT fc.*, 
           COALESCE(NULLIF(c.image, ''), NULLIF(c.custom_icon, ''), fc.image) AS main_category_image,
           c.id AS real_parent_id
    FROM featured_categories fc
    LEFT JOIN categories c ON (LOWER(fc.slug) = LOWER(c.slug) OR LOWER(fc.name) = LOWER(c.name)) AND c.parent_id IS NULL
    WHERE fc.is_active = 1 
    ORDER BY fc.sort_order ASC, fc.id ASC
");
$categories = $stmt->fetchAll() ?: [];

foreach ($categories as $cat) {
    echo "Category: {$cat['name']} (Real Parent ID: " . ($cat['real_parent_id'] ?? 'NONE') . ")\n";
    if (!empty($cat['real_parent_id'])) {
        $subStmt = $db->prepare("
            SELECT id, name, slug, image, sort_order, status 
            FROM categories 
            WHERE parent_id = ? AND status = 'active' 
            ORDER BY sort_order ASC, name ASC
        ");
        $subStmt->execute([$cat['real_parent_id']]);
        $subs = $subStmt->fetchAll();
        echo "  Found " . count($subs) . " live subcategories in categories table:\n";
        foreach (array_slice($subs, 0, 3) as $s) {
            echo "    - [Sub ID {$s['id']}] {$s['name']} (Img: {$s['image']})\n";
        }
    }
    echo "\n";
}
