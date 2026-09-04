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
$prods = $db->query("SELECT p.id, p.name, p.category_id, c.name as category_name, c.parent_id FROM products p LEFT JOIN categories c ON p.category_id = c.id")->fetchAll(PDO::FETCH_ASSOC);

foreach ($prods as $p) {
    echo "ID {$p['id']}: {$p['name']} (Category ID {$p['category_id']}: {$p['category_name']}, Parent ID: " . ($p['parent_id'] ?? 'none') . ")\n";
}
