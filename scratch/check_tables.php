<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "Existing DB Tables (" . count($tables) . "):\n";
foreach ($tables as $t) {
    echo " - " . $t . "\n";
}
