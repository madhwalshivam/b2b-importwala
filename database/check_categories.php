<?php
$config = require __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $stmt = $pdo->query("DESCRIBE categories");
    $columns = $stmt->fetchAll();
    echo "COLUMNS:\n";
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
    
    $catStmt = $pdo->query("SELECT * FROM categories");
    echo "\nDATA (" . count($catStmt->fetchAll()) . " rows):\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
