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

use App\Core\Database;
use App\Services\VisualSearchService;

$db = Database::getInstance();
$service = new VisualSearchService();

$products = $db->query("SELECT p.id, p.name, p.category_id, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    $res = $service->searchByProductId((int)$p['id'], 8);
    echo "=======================================================\n";
    echo "PRODUCT #{$p['id']}: {$p['name']} [Cat: {$p['cat_name']}]\n";
    echo "Has Matches: " . ($res['has_matches'] ? 'YES' : 'NO') . " (Total: {$res['total']})\n";
    if ($res['has_matches']) {
        foreach ($res['items'] as $item) {
            echo "  -> Match #{$item['id']} [{$item['similarity_score']}%] [Cat: {$item['category_name']}] {$item['name']}\n";
            echo "     Image: {$item['main_image']}\n";
        }
    } else {
        echo "  -> Section will be HIDDEN completely (Zero filler/unrelated fallback)\n";
    }
}
