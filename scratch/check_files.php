<?php
$dir = __DIR__ . '/../public/uploads/products/';
$files = scandir($dir);
print_r($files);
