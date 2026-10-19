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
App\Services\AuthSchema::ensure();
$db = App\Core\Database::getInstance();
$cols = App\Services\AuthSchema::columns($db, 'users');
$ok = App\Services\CustomerAuthService::normalizeGstNumber('22AAAAA0000A1Z5');
$bad = App\Services\CustomerAuthService::normalizeGstNumber('invalid');
$empty = App\Services\CustomerAuthService::normalizeGstNumber('  ');
echo json_encode([
    'has_gst_column' => isset($cols['gst_number']),
    'valid' => $ok,
    'invalid' => $bad,
    'empty' => $empty,
], JSON_PRETTY_PRINT) . PHP_EOL;
