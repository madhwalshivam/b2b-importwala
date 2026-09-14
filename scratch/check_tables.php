<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo implode("\n", $tables);
