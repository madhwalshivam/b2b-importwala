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

use App\Services\VisualSearchService;
use App\Core\Database;

$db = Database::getInstance();
$service = new VisualSearchService();

// Fetch 5 sample products
$products = $db->query("SELECT id, name, category_id FROM products WHERE status = 'active' ORDER BY id ASC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    echo "=========================================\n";
    echo "PRODUCT #{$p['id']}: {$p['name']} (Cat ID: {$p['category_id']})\n";
    $res = $service->searchByProductId((int)$p['id'], 10);
    echo "Headline: {$res['headline']}\n";
    echo "Has Matches: " . ($res['has_matches'] ? 'YES' : 'NO') . " (Total: {$res['total']})\n";
    foreach ($res['items'] as $idx => $item) {
        echo "  #{$idx} [{$item['similarity_score']}%] (Cat: {$item['category_name']}) {$item['name']}\n";
    }
}
