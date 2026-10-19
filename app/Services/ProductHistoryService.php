<?php

namespace App\Services;

use App\Core\Database;

class ProductHistoryService
{
    public const MAX_ITEMS = 300;

    public static function record($userId, int $productId): void
    {
        $userId = self::userKey($userId);
        if ($userId === '' || $productId <= 0) {
            return;
        }
        try {
            self::ensureTable();
            $db = Database::getInstance();
            $db->prepare("
                INSERT INTO user_product_history (user_id, product_id, last_viewed_at, view_count)
                VALUES (?, ?, NOW(), 1)
                ON DUPLICATE KEY UPDATE
                    last_viewed_at = NOW(),
                    view_count = view_count + 1
            ")->execute([$userId, $productId]);
            self::trim($userId);
        } catch (\Throwable $e) {
            error_log('Product history record failed: ' . $e->getMessage());
        }
    }

    public static function getRecent($userId, int $limit = 300, int $offset = 0): array
    {
        $userId = self::userKey($userId);
        if ($userId === '') {
            return [];
        }
        $limit = max(1, min(self::MAX_ITEMS, $limit));
        $offset = max(0, $offset);
        try {
            self::ensureTable();
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT p.*, h.last_viewed_at, h.view_count
                FROM user_product_history h
                INNER JOIN products p ON p.id = h.product_id
                WHERE h.user_id = ?
                  AND p.status = 'active'
                ORDER BY h.last_viewed_at DESC
                LIMIT {$limit} OFFSET {$offset}
            ");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$row) {
                $row['viewed_label'] = self::viewedLabel($row['last_viewed_at'] ?? null);
            }
            unset($row);
            return $rows;
        } catch (\Throwable $e) {
            error_log('Product history fetch failed: ' . $e->getMessage());
            return [];
        }
    }

    public static function count($userId): int
    {
        $userId = self::userKey($userId);
        if ($userId === '') {
            return 0;
        }
        try {
            self::ensureTable();
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT COUNT(*)
                FROM user_product_history h
                INNER JOIN products p ON p.id = h.product_id
                WHERE h.user_id = ?
                  AND p.status = 'active'
            ");
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public static function clear($userId): void
    {
        $userId = self::userKey($userId);
        if ($userId === '') {
            return;
        }
        try {
            self::ensureTable();
            Database::getInstance()
                ->prepare("DELETE FROM user_product_history WHERE user_id = ?")
                ->execute([$userId]);
        } catch (\Throwable $e) {
            error_log('Product history clear failed: ' . $e->getMessage());
        }
    }

    public static function remove($userId, int $productId): void
    {
        $userId = self::userKey($userId);
        if ($userId === '' || $productId <= 0) {
            return;
        }
        try {
            self::ensureTable();
            Database::getInstance()
                ->prepare("DELETE FROM user_product_history WHERE user_id = ? AND product_id = ?")
                ->execute([$userId, $productId]);
        } catch (\Throwable $e) {
            error_log('Product history remove failed: ' . $e->getMessage());
        }
    }

    public static function mergeFromGuest($userId): void
    {
        $userId = self::userKey($userId);
        if ($userId === '') {
            return;
        }
        try {
            $ids = [];
            if (class_exists(GuestProductViewService::class)) {
                $ids = GuestProductViewService::currentViewedProductIds();
            }
            foreach ($ids as $productId) {
                $pid = (int)$productId;
                if ($pid > 0) {
                    self::record($userId, $pid);
                }
            }
        } catch (\Throwable $e) {
            error_log('Product history guest merge failed: ' . $e->getMessage());
        }
    }

    public static function viewedLabel(?string $at): string
    {
        $ts = $at ? strtotime($at) : false;
        if (!$ts) {
            return 'Viewed recently';
        }
        $days = (int)floor((time() - $ts) / 86400);
        if ($days <= 0) {
            return 'Viewed today';
        }
        if ($days === 1) {
            return 'Viewed yesterday';
        }
        return 'Viewed ' . $days . ' days ago';
    }

    public static function toCardPayload(array $row): array
    {
        $id = (int)($row['id'] ?? 0);
        $slug = (string)($row['slug'] ?? $id);
        $images = [];
        if (function_exists('get_product_images')) {
            try {
                $images = get_product_images($row);
            } catch (\Throwable $e) {
                $images = [];
            }
        }
        $image = $images[0] ?? (function_exists('asset') ? asset($row['main_image'] ?? 'assets/images/placeholder.jpg') : '');
        $base = (float)($row['price'] ?? $row['base_price'] ?? 0);
        $sale = !empty($row['sale_price']) ? (float)$row['sale_price'] : null;
        return [
            'id' => $id,
            'name' => (string)($row['name'] ?? $row['title'] ?? 'Product'),
            'slug' => $slug,
            'url' => function_exists('url') ? url('product/' . $slug) : '/product/' . $slug,
            'image' => $image,
            'price' => $base,
            'sale_price' => $sale,
            'price_label' => function_exists('format_price') ? format_price($sale ?: $base) : (string)($sale ?: $base),
            'viewed_label' => $row['viewed_label'] ?? self::viewedLabel($row['last_viewed_at'] ?? null),
        ];
    }

    private static function trim(string $userId): void
    {
        $db = Database::getInstance();
        $countStmt = $db->prepare("SELECT COUNT(*) FROM user_product_history WHERE user_id = ?");
        $countStmt->execute([$userId]);
        if ((int)$countStmt->fetchColumn() <= self::MAX_ITEMS) {
            return;
        }
        $db->prepare("
            DELETE FROM user_product_history
            WHERE user_id = ?
              AND id NOT IN (
                SELECT id FROM (
                    SELECT id
                    FROM user_product_history
                    WHERE user_id = ?
                    ORDER BY last_viewed_at DESC, id DESC
                    LIMIT 300
                ) keep_rows
              )
        ")->execute([$userId, $userId]);
    }

    private static function userKey($userId): string
    {
        if ($userId === null || $userId === '' || $userId === false) {
            return '';
        }
        $key = trim((string)$userId);
        if ($key === '' || $key === '0') {
            return '';
        }
        return $key;
    }

    private static function ensureTable(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        try {
            $db = Database::getInstance();
            $userType = 'VARCHAR(36) NOT NULL';
            $prodType = 'INT UNSIGNED NOT NULL';
            try {
                $u = $db->query("SHOW COLUMNS FROM `users` LIKE 'id'")->fetch(\PDO::FETCH_ASSOC);
                if (!empty($u['Type']) && preg_match('/^[a-z0-9(),\s]+$/i', (string)$u['Type'])) {
                    $userType = strtoupper((string)$u['Type']) . ' NOT NULL';
                }
                $p = $db->query("SHOW COLUMNS FROM `products` LIKE 'id'")->fetch(\PDO::FETCH_ASSOC);
                if (!empty($p['Type']) && preg_match('/^[a-z0-9(),\s]+$/i', (string)$p['Type'])) {
                    $prodType = strtoupper((string)$p['Type']) . ' NOT NULL';
                }
            } catch (\Throwable $e) {
            }
            $db->exec("
                CREATE TABLE IF NOT EXISTS `user_product_history` (
                  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `user_id` {$userType},
                  `product_id` {$prodType},
                  `last_viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `view_count` INT UNSIGNED NOT NULL DEFAULT 1,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uniq_user_product` (`user_id`, `product_id`),
                  KEY `idx_user_recent` (`user_id`, `last_viewed_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Throwable $e) {
            error_log('Product history ensure table failed: ' . $e->getMessage());
        }
    }
}
