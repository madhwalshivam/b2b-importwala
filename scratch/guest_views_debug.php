<?php
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/app/Core/EnvLoader.php';
App\Core\EnvLoader::load(ROOT_PATH);
require ROOT_PATH . '/app/Helpers/Functions.php';
require ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();
$db->exec("ALTER TABLE guest_product_views MODIFY guest_token VARCHAR(128) NOT NULL");
$n = $db->exec("UPDATE guest_product_views SET guest_token = SUBSTRING_INDEX(guest_token, '.', 1) WHERE guest_token LIKE '%.%'");
echo "normalized rows: {$n}\n";
$col = $db->query("SHOW COLUMNS FROM guest_product_views LIKE 'guest_token'")->fetch(PDO::FETCH_ASSOC);
echo "column: " . json_encode($col) . "\n";
$sample = $db->query("SELECT guest_token, LENGTH(guest_token) AS len, COUNT(*) AS n FROM guest_product_views GROUP BY guest_token")->fetchAll(PDO::FETCH_ASSOC);
echo "tokens: " . json_encode($sample) . "\n";
