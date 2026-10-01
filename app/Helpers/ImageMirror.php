<?php
namespace App\Helpers;

use App\Core\Database;

class ImageMirror
{
    private static bool $workerTriggered = false;

    /**
     * Enqueue a URL for background mirroring.
     * Returns the R2 URL if already mirrored, otherwise returns the original URL.
     */
    public static function enqueue(?string $url): ?string
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        // If the URL is already an R2 URL or local URL, do not enqueue
        if (strpos($url, env('R2_PUBLIC_URL', 'NO_R2_URL')) !== false) {
            return $url;
        }

        $url = trim($url);
        $hash = sha1($url);

        $db = Database::getInstance();
        
        // Check if already in queue
        $stmt = $db->prepare("SELECT status, r2_key FROM image_mirror_queue WHERE source_hash = ? LIMIT 1");
        $stmt->execute([$hash]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            if ($row['status'] === 'done' && !empty($row['r2_key'])) {
                return image_url($row['r2_key']); // Assume image_url handles r2 keys
            }
            self::triggerWorker();
            return $url;
        }

        // Insert pending row
        $stmt = $db->prepare("
            INSERT IGNORE INTO image_mirror_queue (source_url, source_hash, status, created_at)
            VALUES (?, ?, 'pending', NOW())
        ");
        $stmt->execute([$url, $hash]);

        self::triggerWorker();

        return $url;
    }

    /**
     * Triggers the background worker non-blocking:
     *  1. Via PHP CLI exec (works on VPS / local)
     *  2. Falls back to HTTP fire-and-forget (works on shared hosting like Hostinger)
     */
    public static function triggerWorker(): void
    {
        if (self::$workerTriggered) {
            return;
        }
        self::$workerTriggered = true;

        register_shutdown_function(function () {
            // --- Method 1: PHP CLI (VPS / local) ---
            $script = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'cli_mirror_queue.php';
            $phpBin = PHP_BINARY ?: 'php';

            $launched = false;
            if (function_exists('exec') && is_file($script)) {
                try {
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        pclose(popen("start /B \"\" " . escapeshellarg($phpBin) . " " . escapeshellarg($script) . " > NUL 2>&1", "r"));
                    } else {
                        exec(escapeshellarg($phpBin) . " " . escapeshellarg($script) . " > /dev/null 2>&1 &");
                    }
                    $launched = true;
                } catch (\Throwable $e) {
                    // fall through to HTTP method
                }
            }

            // --- Method 2: Non-blocking HTTP (shared hosting fallback) ---
            if (!$launched) {
                try {
                    $secret  = env('WORKER_SECRET', '');
                    $appUrl  = rtrim(env('APP_URL', ''), '/');
                    if (empty($secret) || empty($appUrl)) return;

                    $workerUrl = $appUrl . '/worker.php?token=' . urlencode($secret);

                    // Parse URL to get host/path
                    $parsed = parse_url($workerUrl);
                    $host   = $parsed['host'] ?? '';
                    $path   = ($parsed['path'] ?? '/') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
                    $scheme = $parsed['scheme'] ?? 'https';
                    $port   = $parsed['port'] ?? ($scheme === 'https' ? 443 : 80);

                    // fsockopen with 1s connect timeout — fire and forget
                    if ($scheme === 'https') {
                        $fp = @fsockopen("ssl://{$host}", $port, $errno, $errstr, 1);
                    } else {
                        $fp = @fsockopen($host, $port, $errno, $errstr, 1);
                    }

                    if ($fp) {
                        $request = "GET {$path} HTTP/1.1\r\n"
                            . "Host: {$host}\r\n"
                            . "Connection: close\r\n"
                            . "User-Agent: ImportWala-Worker/1.0\r\n\r\n";
                        fwrite($fp, $request);
                        // Do NOT wait for response — fire and forget
                        stream_set_timeout($fp, 0, 100000); // 0.1s read timeout
                        @fgets($fp, 128); // read just status line then close
                        fclose($fp);
                    }
                } catch (\Throwable $e) {
                    // Ignore — cron will handle it as final fallback
                }
            }
        });
    }
}
