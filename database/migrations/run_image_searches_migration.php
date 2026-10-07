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

try {
    $db = \App\Core\Database::getInstance();
    $db->exec(file_get_contents(__DIR__ . '/image_searches.sql'));
    echo "SUCCESS: Created image_searches table.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
