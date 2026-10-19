<?php
/**
 * Adds Google auth columns, guest product views, phone OTP, and settings.
 * Existing users with no valid mobile are marked profile_completed = 0.
 *
 * Usage: php database/migrate_google_auth_guest_views.php
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

function indexExists(PDO $db, string $table, string $index): bool
{
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?
    ");
    $stmt->execute([$table, $index]);
    return (int)$stmt->fetchColumn() > 0;
}

function addColumn(PDO $db, string $table, string $column, string $definition): void
{
    if (!columnExists($db, $table, $column)) {
        $db->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        echo "Added {$table}.{$column}\n";
    }
}

echo "Google auth / guest view migration\n";

$tables = $db->query("SHOW TABLES LIKE 'users'")->fetchAll();
if (!$tables) {
    fwrite(STDERR, "users table not found.\n");
    exit(1);
}

addColumn($db, 'users', 'google_id', "VARCHAR(255) NULL DEFAULT NULL");
addColumn($db, 'users', 'auth_provider', "VARCHAR(20) NOT NULL DEFAULT 'email'");
addColumn($db, 'users', 'email_verified', "TINYINT(1) NOT NULL DEFAULT 0");
addColumn($db, 'users', 'phone_verified', "TINYINT(1) NOT NULL DEFAULT 0");
addColumn($db, 'users', 'profile_completed', "TINYINT(1) NOT NULL DEFAULT 0");
addColumn($db, 'users', 'contact_consent', "TINYINT(1) NOT NULL DEFAULT 0");
addColumn($db, 'users', 'consent_at', "DATETIME NULL DEFAULT NULL");
addColumn($db, 'users', 'last_login_at', "DATETIME NULL DEFAULT NULL");
addColumn($db, 'users', 'signup_source', "VARCHAR(32) NULL DEFAULT NULL");
addColumn($db, 'users', 'company_name', "VARCHAR(150) NULL DEFAULT NULL");

if (columnExists($db, 'users', 'phone')) {
    $rows = $db->query("SELECT id, phone FROM users")->fetchAll(PDO::FETCH_ASSOC);
    $seen = [];
    $update = $db->prepare("UPDATE users SET phone = ? WHERE id = ?");
    foreach ($rows as $row) {
        $digits = preg_replace('/\D+/', '', (string)($row['phone'] ?? ''));
        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        }
        $valid = (bool)preg_match('/^[6-9]\d{9}$/', $digits);
        if (!$valid) {
            $update->execute([null, $row['id']]);
            continue;
        }
        if (isset($seen[$digits])) {
            $update->execute([null, $row['id']]);
            echo "Cleared duplicate phone on user {$row['id']}\n";
            continue;
        }
        $seen[$digits] = $row['id'];
        $update->execute([$digits, $row['id']]);
    }

    $db->exec("ALTER TABLE `users` MODIFY `phone` VARCHAR(10) NULL DEFAULT NULL");
    echo "Normalised users.phone to VARCHAR(10)\n";
}

if (!indexExists($db, 'users', 'uniq_users_google_id')) {
    $db->exec("ALTER TABLE `users` ADD UNIQUE KEY `uniq_users_google_id` (`google_id`)");
    echo "Added unique google_id\n";
}
if (!indexExists($db, 'users', 'uniq_users_phone')) {
    $db->exec("ALTER TABLE `users` ADD UNIQUE KEY `uniq_users_phone` (`phone`)");
    echo "Added unique phone\n";
}

$db->exec("
    UPDATE users
    SET profile_completed = 0
    WHERE phone IS NULL OR phone = '' OR phone NOT REGEXP '^[6-9][0-9]{9}$'
");
$db->exec("
    UPDATE users
    SET profile_completed = 1
    WHERE phone REGEXP '^[6-9][0-9]{9}$'
");
$db->exec("
    UPDATE users
    SET signup_source = 'email'
    WHERE signup_source IS NULL OR signup_source = ''
");
echo "Marked accounts without a valid mobile as profile incomplete\n";

$db->exec("
    CREATE TABLE IF NOT EXISTS `guest_product_views` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `guest_token` VARCHAR(128) NOT NULL,
      `product_id` INT UNSIGNED NOT NULL,
      `first_viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `ip_address` VARCHAR(45) NULL DEFAULT NULL,
      `user_agent` VARCHAR(255) NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_guest_product` (`guest_token`, `product_id`),
      KEY `idx_guest_token` (`guest_token`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$db->exec("
    CREATE TABLE IF NOT EXISTS `phone_otps` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `phone` VARCHAR(10) NOT NULL,
      `otp_hash` VARCHAR(255) NOT NULL,
      `expires_at` DATETIME NOT NULL,
      `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_phone_otp_created` (`phone`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$db->exec("
    CREATE TABLE IF NOT EXISTS `rate_limit_buckets` (
      `bucket_key` VARCHAR(191) NOT NULL,
      `hits` INT UNSIGNED NOT NULL DEFAULT 0,
      `window_start` DATETIME NOT NULL,
      PRIMARY KEY (`bucket_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$seed = $db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
$seed->execute(['guest_product_view_limit', '3']);
$seed->execute(['require_phone_otp', '0']);
$seed->execute(['google_login_enabled', '1']);

echo "Done.\n";
