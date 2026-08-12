<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});
require_once __DIR__ . '/../app/Helpers/Functions.php';

use App\Core\Database;

try {
    $db = Database::getInstance();

    // 1. Add video_thumbnail and auto_video_thumbnail to products table
    $productsCols = [
        "video_thumbnail VARCHAR(512) DEFAULT NULL",
        "auto_video_thumbnail VARCHAR(512) DEFAULT NULL"
    ];

    foreach ($productsCols as $colDef) {
        $colName = explode(' ', trim($colDef))[0];
        $check = $db->query("SHOW COLUMNS FROM products LIKE '{$colName}'")->fetchAll();
        if (empty($check)) {
            $db->exec("ALTER TABLE products ADD COLUMN {$colDef};");
            echo "Added {$colName} column to products table.\n";
        }
    }

    // 2. Add auto_thumbnail to homepage_videos table
    $hpCols = [
        "auto_thumbnail VARCHAR(512) DEFAULT NULL"
    ];

    foreach ($hpCols as $colDef) {
        $colName = explode(' ', trim($colDef))[0];
        $check = $db->query("SHOW COLUMNS FROM homepage_videos LIKE '{$colName}'")->fetchAll();
        if (empty($check)) {
            $db->exec("ALTER TABLE homepage_videos ADD COLUMN {$colDef};");
            echo "Added {$colName} column to homepage_videos table.\n";
        }
    }

    echo "Video thumbnails migration completed successfully!\n";
} catch (\Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
