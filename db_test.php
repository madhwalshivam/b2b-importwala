<?php
require "vendor/autoload.php";
$db = \App\Core\Database::getInstance();
print_r($db->query("DESCRIBE product_color_sizes")->fetchAll(PDO::FETCH_ASSOC));
