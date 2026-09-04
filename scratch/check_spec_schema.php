<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("DESCRIBE product_specifications");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
