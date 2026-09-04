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

$s = new App\Services\VisualSearchService();
$url = 'https://cbu01.alicdn.com/img/ibank/O1CN01hMajmj1VY9sPbnPkp_!!2213264682664-0-cib.jpg';

echo "Testing callMicroserviceEmbed directly...\n";
$reflector = new ReflectionMethod('App\Services\VisualSearchService', 'callMicroserviceEmbed');
$reflector->setAccessible(true);
$res = $reflector->invoke($s, $url);

echo "callMicroserviceEmbed returned: " . (is_array($res) ? count($res) . " floats" : "NULL") . "\n";
