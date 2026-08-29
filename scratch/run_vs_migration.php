<?php
$cfg = require __DIR__ . '/../config/database.php';
$db  = $cfg['connections']['mysql'];
$host = $db['write']['host'][0] ?? '127.0.0.1';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $host,
    $db['port'],
    $db['dbname'],
    $db['charset']
);

try {
    $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);
    $sql = file_get_contents(__DIR__ . '/../database/migrations/visual_signatures.sql');
    $pdo->exec($sql);
    echo "Visual signatures migration completed successfully.\n";
} catch (PDOException $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
