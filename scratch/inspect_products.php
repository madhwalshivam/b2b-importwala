<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

$db = App\Core\Database::getInstance();
$prods = $db->query("SELECT id, name, main_image FROM products WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

echo "Total Active Products: " . count($prods) . "\n\n";
foreach ($prods as $p) {
    echo "ID: {$p['id']} | Name: {$p['name']}\n";
    echo "     Image: {$p['main_image']}\n";

    $stEmbed = $db->prepare("SELECT id FROM product_image_embeddings WHERE product_id = ?");
    $stEmbed->execute([$p['id']]);
    $hasEmbed = $stEmbed->fetchColumn();
    echo "     Embedded in DB: " . ($hasEmbed ? "YES" : "NO") . "\n---\n";
}
