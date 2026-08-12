<?php
namespace App\Core;

class Auth {
    private static ?array $user = null;
    private static ?array $permissions = null;

    public static function check(): bool {
        Session::init();
        if (empty($_SESSION['admin_user_id'])) {
            self::attemptTokenRestoration();
        }
        return !empty($_SESSION['admin_user_id']);
    }

    private static function attemptTokenRestoration(): void {
        $refreshToken = $_COOKIE['refresh_token'] ?? null;
        if (empty($refreshToken)) {
            return;
        }

        try {
            $db = Database::getInstance();
            $tokenHash = hash('sha256', $refreshToken);
            $stmt = $db->prepare("SELECT user_id FROM refresh_tokens WHERE token_hash = ? AND user_type = 'admin' AND revoked_at IS NULL AND expires_at > NOW() LIMIT 1");
            $stmt->execute([$tokenHash]);
            $record = $stmt->fetch();

            if ($record && !empty($record['user_id'])) {
                $userStmt = $db->prepare("
                    SELECT u.*, r.name as role_name, r.slug as role_slug 
                    FROM admin_users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE u.id = ? AND u.status = 'active'
                    LIMIT 1
                ");
                $userStmt->execute([$record['user_id']]);
                $user = $userStmt->fetch();

                if ($user) {
                    $_SESSION['admin_user_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_role_id'] = $user['role_id'];
                    self::$user = $user;
                }
            }
        } catch (\Throwable $e) {
            // Silently swallow errors if table missing
        }
    }

    public static function user(): ?array {
        if (self::$user !== null) {
            return self::$user;
        }

        if (!self::check()) {
            return null;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT u.*, r.name as role_name, r.slug as role_slug 
            FROM admin_users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = ? AND u.status = 'active'
        ");
        $stmt->execute([$_SESSION['admin_user_id']]);
        $user = $stmt->fetch();

        if ($user) {
            self::$user = $user;
            self::loadPermissions($user['role_id']);
            return self::$user;
        }

        self::logout();
        return null;
    }

    private static function loadPermissions(int $roleId): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.key_code, p.module 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ");
        $stmt->execute([$roleId]);
        self::$permissions = $stmt->fetchAll();
    }

    public static function hasPermission(string $keyCode): bool {
        $user = self::user();
        if (!$user) return false;
        // Super admin has all permissions
        if ($user['role_slug'] === 'super-admin') return true;

        if (self::$permissions === null) {
            self::loadPermissions($user['role_id']);
        }

        foreach (self::$permissions as $perm) {
            if ($perm['key_code'] === $keyCode) {
                return true;
            }
        }
        return false;
    }

    public static function canAccessModule(string $moduleName): bool {
        $user = self::user();
        if (!$user) return false;
        if ($user['role_slug'] === 'super-admin') return true;

        if (self::$permissions === null) {
            self::loadPermissions($user['role_id']);
        }

        foreach (self::$permissions as $perm) {
            if ($perm['module'] === $moduleName) {
                return true;
            }
        }
        return false;
    }

    public static function login(array $user): void {
        Session::init();
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['name'] ?? '';
        $_SESSION['admin_email'] = $user['email'] ?? '';
        $_SESSION['admin_role_id'] = $user['role_id'] ?? null;
        self::$user = $user;
        activity_log('Login', 'Auth', $user['id'], 'Logged in to Admin Panel');
    }

    public static function logout(): void {
        Session::init();
        if (!empty($_SESSION['admin_user_id'])) {
            activity_log('Logout', 'Auth', $_SESSION['admin_user_id'], 'Logged out of Admin Panel');
        }
        unset($_SESSION['admin_user_id']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_role_id']);
        self::$user = null;
        self::$permissions = null;
    }
}

