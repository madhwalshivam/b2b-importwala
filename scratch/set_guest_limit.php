<?php
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/app/Core/EnvLoader.php';
App\Core\EnvLoader::load(ROOT_PATH);
require ROOT_PATH . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

App\Models\Setting::set('guest_product_view_limit', '3');
echo 'limit=' . App\Models\Setting::get('guest_product_view_limit', 'missing') . PHP_EOL;
