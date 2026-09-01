<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();

    echo "Running migration for Navigation Links (nav_links)...\n";

    // 1. Create nav_links table
    $db->exec("CREATE TABLE IF NOT EXISTS `nav_links` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `label` VARCHAR(150) NOT NULL,
        `url` VARCHAR(255) NOT NULL,
        `type` VARCHAR(50) NOT NULL DEFAULT 'internal',
        `parent_id` INT UNSIGNED NULL DEFAULT NULL,
        `sort_order` INT NOT NULL DEFAULT 0,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `open_in_new_tab` TINYINT(1) NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`parent_id`) REFERENCES `nav_links` (`id`) ON DELETE CASCADE,
        INDEX `idx_nav_parent_sort` (`parent_id`, `is_active`, `sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Check if table is empty, if empty seed default links
    $checkStmt = $db->query("SELECT COUNT(*) FROM `nav_links`");
    $count = (int)$checkStmt->fetchColumn();

    if ($count === 0) {
        echo "Seeding default top navigation links...\n";

        // Insert Parent Links
        $links = [
            ['label' => 'Home', 'url' => '/', 'type' => 'internal', 'sort_order' => 1, 'is_active' => 1, 'open_in_new_tab' => 0],
            ['label' => 'Categories', 'url' => '/catalog', 'type' => 'dropdown', 'sort_order' => 2, 'is_active' => 1, 'open_in_new_tab' => 0],
            ['label' => 'New Arrivals', 'url' => '/catalog?sort=newest', 'type' => 'internal', 'sort_order' => 3, 'is_active' => 1, 'open_in_new_tab' => 0],
            ['label' => 'Best Sellers', 'url' => '/catalog?sort=popular', 'type' => 'internal', 'sort_order' => 4, 'is_active' => 1, 'open_in_new_tab' => 0],
            ['label' => 'Free Air Shipping', 'url' => '/catalog?free_shipping=1', 'type' => 'internal', 'sort_order' => 5, 'is_active' => 1, 'open_in_new_tab' => 0],
            ['label' => 'Price Drops', 'url' => '/catalog?price_drops=1', 'type' => 'internal', 'sort_order' => 6, 'is_active' => 1, 'open_in_new_tab' => 0],
            ['label' => 'Halloween', 'url' => '/catalog?q=halloween', 'type' => 'internal', 'sort_order' => 7, 'is_active' => 1, 'open_in_new_tab' => 0],
            ['label' => 'Blog', 'url' => '/blog', 'type' => 'internal', 'sort_order' => 8, 'is_active' => 1, 'open_in_new_tab' => 0],
            ['label' => 'Support', 'url' => '/support', 'type' => 'dropdown', 'sort_order' => 9, 'is_active' => 1, 'open_in_new_tab' => 0],
        ];

        $insertStmt = $db->prepare("INSERT INTO `nav_links` (`label`, `url`, `type`, `parent_id`, `sort_order`, `is_active`, `open_in_new_tab`) VALUES (?, ?, ?, ?, ?, ?, ?)");

        $insertedIds = [];
        foreach ($links as $link) {
            $insertStmt->execute([
                $link['label'],
                $link['url'],
                $link['type'],
                null,
                $link['sort_order'],
                $link['is_active'],
                $link['open_in_new_tab']
            ]);
            $insertedIds[$link['label']] = (int)$db->lastInsertId();
        }

        // Seed Sub-items under 'Categories' dropdown
        if (isset($insertedIds['Categories'])) {
            $catParentId = $insertedIds['Categories'];

            // Fetch top categories from categories table if exist
            $catDbStmt = $db->query("SELECT name, slug FROM categories WHERE parent_id IS NULL AND status = 'active' ORDER BY sort_order ASC, name ASC LIMIT 6");
            $dbCategories = $catDbStmt ? $catDbStmt->fetchAll() : [];

            if (!empty($dbCategories)) {
                $subSort = 1;
                foreach ($dbCategories as $catRow) {
                    $insertStmt->execute([
                        $catRow['name'],
                        '/catalog?category=' . urlencode($catRow['slug']),
                        'category',
                        $catParentId,
                        $subSort++,
                        1,
                        0
                    ]);
                }
            } else {
                // Fallback default subcategories
                $catSubLinks = [
                    ['label' => 'All Categories', 'url' => '/catalog', 'type' => 'category', 'sort_order' => 1],
                    ['label' => 'Scooter Parts & Accessories', 'url' => '/catalog?category=scooter-parts', 'type' => 'category', 'sort_order' => 2],
                    ['label' => 'Helmets & Protective Gear', 'url' => '/catalog?category=helmets', 'type' => 'category', 'sort_order' => 3],
                    ['label' => 'Electronics & Gadgets', 'url' => '/catalog?category=electronics', 'type' => 'category', 'sort_order' => 4],
                    ['label' => 'Wholesale Bulk Packages', 'url' => '/catalog?category=wholesale', 'type' => 'category', 'sort_order' => 5],
                ];
                foreach ($catSubLinks as $sub) {
                    $insertStmt->execute([$sub['label'], $sub['url'], $sub['type'], $catParentId, $sub['sort_order'], 1, 0]);
                }
            }
        }

        // Seed Sub-items under 'Support' dropdown
        if (isset($insertedIds['Support'])) {
            $supParentId = $insertedIds['Support'];
            $supSubLinks = [
                ['label' => 'Help Center & FAQs', 'url' => '/support', 'type' => 'internal', 'sort_order' => 1],
                ['label' => 'Contact Support', 'url' => '/contact-us', 'type' => 'internal', 'sort_order' => 2],
                ['label' => 'Shipping & Air Freight Policy', 'url' => '/shipping-policy', 'type' => 'internal', 'sort_order' => 3],
                ['label' => 'Refund & Replacement Policy', 'url' => '/refund-policy', 'type' => 'internal', 'sort_order' => 4],
            ];
            foreach ($supSubLinks as $sub) {
                $insertStmt->execute([$sub['label'], $sub['url'], $sub['type'], $supParentId, $sub['sort_order'], 1, 0]);
            }
        }

        echo "Default top navigation links seeded successfully!\n";
    } else {
        echo "Table nav_links already contains data, skipping seed.\n";
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}
