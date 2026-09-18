<?php

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../app/Core/Database.php";
require __DIR__ . "/../app/Core/Model.php";
require __DIR__ . "/../app/Models/ProductSpecification.php";

$db        = \App\Core\Database::getInstance();
$specModel = new \App\Models\ProductSpecification();

$savedId = $specModel->saveSingleSpec(510, 0, "Test Key", "Test Value");

echo "Saved spec ID: $savedId\n";
