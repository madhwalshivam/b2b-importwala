<?php
require "vendor/autoload.php";
require "app/Core/Database.php";
$db = \App\Core\Database::getInstance();
$res = $db->query("SELECT * FROM product_specifications WHERE product_id=510");
var_dump($res->fetchAll(PDO::FETCH_ASSOC));
$res2 = $db->query("SELECT * FROM product_colors WHERE product_id=510");
var_dump($res2->fetchAll(PDO::FETCH_ASSOC));
$res3 = $db->query("SELECT * FROM product_color_sizes");
var_dump($res3->fetchAll(PDO::FETCH_ASSOC));
