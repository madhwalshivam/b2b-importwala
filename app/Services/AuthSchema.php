<?php

namespace App\Services;

use App\Core\Database;

/**
 * Idempotent schema for Google auth, guest views, OTP and rate limits.
 */
class AuthSchema
{
    private static bool $done = false;
    private static array $columnCache = [];

    public static function ensure(): void
    {
        if (self::$done) {
            return;
        }
        self::$done = true;

        try {
            $db = Database::getInstance();
            self::addColumns($db);
            $db->exec("
                CREATE TABLE IF NOT EXISTS guest_product_views (
                  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  guest_token VARCHAR(128) NOT NULL,
                  product_id INT UNSIGNED NOT NULL,
                  first_viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  ip_address VARCHAR(45) NULL,
                  user_agent VARCHAR(255) NULL,
                  PRIMARY KEY (id),
                  UNIQUE KEY uniq_guest_product (guest_token, product_id),
                  KEY idx_guest_token (guest_token)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS phone_otps (
                  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  phone VARCHAR(10) NOT NULL,
                  otp_hash VARCHAR(255) NOT NULL,
                  expires_at DATETIME NOT NULL,
                  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
                  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (id),
                  KEY idx_phone_otp_created (phone, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS rate_limit_buckets (
                  bucket_key VARCHAR(191) NOT NULL,
                  hits INT UNSIGNED NOT NULL DEFAULT 0,
                  window_start DATETIME NOT NULL,
                  PRIMARY KEY (bucket_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $seed = $db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
            $seed->execute(['guest_product_view_limit', '3']);
            $seed->execute(['require_phone_otp', '0']);
            $seed->execute(['google_login_enabled', '1']);
        } catch (\Throwable $e) {
            error_log('Auth schema ensure failed: ' . $e->getMessage());
        }
    }

    private static function addColumns(\PDO $db): void
    {
        $cols = [
            'google_id' => 'VARCHAR(255) NULL DEFAULT NULL',
            'auth_provider' => "VARCHAR(20) NOT NULL DEFAULT 'email'",
            'email_verified' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'phone_verified' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'profile_completed' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'contact_consent' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'consent_at' => 'DATETIME NULL DEFAULT NULL',
            'last_login_at' => 'DATETIME NULL DEFAULT NULL',
            'signup_source' => 'VARCHAR(32) NULL DEFAULT NULL',
            'company_name' => 'VARCHAR(150) NULL DEFAULT NULL',
            'gst_number' => 'VARCHAR(15) NULL DEFAULT NULL',
            'reset_token' => 'VARCHAR(64) NULL DEFAULT NULL',
            'reset_token_expires_at' => 'DATETIME NULL DEFAULT NULL',
        ];
        $existing = self::columns($db, 'users');
        foreach ($cols as $name => $ddl) {
            if (!isset($existing[$name])) {
                $db->exec("ALTER TABLE `users` ADD COLUMN `{$name}` {$ddl}");
            }
        }
        self::$columnCache = [];
        $existing = self::columns($db, 'users');
        if (isset($existing['google_id']) && empty($existing['google_id']['unique'])) {
            try {
                $db->exec("ALTER TABLE `users` ADD UNIQUE KEY `uniq_users_google_id` (`google_id`)");
            } catch (\Throwable $e) {
            }
        }
    }

    public static function columns(\PDO $db, string $table): array
    {
        if (isset(self::$columnCache[$table])) {
            return self::$columnCache[$table];
        }
        $safe = preg_replace('/[^a-z0-9_]/i', '', $table);
        $rows = $db->query("SHOW COLUMNS FROM `{$safe}`")->fetchAll(\PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $row) {
            $out[$row['Field']] = $row;
        }
        $indexes = $db->query("SHOW INDEX FROM `{$safe}`")->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($indexes as $idx) {
            if ((int)($idx['Non_unique'] ?? 1) === 0 && isset($out[$idx['Column_name']])) {
                $out[$idx['Column_name']]['unique'] = true;
            }
        }
        return self::$columnCache[$table] = $out;
    }

    public static function forgetColumns(): void
    {
        // SHOW COLUMNS cache is request-local; nothing to clear across requests.
    }
}
