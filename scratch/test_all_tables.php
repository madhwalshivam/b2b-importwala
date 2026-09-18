<?php
require __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';
\App\Core\Database::init($config);
$db = \App\Core\Database::getInstance();

$tablesStmt = $db->query("SHOW TABLES");
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

echo "Tables in DB: " . implode(', ', $tables) . "\n\n";

foreach ($tables as $t) {
    try {
        $stmt = $db->query("SELECT * FROM `{$t}`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            foreach ($r as $col => $val) {
                if (is_string($val)) {
                    if (str_contains($val, '&amp;') || str_contains($val, '&#039;') || str_contains($val, '&quot;') || str_contains($val, 'â') || str_contains($val, 'Ã') || str_contains($val, 'Â')) {
                        echo "Table `{$t}`, Col `{$col}`, Row ID " . ($r['id'] ?? '?') . ": {$val}\n";
                    }
                }
            }
        }
    } catch (\Throwable $e) {
        // ignore
    }
}
