<?php
define('ROOT_PATH', dirname(__DIR__));

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

require_once __DIR__ . '/../app/Helpers/Functions.php';

$db = App\Core\Database::getInstance();
$products = $db->query("SELECT id, name, main_image FROM products WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

$service = new App\Services\VisualSearchService();

foreach ($products as $p) {
    $res = $service->indexProduct((int)$p['id']);
    echo "ID: {$p['id']} | Name: {$p['name']} | Img: {$p['main_image']} => " . ($res ? "SUCCESS" : "FAILED") . "\n";
}
