<?php

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Enterprise Database Connection Manager
 * Supports Read/Write Splitting (Primary for writes, Replicas for reads)
 */
class Database
{
    private static ?PDO $writeConnection = null;
    private static ?PDO $readConnection = null;
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    /**
     * Get Write Connection (Primary MySQL)
     */
    public static function getWriteConnection(): PDO
    {
        if (self::$writeConnection === null) {
            self::$writeConnection = self::createPdoInstance('write');
        }
        return self::$writeConnection;
    }

    /**
     * Get Read Connection (Read Replicas MySQL)
     */
    public static function getReadConnection(): PDO
    {
        if (self::$readConnection === null) {
            self::$readConnection = self::createPdoInstance('read');
        }
        return self::$readConnection;
    }

    /**
     * Backward-compatible shorthand (defaults to write connection)
     */
    public static function getInstance(): PDO
    {
        return self::getWriteConnection();
    }

    private static function createPdoInstance(string $type): PDO
    {
        if (empty(self::$config)) {
            self::$config = require __DIR__ . '/../../config/database.php';
        }

        $connConfig = self::$config['connections']['mysql'] ?? [];
        $hosts = $connConfig[$type]['host'] ?? (self::$config['host'] ?? '127.0.0.1');
        if (is_array($hosts)) {
            $host = $hosts[array_rand($hosts)];
        } else {
            $host = $hosts;
        }

        $port = $connConfig['port'] ?? (self::$config['port'] ?? 3306);
        $dbname = $connConfig['dbname'] ?? (self::$config['dbname'] ?? 'ecommerce');
        $user = $connConfig['username'] ?? (self::$config['username'] ?? 'root');
        $pass = $connConfig['password'] ?? (self::$config['password'] ?? '');
        $charset = $connConfig['charset'] ?? (self::$config['charset'] ?? 'utf8mb4');
        $options = $connConfig['options'] ?? (self::$config['options'] ?? []);

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            return $pdo;
        } catch (PDOException $e) {
            // Fallback to write host if read replica connection fails
            if ($type === 'read') {
                return self::getWriteConnection();
            }
            throw new RuntimeException("Database Connection Error ({$type}): " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
