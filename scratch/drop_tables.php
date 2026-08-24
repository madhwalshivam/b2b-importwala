<?php
define('ROOT_PATH', dirname(__DIR__));
$config = require ROOT_PATH . '/config/database.php';
$connConfig = $config['connections']['mysql'];
$host = $connConfig['write']['host'][0] ?? '127.0.0.1';
$port = $connConfig['port'] ?? 3306;
$user = $connConfig['username'] ?? 'root';
$pass = $connConfig['password'] ?? '';
$dbname = $connConfig['dbname'] ?? 'ecommerce';

$pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $t) {
    try {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
        echo "Dropped `$t`\n";
    } catch (Exception $e) {
        echo "Failed to drop `$t`: " . $e->getMessage() . "\n";
    }
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
