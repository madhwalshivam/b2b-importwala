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

$db = App\Core\Database::getInstance();
$totalProducts = $db->query("SELECT COUNT(id) FROM products")->fetchColumn();
$activeProducts = $db->query("SELECT COUNT(id) FROM products WHERE status = 'active'")->fetchColumn();
$totalEmbeddings = $db->query("SELECT COUNT(id) FROM product_image_embeddings")->fetchColumn();

echo "Total Products: {$totalProducts}\n";
echo "Active Products: {$activeProducts}\n";
echo "Total Embeddings: {$totalEmbeddings}\n\n";

$rows = $db->query("SELECT e.id, e.product_id, p.name, e.image_path, e.generated_at FROM product_image_embeddings e JOIN products p ON e.product_id = p.id")->fetchAll(PDO::FETCH_ASSOC);

echo "Indexed Products:\n";
foreach ($rows as $r) {
    echo "Product ID: {$r['product_id']} | Name: {$r['name']} | Img: {$r['image_path']}\n";
}
