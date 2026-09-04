<?php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

use App\Core\Database;

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/visual_embeddings_schema.sql');
    $db->exec($sql);
    echo "SUCCESS: Created product_image_embeddings database table!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
