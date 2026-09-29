<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';

$db = \App\Core\Database::getInstance();
foreach(['products', 'product_gallery', 'product_variants'] as $t) {
    try {
        echo "\nTable: $t\n";
        $cols = $db->query("SHOW COLUMNS FROM $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach($cols as $c) {
            echo $c['Field'] . ' ' . $c['Type'] . "\n";
        }
    } catch (Exception $e) {
        echo "Table not found.\n";
    }
}
