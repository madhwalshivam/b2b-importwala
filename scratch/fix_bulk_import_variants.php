<?php
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../app/Helpers/Functions.php';

// Autoload Classes
spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

$db = \App\Core\Database::getInstance();
$vService = new \App\Services\VariationService();

$stmt = $db->query("SELECT id, variation_mode FROM products WHERE variation_mode IN ('single', 'double')");
$products = $stmt->fetchAll();

$count = 0;
foreach ($products as $p) {
    $vService->syncToFlatVariants((int)$p['id'], $p['variation_mode']);
    $count++;
}

echo "Successfully synced variations for $count products.\n";
