<?php

namespace App\Services;

/**
 * Lets verified search-engine crawlers read product pages.
 * A matching user-agent is not enough; the IP must reverse-resolve
 * to the vendor's domain and forward-confirm back to the same IP.
 */
class SearchBotVerifier
{
    private const BOTS = [
        'googlebot' => ['googlebot.com', 'google.com'],
        'google-inspectiontool' => ['googlebot.com', 'google.com'],
        'adsbot-google' => ['googlebot.com', 'google.com'],
        'bingbot' => ['search.msn.com'],
        'duckduckbot' => ['duckduckgo.com'],
        'slurp' => ['crawl.yahoo.net'],
        'baiduspider' => ['baidu.com', 'baidu.jp'],
        'yandexbot' => ['yandex.ru', 'yandex.net', 'yandex.com'],
    ];

    public static function isVerified(): bool
    {
        $ua = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $suffixes = [];
        foreach (self::BOTS as $needle => $domains) {
            if (str_contains($ua, $needle)) {
                $suffixes = array_merge($suffixes, $domains);
            }
        }
        if (!$suffixes) {
            return false;
        }

        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        $cacheFile = dirname(__DIR__, 2) . '/storage/cache/verified_bots.json';
        $cache = [];
        if (is_file($cacheFile)) {
            $cache = json_decode((string)file_get_contents($cacheFile), true) ?: [];
            if (isset($cache[$ip]) && ($cache[$ip]['exp'] ?? 0) > time()) {
                return (bool)$cache[$ip]['ok'];
            }
        }

        $ok = self::reverseDns($ip, $suffixes);
        $cache[$ip] = ['ok' => $ok, 'exp' => time() + 86400];
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($cacheFile, json_encode($cache));
        return $ok;
    }

    private static function reverseDns(string $ip, array $suffixes): bool
    {
        $host = @gethostbyaddr($ip);
        if (!$host || $host === $ip) {
            return false;
        }
        $host = strtolower(rtrim($host, '.'));
        $matched = false;
        foreach ($suffixes as $suffix) {
            $suffix = strtolower($suffix);
            if ($host === $suffix || str_ends_with($host, '.' . $suffix)) {
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            return false;
        }
        $forward = @gethostbyname($host);
        if ($forward === $ip) {
            return true;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_AAAA);
            if (is_array($records)) {
                foreach ($records as $record) {
                    if (!empty($record['ipv6']) && strcasecmp($record['ipv6'], $ip) === 0) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
