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

// Test thresholds 0.60, 0.65, 0.70, 0.75
$thresholds = [0.60, 0.65, 0.70, 0.75];

$products = $db->query("SELECT p.id, p.name, p.category_id, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    echo "=======================================================\n";
    echo "PRODUCT #{$p['id']}: {$p['name']} [Cat: {$p['cat_name']}]\n";
    
    // Get query vector
    $qRow = $db->query("SELECT embedding_vector FROM product_image_embeddings WHERE product_id = {$p['id']}")->fetch(PDO::FETCH_ASSOC);
    if (!$qRow) continue;
    $qVec = json_decode($qRow['embedding_vector'], true);
    
    $allEmbeds = $db->query("SELECT e.product_id, p.name, p.category_id, c.name as cat_name, e.embedding_vector FROM product_image_embeddings e JOIN products p ON e.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($thresholds as $th) {
        $matches = [];
        foreach ($allEmbeds as $target) {
            if ($target['product_id'] == $p['id']) continue;
            
            // Check category relatedness
            $isRelatedCat = $service->areCategoriesRelated($p['category_id'], $target['category_id']);
            if (!$isRelatedCat) continue;
            
            $tVec = json_decode($target['embedding_vector'], true);
            $score = $service->calculateCosineSimilarity($qVec, $tVec);
            
            if ($score >= $th) {
                $matches[] = [
                    'id' => $target['product_id'],
                    'name' => $target['name'],
                    'cat' => $target['cat_name'],
                    'score' => round($score * 100, 1)
                ];
            }
        }
        
        echo "  Threshold " . ($th * 100) . "%: " . count($matches) . " matches\n";
        foreach ($matches as $m) {
            echo "    -> #{$m['id']} [{$m['score']}%] ({$m['cat']}) {$m['name']}\n";
        }
    }
}
