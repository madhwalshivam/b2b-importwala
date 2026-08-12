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

$db = App\Core\Database::getInstance();
echo "--- PRODUCTS VIDEO COLUMNS ---\n";
print_r($db->query("SHOW COLUMNS FROM products LIKE 'video%'")->fetchAll(PDO::FETCH_ASSOC));

echo "--- HOMEPAGE_VIDEOS COLUMNS ---\n";
print_r($db->query("SHOW COLUMNS FROM homepage_videos")->fetchAll(PDO::FETCH_ASSOC));
