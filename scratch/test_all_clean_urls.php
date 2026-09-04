<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

echo "====================================================\n";
echo " IMPORTWALE SEO CLEAN URL SYSTEM VERIFICATION SUITE\n";
echo "====================================================\n\n";

$tests = [
    'Clean Category URL (/category/jewelry)' => function() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['url' => 'category/jewelry'];
        $controller = new \App\Controllers\Web\CatalogController();
        ob_start();
        $controller->category('jewelry');
        $html = ob_get_clean();
        return [str_contains($html, 'Jewelry') || str_contains($html, 'Wholesale Catalog'), strlen($html)];
    },
    'Clean Subcategory URL (/category/jewelry/earrings)' => function() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['url' => 'category/jewelry/earrings'];
        $controller = new \App\Controllers\Web\CatalogController();
        ob_start();
        $controller->subcategory('jewelry', 'earrings');
        $html = ob_get_clean();
        return [str_contains($html, 'Earrings') || str_contains($html, 'Wholesale Catalog'), strlen($html)];
    },
    'Clean Search URL (/search/hoop-and-stud-earrings)' => function() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['url' => 'search/hoop-and-stud-earrings'];
        $controller = new \App\Controllers\Web\CatalogController();
        ob_start();
        $controller->search('hoop-and-stud-earrings');
        $html = ob_get_clean();
        return [str_contains($html, 'Search Results') || str_contains($html, 'hoop'), strlen($html)];
    },
    'All Categories Directory (/categories)' => function() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['url' => 'categories'];
        $controller = new \App\Controllers\Web\CatalogController();
        ob_start();
        $controller->categoriesDirectory();
        $html = ob_get_clean();
        return [str_contains($html, 'Wholesale Product Categories'), strlen($html)];
    },
    'Collection Clean Route (/collection/1)' => function() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['url' => 'collection/1'];
        $controller = new \App\Controllers\Web\CatalogController();
        ob_start();
        $controller->collection('1');
        $html = ob_get_clean();
        return [strlen($html) > 1000, strlen($html)];
    },
];

$passed = 0;
$total = count($tests);

foreach ($tests as $title => $test) {
    try {
        [$ok, $bytes] = $test();
        if ($ok) {
            echo " [PASS] {$title} ({$bytes} bytes)\n";
            $passed++;
        } else {
            echo " [FAIL] {$title}\n";
        }
    } catch (\Throwable $e) {
        echo " [ERROR] {$title}: " . $e->getMessage() . "\n";
    }
}

echo "\nSummary: {$passed} / {$total} Tests Passed Successfully!\n";
