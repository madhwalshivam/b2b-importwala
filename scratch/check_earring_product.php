<?php
define('ROOT_PATH', dirname(__DIR__));
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

use App\Core\Database;

$db = Database::getInstance();
$stmt = $db->query("SELECT id, name, main_image, gallery_images FROM products WHERE name LIKE '%Silver Post Autumn%' OR name LIKE '%Pendant Earrings%'");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($products);

foreach ($products as $p) {
    $pid = $p['id'];
    $stmt2 = $db->prepare("SELECT * FROM product_images WHERE product_id = ?");
    $stmt2->execute([$pid]);
    print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
    
    echo "get_product_images:\n";
    print_r(get_product_images($p));
}
