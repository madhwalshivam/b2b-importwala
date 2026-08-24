<?php
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
try {
    $db->exec("CREATE TABLE IF NOT EXISTS _test_table (id INT PRIMARY KEY)");
    echo "Create table: SUCCESS\n";
    $db->exec("DROP TABLE IF EXISTS _test_table");
    echo "Drop table: SUCCESS\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
