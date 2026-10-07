<?php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_PATH);
require_once ROOT_PATH . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

use App\Core\Database;

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/product_image_features.sql');
    $db->exec($sql);
    echo "SUCCESS: Created product_image_features table.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
