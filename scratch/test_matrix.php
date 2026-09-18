<?php
require "vendor/autoload.php";
require "app/Core/Database.php";
require "app/Core/Model.php";
require "app/Models/ProductColor.php";
require "app/Models/ProductColorSize.php";
require "app/Services/VariationService.php";

$vs = new \App\Services\VariationService();
var_dump($vs->getNestedVariantMatrix(510));
