<?php
require __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';
\App\Core\Database::init($config);
$db = \App\Core\Database::getInstance();

$stmt = $db->query("SELECT id, name, title, sku FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$weirdCount = 0;
foreach ($products as $p) {
    $name = $p['name'] ?? $p['title'] ?? '';
    
    // Check for smart quotes, dashes, html entities, utf8 multibyte characters, odd symbols
    $reasons = [];
    if (str_contains($name, '&amp;') || str_contains($name, '&#039;') || str_contains($name, '&quot;') || str_contains($name, '&nbsp;')) {
        $reasons[] = 'HTML Entities';
    }
    if (preg_match('/[‘’“”–—…®™©]/u', $name)) {
        $reasons[] = 'Smart Quotes/Dashes/Symbols';
    }
    if (str_contains($name, "\\'") || str_contains($name, '\\"')) {
        $reasons[] = 'Escaped Quotes';
    }
    if (preg_match('/[^\x20-\x7E]/', $name)) {
        $reasons[] = 'Non-ASCII bytes';
    }
    
    if (!empty($reasons)) {
        $weirdCount++;
        echo "ID {$p['id']} [{$p['sku']}]: {$name} | Reasons: " . implode(', ', $reasons) . "\n";
    }
}

echo "\nTotal products with potential symbol issues: {$weirdCount} out of " . count($products) . "\n";
