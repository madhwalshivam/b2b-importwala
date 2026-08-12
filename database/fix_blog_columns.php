<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "Checking and adding missing columns in `blog_posts`...\n";

$columnsNeeded = [
    'title' => "VARCHAR(255) NOT NULL",
    'slug' => "VARCHAR(255) NOT NULL UNIQUE",
    'excerpt' => "TEXT NULL",
    'content' => "LONGTEXT NULL",
    'featured_image' => "VARCHAR(255) NULL",
    'featured_image_alt' => "VARCHAR(255) NULL",
    'meta_title' => "VARCHAR(255) NULL",
    'meta_description' => "TEXT NULL",
    'focus_keyword' => "VARCHAR(255) NULL",
    'author_name' => "VARCHAR(150) DEFAULT 'Mudsor Team'",
    'status' => "ENUM('draft', 'published') DEFAULT 'draft'",
    'views' => "INT UNSIGNED DEFAULT 0",
    'published_at' => "DATETIME NULL"
];

$existingColumns = $db->query("SHOW COLUMNS FROM blog_posts")->fetchAll(PDO::FETCH_COLUMN);

foreach ($columnsNeeded as $col => $def) {
    if (!in_array($col, $existingColumns)) {
        if ($col === 'featured_image' && in_array('image', $existingColumns)) {
            $db->exec("ALTER TABLE `blog_posts` CHANGE `image` `featured_image` VARCHAR(255) NULL");
            echo "Renamed column `image` to `featured_image`.\n";
        } else {
            $db->exec("ALTER TABLE `blog_posts` ADD COLUMN `{$col}` {$def}");
            echo "Added missing column `{$col}`.\n";
        }
    }
}

echo "Database table `blog_posts` schema is verified!\n";
