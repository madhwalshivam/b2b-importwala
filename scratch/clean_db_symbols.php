<?php
require __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';
\App\Core\Database::init($config);
$db = \App\Core\Database::getInstance();

$tablesStmt = $db->query("SHOW TABLES");
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

echo "=== STARTING DATABASE HTML ENTITY & SYMBOL CLEANUP ===\n";

$cleanedTotal = 0;

foreach ($tables as $table) {
    try {
        // Get columns for this table
        $colsStmt = $db->query("SHOW COLUMNS FROM `{$table}`");
        $columns = $colsStmt->fetchAll(PDO::FETCH_ASSOC);

        $textCols = [];
        $primaryKey = 'id';

        foreach ($columns as $c) {
            $type = strtolower($c['Type']);
            if (str_contains($type, 'char') || str_contains($type, 'text')) {
                $textCols[] = $c['Field'];
            }
            if ($c['Key'] === 'PRI') {
                $primaryKey = $c['Field'];
            }
        }

        if (empty($textCols)) {
            continue;
        }

        $selectCols = array_merge([$primaryKey], $textCols);
        $selectSql = "SELECT `" . implode("`, `", $selectCols) . "` FROM `{$table}`";
        $stmt = $db->query($selectSql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $pkVal = $row[$primaryKey];
            $updates = [];
            $updateParams = [];

            foreach ($textCols as $col) {
                $val = $row[$col];
                if (!is_string($val) || $val === '') {
                    continue;
                }

                // Decode HTML entities safely up to 3 times for multi-encoded strings
                $newVal = $val;
                for ($i = 0; $i < 3; $i++) {
                    if (str_contains($newVal, '&amp;') || str_contains($newVal, '&#039;') || str_contains($newVal, '&quot;') || str_contains($newVal, '&lt;') || str_contains($newVal, '&gt;') || str_contains($newVal, '&nbsp;')) {
                        $newVal = html_entity_decode($newVal, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    } else {
                        break;
                    }
                }

                // Replace common broken UTF-8 byte sequences / artifacts if present
                $newVal = str_replace(
                    ["\xEF\xBB\xBF", "â€™", "â€\"", "â€œ", "â€", "Â"],
                    ["", "'", "-", '"', '"', ""],
                    $newVal
                );

                if ($newVal !== $val) {
                    $updates[] = "`{$col}` = ?";
                    $updateParams[] = $newVal;
                }
            }

            if (!empty($updates)) {
                $updateParams[] = $pkVal;
                $updateSql = "UPDATE `{$table}` SET " . implode(", ", $updates) . " WHERE `{$primaryKey}` = ?";
                $uStmt = $db->prepare($updateSql);
                $uStmt->execute($updateParams);
                $cleanedTotal += count($updates);
                echo "Cleaned table `{$table}` [{$primaryKey}={$pkVal}] (" . count($updates) . " fields updated)\n";
            }
        }
    } catch (\Throwable $e) {
        echo "Error cleaning table `{$table}`: " . $e->getMessage() . "\n";
    }
}

echo "\nCompleted! Total field values cleaned: {$cleanedTotal}\n";
