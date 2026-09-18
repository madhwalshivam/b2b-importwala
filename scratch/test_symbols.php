<?php
require __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';
\App\Core\Database::init($config);
$db = \App\Core\Database::getInstance();

$stmt = $db->query("SELECT id, name, title, slug FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    $name = $p['name'] ?? $p['title'] ?? '';
    if (str_contains($name, '&') || str_contains($name, '\\') || str_contains($name, '&#') || str_contains($name, ';')) {
        echo "ID {$p['id']}: name = [{$name}]\n";
    }
}
