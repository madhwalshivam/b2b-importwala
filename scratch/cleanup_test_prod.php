<?php
define('ROOT_PATH', dirname(__DIR__));
spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

use App\Core\Database;

$db = Database::getInstance();
$db->prepare("DELETE FROM product_image_embeddings WHERE product_id = 20")->execute();
$db->prepare("DELETE FROM products WHERE id = 20")->execute();
echo "Cleaned up temporary test product #20.\n";
