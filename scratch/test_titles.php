<?php
require __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';
\App\Core\Database::init($config);
$db = \App\Core\Database::getInstance();

$stmt = $db->query("SELECT id, title, name FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    $t = $p['title'] ?? $p['name'] ?? '';
    // Let's check for any characters outside simple alphanumeric and space and hyphen/comma
    if (preg_match('/[^a-zA-Z0-9\s\-\,\.\(\)\/\+\:\'\&]/', $t)) {
        echo "ID: {$p['id']} | Title: " . bin2hex($t) . " | ASCII: {$t}\n";
    }
}
