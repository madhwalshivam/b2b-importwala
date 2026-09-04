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

$products = $db->query("SELECT id, name, category_id FROM products WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    echo "=========================================\n";
    echo "PRODUCT #{$p['id']}: {$p['name']} (Cat ID: {$p['category_id']})\n";
    
    // Get raw matches without fallback
    $all = $service->getDebugAnalysis($p['name']); // or test vector
    
    $queryVecRow = $db->query("SELECT embedding_vector FROM product_image_embeddings WHERE product_id = {$p['id']}")->fetch(PDO::FETCH_ASSOC);
    if (!$queryVecRow) continue;
    $qVec = json_decode($queryVecRow['embedding_vector'], true);
    
    $allEmbeds = $db->query("SELECT e.product_id, p.name, p.category_id, c.name as cat_name, c.parent_id, e.embedding_vector FROM product_image_embeddings e JOIN products p ON e.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allEmbeds as $target) {
        if ($target['product_id'] == $p['id']) continue;
        $tVec = json_decode($target['embedding_vector'], true);
        $score = $service->calculateCosineSimilarity($qVec, $tVec);
        $pct = round($score * 100, 1);
        echo "  -> #{$target['product_id']} [{$pct}%] (Cat #{$target['category_id']}: {$target['cat_name']}) {$target['name']}\n";
    }
}
