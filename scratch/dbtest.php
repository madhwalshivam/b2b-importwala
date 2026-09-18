<?php
require "vendor/autoload.php";
require "app/Core/Database.php";
$db = \App\Core\Database::getInstance();
$res = $db->query("SHOW COLUMNS FROM product_color_sizes")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
