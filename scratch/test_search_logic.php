<?php
require_once __DIR__ . '/../public/index.php';

use App\Services\VisualSearchService;

$service = new VisualSearchService();

echo "--- TEST 1: Uploading Watch Image ---\n";
$watchFile = __DIR__ . '/../public/uploads/products/luxury-watches.jpg';
$res1 = $service->searchByImage($watchFile, null, 12);
echo "Total Matches Returned: " . count($res1) . "\n";
foreach ($res1 as $item) {
    echo "  - [Score: {$item['similarity_score']}%] ID {$item['id']}: {$item['name']}\n";
}

echo "\n--- TEST 2: Uploading Sunglasses Image ---\n";
$sunFile = __DIR__ . '/../public/uploads/products/sunglasses.jpg';
$res2 = $service->searchByImage($sunFile, null, 12);
echo "Total Matches Returned: " . count($res2) . "\n";
foreach ($res2 as $item) {
    echo "  - [Score: {$item['similarity_score']}%] ID {$item['id']}: {$item['name']}\n";
}
