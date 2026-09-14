<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "1. Truncating fake items from featured_categories and featured_subcategories...\n";
$db->exec("SET FOREIGN_KEY_CHECKS = 0;");
$db->exec("TRUNCATE TABLE featured_subcategories;");
$db->exec("TRUNCATE TABLE featured_categories;");
$db->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "2. Populating Section 1 (featured_categories) with real top-level categories from Admin Panel...\n";
$mainCats = $db->query("
    SELECT id, name, slug, COALESCE(NULLIF(image, ''), NULLIF(custom_icon, '')) AS image, sort_order 
    FROM categories 
    WHERE (parent_id IS NULL OR parent_id = 0 OR parent_id = '') AND (status = 'active' OR status = 'enabled') 
    ORDER BY sort_order ASC, name ASC
")->fetchAll();

$insertedCats = 0;
foreach ($mainCats as $cat) {
    $stmt = $db->prepare("
        INSERT INTO featured_categories (id, name, slug, image, link_url, sort_order, is_active)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    $linkUrl = '/category/' . $cat['slug'];
    $stmt->execute([
        $cat['id'],
        $cat['name'],
        $cat['slug'],
        $cat['image'] ?: '',
        $linkUrl,
        $cat['sort_order'] ?? 0
    ]);
    $insertedCats++;
    echo "  Added Section 1 Tile: {$cat['name']} ({$linkUrl})\n";
}

echo "3. Populating Section 2 (featured_subcategories) with real subcategories from Admin Panel...\n";
// Fetch from subcategories table
$subcats1 = $db->query("
    SELECT s.id, s.category_id, s.name, s.slug, COALESCE(NULLIF(s.image, ''), NULLIF(c.image, '')) AS image, s.sort_order, c.slug AS parent_slug
    FROM subcategories s
    LEFT JOIN categories c ON (s.category_id = c.id OR s.category_id = c.slug)
    WHERE (s.status = 'active' OR s.status = 'enabled')
    ORDER BY s.sort_order ASC, s.name ASC
")->fetchAll();

// Fetch from child categories table
$subcats2 = $db->query("
    SELECT c.id, c.parent_id AS category_id, c.name, c.slug, COALESCE(NULLIF(c.image, ''), NULLIF(c.custom_icon, '')) AS image, c.sort_order, p.slug AS parent_slug
    FROM categories c
    LEFT JOIN categories p ON c.parent_id = p.id
    WHERE c.parent_id > 0 AND (c.status = 'active' OR c.status = 'enabled')
    ORDER BY c.sort_order ASC, c.name ASC
")->fetchAll();

$allSubcats = array_merge($subcats1, $subcats2);
$insertedSubs = 0;
$seenSlugs = [];

foreach ($allSubcats as $sub) {
    if (isset($seenSlugs[$sub['slug']])) continue;
    $seenSlugs[$sub['slug']] = true;

    $parentCatId = (int)$sub['category_id'] ?: 1;
    $parentSlug = !empty($sub['parent_slug']) ? $sub['parent_slug'] : 'jewellery';
    $linkUrl = '/category/' . $parentSlug . '/' . $sub['slug'];

    $stmt = $db->prepare("
        INSERT INTO featured_subcategories (featured_category_id, name, slug, image, link_url, sort_order, is_active)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([
        $parentCatId,
        $sub['name'],
        $sub['slug'],
        $sub['image'] ?: '',
        $linkUrl,
        $sub['sort_order'] ?? 0
    ]);
    $insertedSubs++;
    echo "  Added Section 2 Icon: {$sub['name']} ({$linkUrl})\n";
}

echo "Sync complete! Added {$insertedCats} Main Categories and {$insertedSubs} Subcategories from Admin Panel!\n";
