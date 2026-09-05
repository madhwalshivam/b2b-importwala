<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
foreach(['inquiries', 'inquiry_items'] as $t) {
    echo "=== {$t} ===\n";
    try {
        $st = $db->query("DESCRIBE {$t}");
        foreach($st->fetchAll(PDO::FETCH_ASSOC) as $c) {
            echo "{$c['Field']} ({$c['Type']})\n";
        }
    } catch (\Exception $e) {
        echo "Error describing {$t}: " . $e->getMessage() . "\n";
    }
}
