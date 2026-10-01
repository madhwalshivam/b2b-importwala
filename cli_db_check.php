<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

require_once __DIR__ . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(__DIR__);
require_once __DIR__ . '/app/Helpers/Functions.php';

try {
    $db = \App\Core\Database::getInstance();
} catch (\Throwable $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

$sqlPath = __DIR__ . '/database/schema.sql';
if (!file_exists($sqlPath)) {
    die("schema.sql not found at $sqlPath\n");
}

$sql = file_get_contents($sqlPath);

// Extract tables and columns using regex tailored to mysqldump format
preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)` \((.*?)\) ENGINE=/s', $sql, $matches);

$expectedTables = [];
foreach ($matches[1] as $idx => $tableName) {
    $columnsRaw = $matches[2][$idx];
    $lines = explode("\n", $columnsRaw);
    $columns = [];
    foreach ($lines as $line) {
        $line = trim($line);
        // Only match column definitions (starts with `column_name`), not keys or constraints
        if (preg_match('/^`([^`]+)`/', $line, $colMatch)) {
            $columns[] = $colMatch[1];
        }
    }
    $expectedTables[$tableName] = $columns;
}

if (empty($expectedTables)) {
    die("Could not parse any tables from schema.sql. Please check the file format.\n");
}

$missingTables = [];
$missingColumns = [];

foreach ($expectedTables as $table => $columns) {
    $check = $db->query("SHOW TABLES LIKE '$table'")->fetchColumn();
    if (!$check) {
        $missingTables[] = $table;
    } else {
        $actualCols = $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
        $diff = array_diff($columns, $actualCols);
        if (!empty($diff)) {
            $missingColumns[$table] = array_values($diff);
        }
    }
}

echo "========================================\n";
echo " DATABASE SCHEMA CHECK\n";
echo "========================================\n";

if (empty($missingTables) && empty($missingColumns)) {
    echo "✅ Database is fully in sync with schema.sql!\n";
} else {
    if (!empty($missingTables)) {
        echo "❌ Missing Tables:\n";
        foreach ($missingTables as $t) {
            echo "   - $t\n";
        }
        echo "\n";
    }
    if (!empty($missingColumns)) {
        echo "⚠️ Missing Columns:\n";
        foreach ($missingColumns as $t => $cols) {
            echo "   - Table `$t` is missing: " . implode(', ', $cols) . "\n";
        }
        echo "\n";
    }
    echo "Please create migrations for the missing items.\n";
}
echo "========================================\n";
