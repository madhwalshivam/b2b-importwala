<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

require_once ROOT_PATH . '/app/Helpers/Functions.php';
require_once ROOT_PATH . '/config/app.php';

$db = App\Core\Database::getInstance();
$cols = $db->query("DESCRIBE tiered_prices")->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);

$rows = $db->query("SELECT * FROM tiered_prices LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
echo "\nExisting Data in tiered_prices:\n";
print_r($rows);
