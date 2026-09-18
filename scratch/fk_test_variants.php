<?php
require "vendor/autoload.php";
require "app/Core/Database.php";
$db = \App\Core\Database::getInstance();
$res = $db->query("SHOW CREATE TABLE product_variants")->fetch(PDO::FETCH_ASSOC);
print_r($res);
