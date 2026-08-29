<?php
require_once __DIR__ . '/../public/index.php';

use App\Core\Database;

$db = Database::getInstance();
$stmt = $db->query("SELECT id, name, main_image FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($products);
