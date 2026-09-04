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
$cols = $db->query("DESCRIBE categories")->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);

$cats = $db->query("SELECT id, name, slug, parent_id FROM categories")->fetchAll(PDO::FETCH_ASSOC);
print_r($cats);
