<?php
require 'app/Core/Database.php';
require 'app/Core/Model.php';
require 'app/Models/Product.php';
$m = new \App\Models\Product();
$res = $m->getRelatedProducts(1, 'frequently_bought', 4);
print_r(array_map(function($r) { return $r['slug']; }, $res));
