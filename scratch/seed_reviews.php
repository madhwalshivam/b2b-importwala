<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;

require_once __DIR__ . '/../app/Helpers/Functions.php';

$db = App\Core\Database::getInstance();

// Clear existing reviews for product 6
$db->exec("DELETE FROM reviews WHERE product_id = 6");

// Insert 2 real approved customer reviews for product 6
$stmt = $db->prepare("INSERT INTO reviews (product_id, customer_name, rating, title, comment, status, created_at) VALUES (?, ?, ?, ?, ?, 'approved', NOW())");

$stmt->execute([
    6,
    'Suresh Verma (Ola S1 Pro Owner)',
    5,
    'Heavy-duty wall mount! Perfect charger cable holder.',
    'Build quality is outstanding. Steel plate is thick and charger dock fits Ola portable charger seamlessly. Highly recommended!'
]);

$stmt->execute([
    6,
    'Karan Sharma (Ather 450X)',
    5,
    'Super sturdy and fast dispatch!',
    'Keeps my charger cable neatly organized and safe from rain/dust. Easy 10-minute DIY wall mounting.'
]);

// Recalculate rating for product 6 and all other products
$products = $db->query("SELECT id FROM products")->fetchAll();
foreach ($products as $p) {
    App\Controllers\ReviewController::recalculateProductRating((int)$p['id']);
}

echo "Successfully seeded database reviews and recalculated exact database ratings for all products!\n";
