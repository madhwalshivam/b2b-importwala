<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Setting;

class GuestProductViewService
{
    private const COOKIE = 'iw_guest';
    private const VIEW_COOKIE = 'iw_gv';

    public static function limit(): int
    {
        $limit = 3;
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
            $stmt->execute(['guest_product_view_limit']);
            $raw = $stmt->fetchColumn();
            if ($raw !== false && $raw !== null && $raw !== '') {
                $parsed = (int)$raw;
                if ($parsed >= 20 || $parsed === 5) {
                    Setting::set('guest_product_view_limit', '3');
                    $limit = 3;
                } elseif ($parsed > 0) {
                    $limit = $parsed;
                }
            } else {
                Setting::set('guest_product_view_limit', '3');
            }
        } catch (\Throwable $e) {
        }
        return $limit > 0 ? $limit : 3;
    }

    /**
     * @return array{allowed:bool,limited:bool,count:int,limit:int,blocked:bool}
     */
    public static function consume(int $productId): array
    {
        $limit = self::limit();
        if (!empty($_SESSION['user_id']) || SearchBotVerifier::isVerified()) {
            return ['allowed' => true, 'limited' => false, 'count' => 0, 'limit' => $limit, 'blocked' => false];
        }

        $token = self::tokenId();
        $ids = self::viewedIds($token);

        if (isset($ids[$productId])) {
            return ['allowed' => true, 'limited' => true, 'count' => count($ids), 'limit' => $limit, 'blocked' => false];
        }

        if (count($ids) >= $limit) {
            $_SESSION['auth_return'] = self::currentPath();
            $_SESSION['pending_action'] = 'view_product';
            $_SESSION['guest_product_view_count'] = count($ids);
            return ['allowed' => false, 'limited' => true, 'count' => count($ids), 'limit' => $limit, 'blocked' => true];
        }

        $ids[$productId] = time();
        $_SESSION['guest_viewed_ids'] = $ids;
        $_SESSION['guest_product_view_count'] = count($ids);
        self::rememberViewedIds($ids);
        self::persistView($token, $productId);

        return ['allowed' => true, 'limited' => true, 'count' => count($ids), 'limit' => $limit, 'blocked' => false];
    }

    public static function usage(): ?array
    {
        if (!empty($_SESSION['user_id'])) {
            return null;
        }
        $limit = self::limit();
        try {
            $ids = self::viewedIds(self::tokenId());
            return ['count' => count($ids), 'limit' => $limit];
        } catch (\Throwable $e) {
            return [
                'count' => (int)($_SESSION['guest_product_view_count'] ?? 0),
                'limit' => $limit,
            ];
        }
    }

    public static function frontState(): array
    {
        if (!empty($_SESSION['user_id'])) {
            return ['atLimit' => false, 'viewedIds' => []];
        }
        $limit = self::limit();
        try {
            $ids = array_values(array_map('intval', array_keys(self::viewedIds(self::tokenId()))));
        } catch (\Throwable $e) {
            $ids = [];
        }
        return [
            'atLimit' => count($ids) >= $limit,
            'viewedIds' => $ids,
            'limit' => $limit,
        ];
    }

    public static function currentViewedProductIds(): array
    {
        try {
            return array_values(array_map('intval', array_keys(self::viewedIds(self::tokenId()))));
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function bounceUrl(): string
    {
        $app = rtrim((string)env('APP_URL', ''), '/');
        $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
        $here = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';
        if ($app !== '' && $ref !== '' && str_starts_with($ref, $app)) {
            $refPath = parse_url($ref, PHP_URL_PATH) ?: '';
            $blocked = false;
            foreach (['/login', '/signup', '/complete-profile', '/admin', '/logout'] as $skip) {
                if (str_contains($refPath, $skip)) {
                    $blocked = true;
                    break;
                }
            }
            if (!$blocked && $refPath !== '' && $refPath !== $here) {
                return $ref;
            }
        }
        return url('catalog');
    }

    private static function viewedIds(string $token): array
    {
        $ids = [];
        foreach (self::cookieViewIds() as $pid => $_) {
            $ids[$pid] = 1;
        }
        if (isset($_SESSION['guest_viewed_ids']) && is_array($_SESSION['guest_viewed_ids'])) {
            foreach ($_SESSION['guest_viewed_ids'] as $key => $value) {
                $pid = is_int($key) ? $key : (int)$value;
                if ($pid > 0) {
                    $ids[$pid] = 1;
                }
            }
        }
        try {
            AuthSchema::ensure();
            self::widenTokenColumn();
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT product_id FROM guest_product_views WHERE guest_token = ?");
            $stmt->execute([$token]);
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $pid = (int)$row['product_id'];
                if ($pid > 0) {
                    $ids[$pid] = 1;
                }
            }
        } catch (\Throwable $e) {
        }
        $_SESSION['guest_viewed_ids'] = $ids;
        $_SESSION['guest_product_view_count'] = count($ids);
        self::rememberViewedIds($ids);
        return $ids;
    }

    private static function cookieViewIds(): array
    {
        $ids = [];
        $raw = (string)($_COOKIE[self::VIEW_COOKIE] ?? '');
        if ($raw === '') {
            return $ids;
        }
        foreach (explode(',', $raw) as $part) {
            $pid = (int)trim($part);
            if ($pid > 0) {
                $ids[$pid] = 1;
            }
        }
        return $ids;
    }

    private static function rememberViewedIds(array $ids): void
    {
        $list = [];
        foreach ($ids as $key => $value) {
            $pid = is_int($key) && $key > 0 ? $key : (int)$value;
            if ($pid > 0) {
                $list[$pid] = 1;
            }
        }
        $csv = implode(',', array_keys($list));
        if (headers_sent()) {
            return;
        }
        setcookie(self::VIEW_COOKIE, $csv, [
            'expires' => time() + 60 * 60 * 24 * 365,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::VIEW_COOKIE] = $csv;
    }

    private static function persistView(string $token, int $productId): void
    {
        try {
            AuthSchema::ensure();
            self::widenTokenColumn();
            $db = Database::getInstance();
            $ip = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
            $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
            $db->prepare("
                INSERT IGNORE INTO guest_product_views (guest_token, product_id, ip_address, user_agent)
                VALUES (?, ?, ?, ?)
            ")->execute([$token, $productId, $ip, $ua]);
        } catch (\Throwable $e) {
            error_log('Guest product view persist failed: ' . $e->getMessage());
        }
    }

    private static function widenTokenColumn(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        try {
            $db = Database::getInstance();
            $col = $db->query("SHOW COLUMNS FROM guest_product_views LIKE 'guest_token'")->fetch(\PDO::FETCH_ASSOC);
            $type = strtolower((string)($col['Type'] ?? ''));
            if (!str_contains($type, '128') && !str_contains($type, '255')) {
                $db->exec("ALTER TABLE guest_product_views MODIFY guest_token VARCHAR(128) NOT NULL");
            }
            $db->exec("UPDATE guest_product_views SET guest_token = SUBSTRING_INDEX(guest_token, '.', 1) WHERE guest_token LIKE '%.%'");
        } catch (\Throwable $e) {
        }
    }

    private static function tokenId(): string
    {
        $signed = self::signedToken();
        return explode('.', $signed, 2)[0];
    }

    private static function signedToken(): string
    {
        if (!empty($_SESSION['guest_token']) && self::signatureOk((string)$_SESSION['guest_token'])) {
            self::rememberCookie((string)$_SESSION['guest_token']);
            return (string)$_SESSION['guest_token'];
        }

        $raw = (string)($_COOKIE[self::COOKIE] ?? '');
        if ($raw !== '' && self::signatureOk($raw)) {
            $_SESSION['guest_token'] = $raw;
            self::rememberCookie($raw);
            return $raw;
        }

        $public = bin2hex(random_bytes(16));
        $token = $public . '.' . self::sign($public);
        $_SESSION['guest_token'] = $token;
        self::rememberCookie($token);
        return $token;
    }

    private static function signatureOk(string $token): bool
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2 || !preg_match('/^[a-f0-9]{32}$/', $parts[0])) {
            return false;
        }
        return hash_equals(self::sign($parts[0]), $parts[1]);
    }

    private static function sign(string $public): string
    {
        $secret = (string)env('APP_SECRET_KEY', '');
        if ($secret === '') {
            $secret = 'importwale-guest-token';
        }
        return hash_hmac('sha256', $public, $secret);
    }

    private static function rememberCookie(string $token): void
    {
        if (headers_sent()) {
            return;
        }
        setcookie(self::COOKIE, $token, [
            'expires' => time() + 60 * 60 * 24 * 365,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function currentPath(): string
    {
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $query = parse_url($uri, PHP_URL_QUERY);
        $base = rtrim((string)env('APP_BASE_PATH', ''), '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        return ltrim($path, '/') . ($query ? '?' . $query : '');
    }
}
