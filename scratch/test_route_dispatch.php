<?php
define('ROOT_PATH', dirname(__DIR__));

if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Helpers/Functions.php';

// Mock session admin login
$_SESSION['admin_user_id'] = 1;
$_POST['_csrf_token'] = 'test';
$_SESSION['_csrf_token'] = 'test';

// Test BlogController handleFileUpload directly
$controller = new App\Controllers\Admin\BlogController();

echo "Testing handleFileUpload resolution...\n";
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('handleFileUpload');
$method->setAccessible(true);

$dummyFile = [
    'name' => 'test.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => tempnam(sys_get_temp_dir(), 'test'),
    'error' => UPLOAD_ERR_OK,
    'size' => 1024
];

// Write small fake jpg header to temp file
file_put_contents($dummyFile['tmp_name'], "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01");

try {
    $res = $method->invoke($controller, $dummyFile, 'uploads/blogs/test/');
    echo "Upload handler output path: " . ($res['path'] ?? 'NULL') . "\n";
    echo "Upload handler error: " . ($res['error'] ?? 'NONE') . "\n";
    echo "[SUCCESS] handleFileUpload executed without throwing any undefined constant exception!\n";
} catch (\Throwable $e) {
    echo "[ERROR] Exception thrown: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

@unlink($dummyFile['tmp_name']);
