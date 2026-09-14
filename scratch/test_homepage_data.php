<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/FeaturedCategory.php';
require_once __DIR__ . '/../app/Models/FeaturedSubcategory.php';

$catModel = new App\Models\FeaturedCategory();
$subcatModel = new App\Models\FeaturedSubcategory();

$mainTiles = $catModel->getActive();
$subIcons = $subcatModel->getActive();

echo "=== Section 1: Main Category Tiles (" . count($mainTiles) . " items) ===\n";
foreach ($mainTiles as $t) {
    echo "ID: {$t['id']} | Name: {$t['name']} | Image: {$t['image']} | Link: {$t['link_url']}\n";
}

echo "\n=== Section 2: Subcategory Icons (" . count($subIcons) . " items) ===\n";
foreach ($subIcons as $s) {
    echo "ID: {$s['id']} | Name: {$s['name']} | Image: {$s['image']} | Link: {$s['link_url']}\n";
}
