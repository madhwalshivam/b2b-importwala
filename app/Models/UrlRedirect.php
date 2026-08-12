<?php
namespace App\Models;

use App\Core\Database;

class UrlRedirect {
    private \PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByOldSlug(string $oldSlug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM url_redirects WHERE old_slug = ? LIMIT 1");
        $stmt->execute([$oldSlug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createOrUpdate(string $oldSlug, string $targetUrl, int $httpCode = 301): void {
        $stmt = $this->db->prepare("
            INSERT INTO url_redirects (old_slug, target_url, http_code)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE target_url = VALUES(target_url), http_code = VALUES(http_code)
        ");
        $stmt->execute([$oldSlug, $targetUrl, $httpCode]);
    }

    public function deleteByOldSlug(string $oldSlug): void {
        $stmt = $this->db->prepare("DELETE FROM url_redirects WHERE old_slug = ?");
        $stmt->execute([$oldSlug]);
    }

    public function deleteByTargetUrl(string $targetUrl): void {
        $stmt = $this->db->prepare("DELETE FROM url_redirects WHERE target_url = ?");
        $stmt->execute([$targetUrl]);
    }
}
