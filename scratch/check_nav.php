<?php
require_once __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';

\App\Core\Database::init($config);
$db = \App\Core\Database::getInstance();

$db->exec("UPDATE nav_links SET open_in_new_tab = 0");
echo "Updated nav_links set open_in_new_tab = 0 successfully.\n";

$stmt = $db->query("SELECT id, label, url, open_in_new_tab FROM nav_links");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
