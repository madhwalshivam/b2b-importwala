<?php
require_once __DIR__ . '/../public/index.php';

use App\Core\Database;

$db = Database::getInstance();

$updates = [
    1 => ['name' => 'Mudsor Premium Stainless Steel Necklace', 'slug' => 'mudsor-stainless-steel-necklace'],
    2 => ['name' => 'Mudsor Statement Crystal Ring', 'slug' => 'mudsor-statement-crystal-ring'],
    3 => ['name' => 'Mudsor Elegant Sapphire Drop Earrings', 'slug' => 'mudsor-sapphire-drop-earrings'],
    4 => ['name' => 'Mudsor Classic Aviator Sunglasses', 'slug' => 'mudsor-classic-aviator-sunglasses'],
    5 => ['name' => 'Mudsor Ultra-Slim Protective Phone Case', 'slug' => 'mudsor-protective-phone-case'],
    6 => ['name' => 'Mudsor Chronograph Luxury Male Watch', 'slug' => 'mudsor-chronograph-luxury-watch'],
];

foreach ($updates as $id => $data) {
    $stmt = $db->prepare("UPDATE products SET name = :name, slug = :slug WHERE id = :id");
    $stmt->execute(['name' => $data['name'], 'slug' => $data['slug'], 'id' => $id]);
}

echo "Products updated successfully!\n";
