<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();
$cols = $db->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);

$needed = [
    'sales_count' => "INT UNSIGNED NOT NULL DEFAULT 0",
    'title' => "VARCHAR(255) NULL",
    'base_price' => "DECIMAL(12,4) NOT NULL DEFAULT 0.0000",
    'gallery_images' => "JSON NULL",
    'moq' => "INT UNSIGNED NOT NULL DEFAULT 1"
];

foreach ($needed as $col => $def) {
    if (!in_array($col, $cols)) {
        $db->exec("ALTER TABLE `products` ADD COLUMN `{$col}` {$def}");
        echo "Added column `{$col}` to products table.\n";
    }
}

$db->exec("UPDATE `products` SET `title` = `name` WHERE `title` IS NULL OR `title` = ''");
$db->exec("UPDATE `products` SET `base_price` = `price` WHERE `base_price` = 0");

echo "Products columns verification complete!\n";
