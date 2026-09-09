<?php
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Services/VisualSearchService.php';

use App\Core\Database;
use App\Services\VisualSearchService;

$db = Database::getInstance();
$service = new VisualSearchService();

// Get first product ID
$productId = (int)$db->query("SELECT id FROM products LIMIT 1")->fetchColumn();
echo "Testing searchByProductId for product ID {$productId}...\n";

$start = microtime(true);
$res = $service->searchByProductId($productId);
$elapsed = (microtime(true) - $start) * 1000;

echo "Completed in " . round($elapsed, 2) . " ms!\n";
echo "Has matches: " . ($res['has_matches'] ? 'YES' : 'NO') . "\n";
