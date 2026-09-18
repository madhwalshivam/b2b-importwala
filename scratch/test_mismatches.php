<?php
require __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';
\App\Core\Database::init($config);
$db = \App\Core\Database::getInstance();

$tables = [
    'products' => ['name', 'title', 'meta_title'],
    'categories' => ['name'],
    'brands' => ['name'],
    'scooter_models' => ['name', 'model_name'],
    'featured_categories' => ['name', 'title'],
    'homepage_sections' => ['title', 'subtitle'],
    'collection_cards' => ['title', 'subtitle'],
    'product_color_sizes' => ['color_name', 'size_label'],
    'rfq_requests' => ['product_name']
];

echo "=== FINDING ALL ENTITIES & SYMBOL MISMATCHES IN DATABASE ===\n";

$totalIssues = 0;
foreach ($tables as $table => $cols) {
    try {
        $stmt = $db->query("SELECT * FROM `{$table}`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            foreach ($cols as $col) {
                if (isset($r[$col]) && is_string($r[$col])) {
                    $val = $r[$col];
                    $decoded = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $cleaned = str_replace(["\xEF\xBB\xBF", "â€™", "â€\"", "â€œ", "â€", "Â"], ["", "'", "-", '"', '"', ""], $decoded);
                    if ($cleaned !== $val || str_contains($val, '&') || str_contains($val, '\'') || str_contains($val, '"')) {
                        $totalIssues++;
                        echo "Table `{$table}` (ID {$r['id']}) Col `{$col}`: '{$val}' => '{$cleaned}'\n";
                    }
                }
            }
        }
    } catch (\Throwable $e) {
        // table might not exist or col missing
    }
}

echo "\nTotal issues found: {$totalIssues}\n";
