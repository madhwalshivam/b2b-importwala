<?php
define('ROOT_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance();

echo "=== Merging Duplicate Subcategories ===\n";

// 1. Move any products from ID 13 (Bracelets) to ID 11 (Bracelet)
$db->exec("UPDATE products SET subcategory_id = 11 WHERE subcategory_id = 13");
$db->exec("DELETE FROM subcategories WHERE id = 13");

// Rename ID 11 to 'Bracelets'
$db->exec("UPDATE subcategories SET name = 'Bracelets', slug = 'bracelets' WHERE id = 11");
echo "Merged Bracelets (ID 13) into ID 11 ('Bracelets').\n";

// 2. Move any products from ID 10 (Earring) to ID 8 (Earrings)
$db->exec("UPDATE products SET subcategory_id = 8 WHERE subcategory_id = 10");
$db->exec("DELETE FROM subcategories WHERE id = 10");

// Ensure ID 8 is 'Earrings'
$db->exec("UPDATE subcategories SET name = 'Earrings', slug = 'earrings' WHERE id = 8");
echo "Merged Earring (ID 10) into ID 8 ('Earrings').\n";

// 3. Rename ID 9 (Necklace) to 'Necklaces'
$db->exec("UPDATE subcategories SET name = 'Necklaces', slug = 'necklaces' WHERE id = 9");
echo "Renamed Necklace (ID 9) to 'Necklaces'.\n";

echo "Database cleanup completed successfully.\n";
