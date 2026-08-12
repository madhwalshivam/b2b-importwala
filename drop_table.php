<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$db->exec('DROP TABLE IF EXISTS product_links');
echo 'Table dropped successfully';
