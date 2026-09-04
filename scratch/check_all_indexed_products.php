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
$items = $db->query("SELECT p.id, p.name, p.category_id, c.name as cat_name, c.parent_id, e.generated_at FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN product_image_embeddings e ON p.id = e.product_id WHERE p.status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

print_r($items);
