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
$res = $s->searchByProductId(1, 8);

echo "Product #1 Visually Similar Items Count: " . count($res['items']) . "\n";
echo "Headline: " . $res['headline'] . "\n";
foreach ($res['items'] as $item) {
    echo " - " . $item['name'] . " (Score: " . $item['similarity_score'] . "% | Badge: " . $item['match_badge'] . ")\n";
}
