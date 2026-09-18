<?php

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../app/Core/Database.php";

$db = \App\Core\Database::getInstance();

$stmt = $db->query("SELECT id, price, size_label FROM product_color_sizes LIMIT 2");

print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
