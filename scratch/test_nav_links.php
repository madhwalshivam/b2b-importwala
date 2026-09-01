<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Models/NavLink.php';

use App\Models\NavLink;

try {
    $navModel = new NavLink();
    
    echo "=== TESTING NAVLINK TREE FETCH (Active Only) ===\n";
    $activeTree = $navModel->getTree(true);
    foreach ($activeTree as $item) {
        echo " - [#{$item['sort_order']}] {$item['label']} ({$item['url']}) [Type: {$item['type']}]\n";
        if (!empty($item['children'])) {
            foreach ($item['children'] as $child) {
                echo "      ↳ [#{$child['sort_order']}] {$child['label']} ({$child['url']})\n";
            }
        }
    }

    echo "\n=== TESTING ALL FLAT LINKS FOR ADMIN ===\n";
    $flat = $navModel->getAllFlat();
    echo "Total links in DB: " . count($flat) . "\n";

    echo "\nTEST PASSED!\n";
} catch (Throwable $e) {
    echo "TEST FAILED: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
