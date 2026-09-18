<?php
require __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';
\App\Core\Database::init($config);
$db = \App\Core\Database::getInstance();

$tablesStmt = $db->query("SHOW TABLES");
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

echo "=== SCANNING ALL TABLES FOR HTML ENTITIES AND UTF8 SYMBOL ARTIFACTS ===\n";

$entityCount = 0;
foreach ($tables as $t) {
    try {
        $stmt = $db->query("SELECT * FROM `{$t}`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $id = $r['id'] ?? ($r['code'] ?? 'row');
            foreach ($r as $col => $val) {
                if (is_string($val) && strlen($val) > 0) {
                    if (str_contains($val, '&amp;') || str_contains($val, '&#039;') || str_contains($val, '&quot;') || str_contains($val, '&lt;') || str_contains($val, '&gt;') || str_contains($val, '&nbsp;') || preg_match('/[\x80-\xFF]/', $val)) {
                        $entityCount++;
                        $shortVal = mb_strimwidth($val, 0, 100, "...");
                        echo "Table `{$t}` | Col `{$col}` | ID {$id}: {$shortVal}\n";
                    }
                }
            }
        }
    } catch (\Throwable $e) {
        // ignore
    }
}

echo "\nTotal entity/symbol occurrences found in DB: {$entityCount}\n";
