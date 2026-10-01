<?php
namespace App\Helpers;

use App\Core\Database;

class ImageMirror
{
    /**
     * Enqueue a URL for background mirroring.
     * Returns the R2 URL if already mirrored, otherwise returns the original URL.
     */
    public static function enqueue(?string $url): ?string
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
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
            return $url;
        }

        // Insert pending row
        $stmt = $db->prepare("
            INSERT IGNORE INTO image_mirror_queue (source_url, source_hash, status, created_at)
            VALUES (?, ?, 'pending', NOW())
        ");
        $stmt->execute([$url, $hash]);

        return $url;
    }
}
