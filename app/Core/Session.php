<?php
namespace App\Core;

class Session {
    protected const FLASH_KEY = 'flash_messages';

    public function __construct() {
        self::init();
    }

    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Set session GC lifetime to 30 days
            @ini_set('session.gc_maxlifetime', (string)(86400 * 30));
            @ini_set('session.cookie_lifetime', (string)(86400 * 30));

            if (!headers_sent()) {
                session_set_cookie_params([
                    'lifetime' => 86400 * 30,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
            
            if (env('SESSION_DRIVER') === 'redis' && extension_loaded('redis')) {
                try {
                    $redisConfig = require __DIR__ . '/../../config/redis.php';
                    $host = $redisConfig['session']['host'] ?? '127.0.0.1';
                    $port = $redisConfig['session']['port'] ?? 6379;
                    $password = $redisConfig['session']['password'] ?? '';
                    $db = $redisConfig['session']['database'] ?? 2;
                    
                    // Quick test connection to avoid crash on session_start
                    // Use dynamic instantiation to prevent IDE "Unknown class" warnings
                    $redisClass = '\Redis';
                    $redis = new $redisClass();
                    $connected = @$redis->connect($host, $port, 1.5);
                    if ($connected) {
                        if ($password) $redis->auth($password);
                        $redis->select($db);
                        $redis->close(); // Close test connection
                        
                        $savePath = "tcp://$host:$port?database=$db";
                        if ($password) {
                            $savePath .= "&auth=" . urlencode($password);
                        }
                        
                        ini_set('session.save_handler', 'redis');
                        ini_set('session.save_path', $savePath);
                    } else {
                        error_log("WARNING: Redis SESSION_DRIVER selected but connection failed. Falling back to file session.");
                    }
                } catch (\Throwable $e) {
                    error_log("WARNING: Redis session connection exception: " . $e->getMessage() . ". Falling back to file session.");
                }
            }
            @session_start();
        }
    }

    public function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public function setFlash(string $key, string $message): void {
        if (!isset($_SESSION[self::FLASH_KEY])) {
            $_SESSION[self::FLASH_KEY] = [];
        }
        $_SESSION[self::FLASH_KEY][$key] = $message;
    }

    public function getFlash(string $key): ?string {
        if (isset($_SESSION[self::FLASH_KEY][$key])) {
            $val = $_SESSION[self::FLASH_KEY][$key];
            unset($_SESSION[self::FLASH_KEY][$key]);
            if (is_array($val)) {
                return $val['value'] ?? null;
            }
            return (string)$val;
        }
        return null;
    }

    public function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken(?string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
    }
}

