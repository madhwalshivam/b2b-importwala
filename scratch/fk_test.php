<?php
require "vendor/autoload.php";
require "app/Core/Database.php";
$db = \App\Core\Database::getInstance();
$res = $db->query("SHOW CREATE TABLE product_colors")->fetch(PDO::FETCH_ASSOC);
print_r($res);
$res2 = $db->query("SHOW CREATE TABLE product_color_sizes")->fetch(PDO::FETCH_ASSOC);
print_r($res2);
