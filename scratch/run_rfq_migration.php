<?php
// Migration runner for RFQ tables
$cfg = require __DIR__ . '/../config/database.php';
$db  = $cfg['connections']['mysql'];

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    getenv('DB_WRITE_HOST') ?: '127.0.0.1',
    $db['port'],
    $db['dbname'],
    $db['charset']
);

try {
    $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);
    $sql = file_get_contents(__DIR__ . '/../database/migrations/rfq.sql');
    $pdo->exec($sql);
    echo "RFQ migration completed successfully.\n";
    echo "Tables created: rfq_requests, rfq_reference_photos\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
