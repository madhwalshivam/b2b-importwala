<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();

$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "=== Database Tables ===\n";
foreach ($tables as $t) {
    $cols = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('product_id', $cols)) {
        echo "Table `$t` has `product_id` column.\n";
    }
}
