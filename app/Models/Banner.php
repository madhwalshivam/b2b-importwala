<?php
namespace App\Models;

use App\Core\Model;

class Banner extends Model {
    protected string $table = 'banners';

    public function __construct() {
        parent::__construct();
        $this->ensureColumns();
    }

    public function ensureColumns(): void {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM banners");
            $cols = $stmt ? $stmt->fetchAll(\PDO::FETCH_COLUMN) : [];

            if ($cols && !in_array('subtitle', $cols)) {
                $this->db->exec("ALTER TABLE banners ADD COLUMN subtitle VARCHAR(255) NULL AFTER title");
            }
            if ($cols && !in_array('tablet_image_path', $cols)) {
                $this->db->exec("ALTER TABLE banners ADD COLUMN tablet_image_path VARCHAR(255) NULL AFTER image_path");
            }
            if ($cols && !in_array('tablet_image_url', $cols)) {
                $this->db->exec("ALTER TABLE banners ADD COLUMN tablet_image_url VARCHAR(2048) NULL AFTER tablet_image_path");
            }
            if ($cols && !in_array('mobile_image_path', $cols)) {
                $this->db->exec("ALTER TABLE banners ADD COLUMN mobile_image_path VARCHAR(255) NULL AFTER tablet_image_url");
            }
            if ($cols && !in_array('mobile_image_url', $cols)) {
                $this->db->exec("ALTER TABLE banners ADD COLUMN mobile_image_url VARCHAR(2048) NULL AFTER mobile_image_path");
            }
            if ($cols && !in_array('cta_text', $cols)) {
                $this->db->exec("ALTER TABLE banners ADD COLUMN cta_text VARCHAR(100) NULL AFTER link_url");
            }
        } catch (\Throwable $e) {}
    }

    public function getActiveBanners(): array {
        $stmt = $this->db->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll();
    }

    public function getAllBanners(): array {
        $stmt = $this->db->query("SELECT * FROM banners ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO banners
                (title, subtitle, image_path, image_url,
                 tablet_image_path, tablet_image_url,
                 mobile_image_path, mobile_image_url,
                 link_url, cta_text, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title']              ?? '',
            $data['subtitle']           ?? null,
            $data['image_path']         ?? null,
            $data['image_url']          ?? null,
            $data['tablet_image_path']  ?? null,
            $data['tablet_image_url']   ?? null,
            $data['mobile_image_path']  ?? null,
            $data['mobile_image_url']   ?? null,
            $data['link_url']           ?? '',
            $data['cta_text']           ?? null,
            (int)($data['sort_order']   ?? 0),
            (int)($data['is_active']    ?? 0),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(mixed $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE banners SET
                title             = ?,
                subtitle          = ?,
                image_path        = ?,
                image_url         = ?,
                tablet_image_path = ?,
                tablet_image_url  = ?,
                mobile_image_path = ?,
                mobile_image_url  = ?,
                link_url          = ?,
                cta_text          = ?,
                sort_order        = ?,
                is_active         = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['title']              ?? '',
            $data['subtitle']           ?? null,
            $data['image_path']         ?? null,
            $data['image_url']          ?? null,
            $data['tablet_image_path']  ?? null,
            $data['tablet_image_url']   ?? null,
            $data['mobile_image_path']  ?? null,
            $data['mobile_image_url']   ?? null,
            $data['link_url']           ?? '',
            $data['cta_text']           ?? null,
            (int)($data['sort_order']   ?? 0),
            (int)($data['is_active']    ?? 0),
            (int)$id,
        ]);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function delete(mixed $id): bool {
        $stmt = $this->db->prepare("DELETE FROM banners WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /** Desktop banner src (uploaded file takes priority over URL) */
    public static function getImageSrc(array $banner): string {
        if (!empty($banner['image_path'])) {
            return asset('uploads/banners/' . basename($banner['image_path']));
        }
        return $banner['image_url'] ?? '';
    }

    /** Tablet banner src (uploaded file takes priority over URL) */
    public static function getTabletImageSrc(array $banner): ?string {
        if (!empty($banner['tablet_image_path'])) {
            return asset('uploads/banners/' . basename($banner['tablet_image_path']));
        }
        if (!empty($banner['tablet_image_url'])) {
            return $banner['tablet_image_url'];
        }
        return null;
    }

    /** Mobile banner src (uploaded file takes priority over URL) */
    public static function getMobileImageSrc(array $banner): ?string {
        if (!empty($banner['mobile_image_path'])) {
            return asset('uploads/banners/' . basename($banner['mobile_image_path']));
        }
        if (!empty($banner['mobile_image_url'])) {
            return $banner['mobile_image_url'];
        }
        return null;
    }
}
