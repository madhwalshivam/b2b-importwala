<?php

namespace App\Services;

use App\Core\Database;

class RateLimiter
{
    public static function tooMany(string $bucket, int $max, int $windowSeconds): bool
    {
        AuthSchema::ensure();
        $db = Database::getInstance();
        $now = time();
        $stmt = $db->prepare("SELECT hits, window_start FROM rate_limit_buckets WHERE bucket_key = ? LIMIT 1");
        $stmt->execute([$bucket]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row || ($now - strtotime($row['window_start'])) >= $windowSeconds) {
            $db->prepare("
                INSERT INTO rate_limit_buckets (bucket_key, hits, window_start)
                VALUES (?, 1, NOW())
                ON DUPLICATE KEY UPDATE hits = 1, window_start = NOW()
            ")->execute([$bucket]);
            return false;
        }

        $hits = (int)$row['hits'] + 1;
        $db->prepare("UPDATE rate_limit_buckets SET hits = ? WHERE bucket_key = ?")->execute([$hits, $bucket]);
        return $hits > $max;
    }

    public static function secondsUntilReset(string $bucket, int $windowSeconds): int
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT window_start FROM rate_limit_buckets WHERE bucket_key = ? LIMIT 1");
            $stmt->execute([$bucket]);
            $start = $stmt->fetchColumn();
            if (!$start) {
                return 0;
            }
            return max(0, $windowSeconds - (time() - strtotime((string)$start)));
        } catch (\Throwable $e) {
            return $windowSeconds;
        }
    }
}
