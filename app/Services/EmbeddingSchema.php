<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Idempotent columns/tables for CLIP image search.
 */
class EmbeddingSchema
{
    private static bool $ready = false;

    public static function ensure(): void
    {
        if (self::$ready) {
            return;
        }
        $db = Database::getInstance();

        $productColumns = [
            'embedding_status' => "ALTER TABLE `products` ADD COLUMN `embedding_status` VARCHAR(20) NULL DEFAULT NULL",
            'embedded_at' => "ALTER TABLE `products` ADD COLUMN `embedded_at` DATETIME NULL DEFAULT NULL",
            'embedding_error' => "ALTER TABLE `products` ADD COLUMN `embedding_error` VARCHAR(500) NULL DEFAULT NULL",
            'embedding_attempts' => "ALTER TABLE `products` ADD COLUMN `embedding_attempts` INT UNSIGNED NOT NULL DEFAULT 0",
            'embedding_source_url' => "ALTER TABLE `products` ADD COLUMN `embedding_source_url` VARCHAR(500) NULL DEFAULT NULL",
        ];
        foreach ($productColumns as $column => $sql) {
            if (!self::hasColumn($db, 'products', $column)) {
                $db->exec($sql);
            }
        }
        if (!self::hasIndex($db, 'products', 'idx_products_embedding_status')) {
            try {
                $db->exec("ALTER TABLE `products` ADD INDEX `idx_products_embedding_status` (`embedding_status`)");
            } catch (\Throwable $e) {
                error_log('[EmbeddingSchema] index: ' . $e->getMessage());
            }
        }

        $db->exec("
            CREATE TABLE IF NOT EXISTS `embedding_jobs` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `product_id` INT UNSIGNED NOT NULL,
              `action` ENUM('upsert','delete') NOT NULL DEFAULT 'upsert',
              `status` ENUM('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
              `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
              `last_error` VARCHAR(500) NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_embedding_jobs_status` (`status`, `id`),
              KEY `idx_embedding_jobs_product` (`product_id`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `image_search_vectors` (
              `token` CHAR(32) NOT NULL,
              `vector_json` LONGTEXT NOT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `expires_at` DATETIME NOT NULL,
              PRIMARY KEY (`token`),
              KEY `idx_image_search_vectors_expires` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::$ready = true;
    }

    private static function hasColumn(PDO $db, string $table, string $column): bool
    {
        $table = str_replace('`', '', $table);
        $column = str_replace(['\\', "'"], ['\\\\', "\\'"], $column);
        $stmt = $db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        return (bool) ($stmt && $stmt->fetch(PDO::FETCH_ASSOC));
    }

    private static function hasIndex(PDO $db, string $table, string $index): bool
    {
        $table = str_replace('`', '', $table);
        $index = str_replace(['\\', "'"], ['\\\\', "\\'"], $index);
        $stmt = $db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'");
        return (bool) ($stmt && $stmt->fetch(PDO::FETCH_ASSOC));
    }
}
