<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/importwala/about-us';
$_SERVER['REQUEST_METHOD'] = 'GET';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

function url($path = '') {
    return '/importwala/' . ltrim($path, '/');
}
function asset($path = '') {
    return '/importwala/public/assets/' . ltrim($path, '/');
}
function csrf_token() {
    return 'test_csrf_token';
}

$controller = new App\Controllers\Web\SupportController();
ob_start();
$controller->about();
$output = ob_get_clean();

echo "Render Status: SUCCESS\n";
echo "Output Length: " . strlen($output) . " bytes\n";
if (str_contains($output, 'About ImportWale') && str_contains($output, 'B2B Wholesale Sourcing Marketplace')) {
    echo "Verification: Key text 'About ImportWale' and 'B2B Wholesale Sourcing Marketplace' found!\n";
} else {
    echo "Verification: FAILED - Key text missing\n";
}
