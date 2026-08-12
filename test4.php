<?php
require 'app/Core/Database.php';
require 'app/Core/Model.php';
require 'app/Models/Product.php';
require 'app/Helpers/Functions.php';
$m = new \App\Models\Product();
$res = $m->getRelatedProducts(1, 'frequently_bought', 4);
foreach ($res as $r) {
    echo url('product/' . ($r['slug'] ?? $r['id'])) . "\n";
}
