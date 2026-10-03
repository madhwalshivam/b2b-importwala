<?php
$env = parse_ini_file(__DIR__ . '/../.env');
$db = new PDO('mysql:host='.$env['DB_HOST'].';dbname='.$env['DB_DATABASE'], $env['DB_USERNAME'], $env['DB_PASSWORD']);
$r = $db->query("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'products' AND TABLE_SCHEMA = '".$env['DB_DATABASE']."'");
print_r($r->fetchAll(PDO::FETCH_ASSOC));
