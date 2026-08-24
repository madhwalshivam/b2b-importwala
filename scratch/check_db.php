<?php
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$tables = ['categories','currencies','inventory_logs','order_items','orders','product_variations','products','tiered_prices','users'];

foreach ($tables as $t) {
    try {
        $stmt = $db->query("SELECT count(*) as cnt FROM `$t`");
        $res = $stmt->fetch();
        echo "Table '$t': OK (" . $res['cnt'] . " rows)\n";
    } catch (Exception $e) {
        echo "Table '$t': ERROR - " . $e->getMessage() . "\n";
    }
}
