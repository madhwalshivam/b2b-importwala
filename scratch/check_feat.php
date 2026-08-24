<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $cats = $db->query("SELECT * FROM featured_categories ORDER BY sort_order ASC")->fetchAll();
    foreach ($cats as $c) {
        echo "CATEGORY [{$c['id']}]: {$c['name']} (slug: {$c['slug']}, sort: {$c['sort_order']})\n";
        $subs = $db->prepare("SELECT * FROM featured_subcategories WHERE featured_category_id = ? ORDER BY sort_order ASC");
        $subs->execute([$c['id']]);
        foreach ($subs->fetchAll() as $s) {
            echo "  -- SUB [{$s['id']}]: {$s['name']} | image: {$s['image']} | link: {$s['link_url']}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
