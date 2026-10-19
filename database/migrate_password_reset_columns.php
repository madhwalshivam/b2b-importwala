<?php
/**
 * Adds password-reset columns to users (and customers if present).
 * Does not touch Google login columns.
 *
 * Usage: php database/migrate_password_reset_columns.php
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_PATH);
require_once ROOT_PATH . '/app/Helpers/Functions.php';
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();

function columnExists(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function tableExists(PDO $db, string $table): bool
{
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
    ");
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

function addColumn(PDO $db, string $table, string $column, string $definition): void
{
    if (!columnExists($db, $table, $column)) {
        $db->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        echo "Added {$table}.{$column}\n";
        return;
    }
    echo "Skip {$table}.{$column} (already exists)\n";
}

echo "Password reset columns\n";

if (!tableExists($db, 'users')) {
    fwrite(STDERR, "users table not found.\n");
    exit(1);
}

addColumn($db, 'users', 'reset_token', 'VARCHAR(64) NULL DEFAULT NULL');
addColumn($db, 'users', 'reset_token_expires_at', 'DATETIME NULL DEFAULT NULL');

if (tableExists($db, 'customers')) {
    addColumn($db, 'customers', 'reset_token', 'VARCHAR(64) NULL DEFAULT NULL');
    addColumn($db, 'customers', 'reset_token_expires_at', 'DATETIME NULL DEFAULT NULL');
}

echo "Done.\n";
