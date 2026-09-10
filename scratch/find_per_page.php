<?php
$files = [
    'c:/xampp/htdocs/importwala/app/Controllers/Web/CatalogController.php',
    'c:/xampp/htdocs/importwala/views/web/shop.php',
    'c:/xampp/htdocs/importwala/app/Controllers/ShopController.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    echo "=== {$file} ===\n";
    $lines = explode("\n", file_get_contents($file));
    foreach ($lines as $i => $line) {
        if (preg_match('/(per_page|perPage|24|12)/i', $line)) {
            echo ($i + 1) . ": " . trim($line) . "\n";
        }
    }
}
