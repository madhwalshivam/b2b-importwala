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
$cats = $db->query("SELECT c1.id, c1.name, c1.parent_id, c2.name as parent_name FROM categories c1 LEFT JOIN categories c2 ON c1.parent_id = c2.id")->fetchAll(PDO::FETCH_ASSOC);

foreach ($cats as $c) {
    echo "ID {$c['id']}: {$c['name']} (Parent: " . ($c['parent_name'] ?? 'Root') . " [ID: " . ($c['parent_id'] ?? 'none') . "])\n";
}
