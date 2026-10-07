<?php

namespace App\Core;

/**
 * Optional request timing / SQL logging for PDP diagnostics.
 * Enable with ?debug=1 when APP_DEBUG=true or an admin session is present.
 */
class PerfDebug
{
    private static bool $enabled = false;
    private static float $requestStart = 0.0;
    private static array $blocks = [];
    private static array $queries = [];
    private static float $queryTotalMs = 0.0;
    private static ?\PDO $pdo = null;
    private static bool $pdoWrapped = false;

    public static function bootIfRequested(): void
    {
        if (self::$enabled) {
            return;
        }

        $wantsDebug = isset($_GET['debug']) && (string) $_GET['debug'] === '1';
        if (!$wantsDebug) {
            return;
        }

        $allowed = false;
        if (filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN)) {
            $allowed = true;
        }
        if (!$allowed && !empty($_SESSION['admin_id'])) {
            $allowed = true;
        }
        if (!$allowed && !empty($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin'], true)) {
            $allowed = true;
        }
        if (!$allowed) {
            return;
        }

        self::$enabled = true;
        self::$requestStart = microtime(true);
    }

    public static function enabled(): bool
    {
        return self::$enabled;
    }

    public static function mark(string $label, float $startedAt): void
    {
        if (!self::$enabled) {
            return;
        }
        self::$blocks[] = [
            'label' => $label,
            'ms' => round((microtime(true) - $startedAt) * 1000, 2),
        ];
    }

    public static function logQuery(string $sql, float $ms): void
    {
        if (!self::$enabled) {
            return;
        }
        self::$queries[] = [
            'sql' => preg_replace('/\s+/', ' ', trim($sql)) ?? $sql,
            'ms' => round($ms, 2),
        ];
        self::$queryTotalMs += $ms;
    }

    /**
     * Wrap PDO prepare/execute to log SQL timings (best-effort; only when debug on).
     */
    public static function instrumentPdo(\PDO $pdo): void
    {
        if (!self::$enabled || self::$pdoWrapped) {
            return;
        }
        self::$pdo = $pdo;
        self::$pdoWrapped = true;
        // Actual wrapping is done via Database query helper when needed;
        // controllers also call mark() around major blocks.
    }

    public static function renderHtmlComment(): string
    {
        if (!self::$enabled) {
            return '';
        }

        $total = round((microtime(true) - self::$requestStart) * 1000, 2);
        $blocks = self::$blocks;
        usort($blocks, static fn($a, $b) => $b['ms'] <=> $a['ms']);

        $lines = [];
        $lines[] = 'PDP PERF DEBUG';
        $lines[] = "total_ms={$total}";
        $lines[] = 'sql_count=' . count(self::$queries);
        $lines[] = 'sql_total_ms=' . round(self::$queryTotalMs, 2);
        $lines[] = '--- blocks ---';
        foreach ($blocks as $b) {
            $lines[] = sprintf('%7.1f ms  %s', $b['ms'], $b['label']);
        }

        $slow = self::$queries;
        usort($slow, static fn($a, $b) => $b['ms'] <=> $a['ms']);
        $lines[] = '--- slowest queries (top 15) ---';
        foreach (array_slice($slow, 0, 15) as $q) {
            $lines[] = sprintf('%7.1f ms  %s', $q['ms'], substr($q['sql'], 0, 200));
        }

        return "\n<!--\n" . htmlspecialchars(implode("\n", $lines), ENT_QUOTES, 'UTF-8') . "\n-->\n";
    }

    public static function summary(): array
    {
        return [
            'total_ms' => self::$enabled ? round((microtime(true) - self::$requestStart) * 1000, 2) : 0,
            'blocks' => self::$blocks,
            'sql_count' => count(self::$queries),
            'sql_total_ms' => round(self::$queryTotalMs, 2),
            'queries' => self::$queries,
        ];
    }
}
