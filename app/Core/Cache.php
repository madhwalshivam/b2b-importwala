<?php
namespace App\Core;

class Cache {
    protected static string $cacheDir = __DIR__ . '/../../storage/cache/';
    protected static array $memoryCache = [];

    /**
     * Get value from cache or execute fallback closure and cache result
     *
     * @param string $key
     * @param int $ttlSeconds Time to live in seconds (default 300s = 5 mins)
     * @param callable $fallback
     * @return mixed
     */
    public static function remember(string $key, int $ttlSeconds, callable $fallback): mixed {
        if (isset(self::$memoryCache[$key])) {
            return self::$memoryCache[$key];
        }

        $cached = self::get($key);
        if ($cached !== null) {
            self::$memoryCache[$key] = $cached;
            return $cached;
        }

        $value = $fallback();
        self::set($key, $value, $ttlSeconds);
        self::$memoryCache[$key] = $value;
        return $value;
    }

    public static function get(string $key): mixed {
        $file = self::getFilePath($key);
        if (!file_exists($file)) return null;

        $content = @file_get_contents($file);
        if (!$content) return null;

        $data = @unserialize($content);
        if (!$data || !is_array($data) || !isset($data['expires']) || !isset($data['value'])) {
            return null;
        }

        if (time() > $data['expires']) {
            @unlink($file);
            return null;
        }

        return $data['value'];
    }

    public static function set(string $key, mixed $value, int $ttlSeconds = 300): bool {
        if (!is_dir(self::$cacheDir)) {
            @mkdir(self::$cacheDir, 0777, true);
        }

        $file = self::getFilePath($key);
        $data = [
            'expires' => time() + $ttlSeconds,
            'value' => $value
        ];

        return (bool)@file_put_contents($file, serialize($data), LOCK_EX);
    }

    public static function forget(string $key): bool {
        unset(self::$memoryCache[$key]);
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    public static function flush(): bool {
        self::$memoryCache = [];
        if (!is_dir(self::$cacheDir)) return true;

        $files = glob(self::$cacheDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) @unlink($file);
        }
        return true;
    }

    protected static function getFilePath(string $key): string {
        return self::$cacheDir . md5($key) . '.cache';
    }
}
