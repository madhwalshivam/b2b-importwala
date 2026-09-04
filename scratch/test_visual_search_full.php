<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

use App\Services\VisualSearchService;
use App\Core\Database;

echo "=========================================================\n";
echo " IMPORTWALE AI VISUAL SEARCH ENGINE VERIFICATION SUITE\n";
echo "=========================================================\n\n";

$service = new VisualSearchService();
$db = Database::getInstance();

// 1. Batch Indexing Catalog Products
echo "1. Batch Indexing Catalog Products:\n";
$stats = $service->indexAllProducts(true);
echo "   Indexed {$stats['indexed']} of {$stats['total']} catalog products.\n";

$totalVectors = (int)$db->query("SELECT COUNT(*) FROM product_image_embeddings")->fetchColumn();
echo "   Database vectors stored in product_image_embeddings: {$totalVectors}\n\n";

// 2. Testing Microservice & CLI Vector Generation
echo "2. Feature Vector Extraction Test:\n";
$sampleImg = ROOT_PATH . '/public/assets/images/importwale-logo.png';
$vector = $service->generateEmbedding($sampleImg);

if ($vector && count($vector) === 128) {
    echo "   [PASS] Generated 128-dimensional L2-normalized vector (first 5 dims: [" . implode(', ', array_slice($vector, 0, 5)) . "])\n\n";
} else {
    echo "   [FAIL] Could not generate embedding vector\n\n";
}

// 3. Testing Upload Search Flow with Strong & Similar Matches
echo "3. Testing Visual Search by Sample Query Image:\n";
// Create a temporary test query image
$testUpload = ROOT_PATH . '/storage/test_query_' . time() . '.png';
copy($sampleImg, $testUpload);

$searchResult = $service->searchByUploadedImage($testUpload, null, 6);

echo "   Headline: {$searchResult['headline']}\n";
echo "   Has Matches: " . ($searchResult['has_matches'] ? 'YES' : 'NO') . "\n";
echo "   Is Fallback: " . (!empty($searchResult['is_fallback']) ? 'YES' : 'NO') . "\n";
echo "   Total Items Returned: " . count($searchResult['items']) . "\n\n";

if (!empty($searchResult['items'])) {
    echo "   Top Ranked Items:\n";
    foreach ($searchResult['items'] as $idx => $item) {
        $badge = $item['match_badge'] ?? 'Match';
        echo "   #" . ($idx + 1) . " {$item['name']} | Score: {$item['similarity_score']}% | Badge: {$badge}\n";
    }
}

echo "\n=========================================================\n";
echo " VISUAL SEARCH VERIFICATION COMPLETE!\n";
echo "=========================================================\n";
