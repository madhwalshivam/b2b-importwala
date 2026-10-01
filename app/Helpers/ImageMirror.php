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
     * Safely triggers the background worker script via exec (non-blocking).
     */
    public static function triggerWorker(): void
    {
        if (self::$workerTriggered) {
            return;
        }
        self::$workerTriggered = true;

        register_shutdown_function(function () {
            try {
                $script = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'cli_mirror_queue.php';
                
                // If on Windows
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    pclose(popen("start /B php " . escapeshellarg($script) . " > NUL 2>&1", "r"));
                } else {
                    // Linux/Hostinger
                    exec("php " . escapeshellarg($script) . " > /dev/null 2>&1 &");
                }
            } catch (\Throwable $e) {
                // Ignore, let cron handle it as fallback
            }
        });
    }
}
