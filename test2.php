<?php
$db=new PDO('mysql:host=127.0.0.1;dbname=mudsor','root','');
$res=$db->query('SELECT product_id FROM product_related WHERE relation_type="frequently_bought" LIMIT 1')->fetchColumn();
echo $res;
