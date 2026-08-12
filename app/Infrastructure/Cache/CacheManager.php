<?php

namespace App\Infrastructure\Cache;

class CacheManager implements CacheInterface
{
    private static ?self $instance = null;
    private mixed $redisClient = null;
    private array $memoryCache = [];
    private string $cacheStoragePath;
    private bool $useRedis = false;

    public function __construct()
    {
        $this->cacheStoragePath = __DIR__ . '/../../../storage/cache';
        if (!is_dir($this->cacheStoragePath)) {
            @mkdir($this->cacheStoragePath, 0777, true);
        }

        // Attempt Redis initialization
        if (class_exists('\Redis')) {
            try {
                $redisConfig = require __DIR__ . '/../../../config/redis.php';
                $client = new \Redis();
                $connected = @$client->connect(
                    $redisConfig['cache']['host'],
                    $redisConfig['cache']['port'],
                    $redisConfig['cache']['timeout']
                );
                if ($connected) {
                    if (!empty($redisConfig['cache']['password'])) {
                        $client->auth($redisConfig['cache']['password']);
                    }
                    $client->select($redisConfig['cache']['database']);
                    $this->redisClient = $client;
                    $this->useRedis = true;
                }
            } catch (\Throwable $e) {
                $this->useRedis = false;
            }
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->useRedis && $this->redisClient) {
            try {
                $val = $this->redisClient->get($key);
                if ($val !== false) {
                    return json_decode($val, true);
                }
                return $default;
            } catch (\Throwable $e) {
                // Failover to file/memory cache
            }
        }

        // Memory / File fallback
        if (array_key_exists($key, $this->memoryCache)) {
            $item = $this->memoryCache[$key];
            if ($item['expires_at'] === 0 || $item['expires_at'] > time()) {
                return $item['value'];
            }
            unset($this->memoryCache[$key]);
        }

        $file = $this->getCacheFilePath($key);
        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content) {
                $data = @unserialize($content);
                if ($data && (isset($data['expires_at']) && ($data['expires_at'] === 0 || $data['expires_at'] > time()))) {
                    $this->memoryCache[$key] = $data;
                    return $data['value'];
                }
            }
            @unlink($file);
        }

        return $default;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        if ($this->useRedis && $this->redisClient) {
            try {
                return $this->redisClient->setex($key, $ttl, json_encode($value));
            } catch (\Throwable $e) {
                // Failover to file/memory
            }
        }

        $expiresAt = $ttl > 0 ? time() + $ttl : 0;
        $payload = [
            'value' => $value,
            'expires_at' => $expiresAt,
        ];
        $this->memoryCache[$key] = $payload;

        $file = $this->getCacheFilePath($key);
        return (bool)@file_put_contents($file, serialize($payload), LOCK_EX);
    }

    public function delete(string $key): bool
    {
        return $this->forget($key);
    }

    public function forget(string $key): bool
    {
        unset($this->memoryCache[$key]);
        if ($this->useRedis && $this->redisClient) {
            try {
                $this->redisClient->del($key);
            } catch (\Throwable $e) {}
        }
        $file = $this->getCacheFilePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
        return true;
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    public function flush(): bool
    {
        $this->memoryCache = [];
        if ($this->useRedis && $this->redisClient) {
            try {
                $this->redisClient->flushDB();
            } catch (\Throwable $e) {}
        }
        $files = glob($this->cacheStoragePath . '/*.cache');
        if ($files) {
            foreach ($files as $f) {
                @unlink($f);
            }
        }
        return true;
    }

    private function getCacheFilePath(string $key): string
    {
        return $this->cacheStoragePath . '/' . md5($key) . '.cache';
    }
}
