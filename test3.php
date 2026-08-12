<?php
require 'app/Core/Database.php';
require 'app/Core/Model.php';
require 'app/Models/Product.php';
$db = \App\Core\Database::getInstance();
$slug = $db->query('SELECT slug FROM products JOIN product_related ON products.id = product_related.product_id LIMIT 1')->fetchColumn();
echo $slug;
