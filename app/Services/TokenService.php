<?php
namespace App\Services;

use App\Core\Database;
use App\Helpers\JWT;

class TokenService {
    
    public static function getJwtSecret(): string {
        return $_ENV['JWT_SECRET'] ?? 'mudsor_secure_jwt_secret_key_2026_x99!';
    }

    /**
     * Issue Access Token (JWT) + Refresh Token (Opaque String) and set HttpOnly Cookies
     */
    public static function issueTokens(int $userId, string $userType = 'customer'): array {
        $db = Database::getInstance();
        $secret = self::getJwtSecret();

        // 1. Generate Access Token (JWT - 30 minutes)
        $accessPayload = [
            'sub' => $userId,
            'user_type' => $userType,
            'iat' => time(),
            'exp' => time() + (30 * 60)
        ];
        $accessToken = JWT::encode($accessPayload, $secret);

        // 2. Generate Refresh Token (Opaque string - 30 days)
        $rawRefreshToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawRefreshToken);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Store hashed refresh token in DB
        $stmt = $db->prepare("
            INSERT INTO refresh_tokens (user_id, user_type, token_hash, expires_at, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $userType, $tokenHash, $expiresAt, $ipAddress, $userAgent]);

        // 3. Set HttpOnly, Secure, SameSite Cookies
        self::setTokenCookies($accessToken, $rawRefreshToken);

        // Log auth event
        self::logAuth('login', $userId, $userType, 'Tokens issued on login');

        return [
            'access_token' => $accessToken,
            'refresh_token' => $rawRefreshToken,
            'expires_in' => 1800
        ];
    }

    /**
     * Rotate Refresh Token & Issue New Access Token with Token Reuse / Theft Detection
     */
    public static function refreshToken(?string $rawRefreshToken): ?array {
        if (empty($rawRefreshToken)) {
            return null;
        }

        $db = Database::getInstance();
        $tokenHash = hash('sha256', $rawRefreshToken);

        $stmt = $db->prepare("SELECT * FROM refresh_tokens WHERE token_hash = ? LIMIT 1");
        $stmt->execute([$tokenHash]);
        $record = $stmt->fetch();

        if (!$record) {
            return null;
        }

        // Theft Detection: If an ALREADY REVOKED token is presented, someone stolen it!
        // Revoke ALL active tokens for this user family as a safety measure.
        if (!empty($record['revoked_at'])) {
            $revokeAllStmt = $db->prepare("UPDATE refresh_tokens SET revoked_at = NOW() WHERE user_id = ? AND user_type = ? AND revoked_at IS NULL");
            $revokeAllStmt->execute([$record['user_id'], $record['user_type']]);
            
            self::logAuth('token_theft_detected', $record['user_id'], $record['user_type'], 'Attempted reuse of revoked refresh token!');
            self::clearCookies();
            return null;
        }

        // Check if expired
        if (strtotime($record['expires_at']) < time()) {
            self::logAuth('token_expired', $record['user_id'], $record['user_type'], 'Expired refresh token used');
            return null;
        }

        // Revoke current refresh token (Rotation)
        $revokeStmt = $db->prepare("UPDATE refresh_tokens SET revoked_at = NOW() WHERE id = ?");
        $revokeStmt->execute([$record['id']]);

        // Issue new token pair
        $newTokens = self::issueTokens($record['user_id'], $record['user_type']);
        self::logAuth('refresh_token', $record['user_id'], $record['user_type'], 'Token rotated successfully');

        return $newTokens;
    }

    /**
     * Revoke all active refresh tokens for a user
     */
    public static function revokeUserTokens(int $userId, string $userType = 'customer'): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE refresh_tokens SET revoked_at = NOW() WHERE user_id = ? AND user_type = ? AND revoked_at IS NULL");
        $stmt->execute([$userId, $userType]);
        
        self::clearCookies();
        self::logAuth('logout', $userId, $userType, 'User logged out and tokens revoked');
    }

    /**
     * Set access and refresh token cookies
     */
    public static function setTokenCookies(string $accessToken, string $refreshToken): void {
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

        // Access Token Cookie (30 minutes)
        setcookie('access_token', $accessToken, [
            'expires'  => time() + 1800,
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        // Refresh Token Cookie (30 days)
        setcookie('refresh_token', $refreshToken, [
            'expires'  => time() + (30 * 86400),
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    /**
     * Clear token cookies
     */
    public static function clearCookies(): void {
        setcookie('access_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true
        ]);
        setcookie('refresh_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true
        ]);
    }

    /**
     * Log authentication events to auth_log table
     */
    public static function logAuth(string $eventType, ?int $userId, string $userType, ?string $details = null): void {
        try {
            $db = Database::getInstance();
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $stmt = $db->prepare("
                INSERT INTO auth_log (user_id, user_type, event_type, ip_address, user_agent, details)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $userType, $eventType, $ipAddress, substr($userAgent, 0, 500), $details]);
        } catch (\Throwable $e) {
            // Silently swallow logger errors to prevent breaking auth flow
        }
    }
}
