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
$testImage = ROOT_PATH . '/public/uploads/products/stainless-steel-necklaces.jpg';

echo "Testing debug analysis for Product 1 image ($testImage):\n";
$analysis = $s->getDebugAnalysis($testImage);
print_r($analysis);
