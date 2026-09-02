<?php
namespace App\Models;

use App\Core\Model;

class Testimonial extends Model {
    protected string $table = 'testimonials';

    /**
     * Palette of dark elegant background colors for letter avatars
     */
    public static array $avatarColors = [
        '#1E293B', '#111827', '#2C3E50', '#064E3B', 
        '#312E81', '#1E1B4B', '#881337', '#1E3A8A', 
        '#0F766E', '#374151', '#4C1D95', '#164E63'
    ];

    /**
     * Get active & featured testimonials for Homepage (Everful style)
     */
    public function getFeatured(int $limit = 6): array {
        $stmt = $this->db->prepare("
            SELECT t.*, p.name as product_name, p.slug as product_slug
            FROM testimonials t
            LEFT JOIN products p ON t.product_id = p.id
            WHERE t.status = 'active' AND t.is_featured = 1
            ORDER BY t.display_order ASC, t.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all active testimonials for dedicated /reviews page
     */
    public function getAllActive(int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT t.*, p.name as product_name, p.slug as product_slug
            FROM testimonials t
            LEFT JOIN products p ON t.product_id = p.id
            WHERE t.status = 'active'
            ORDER BY t.display_order ASC, t.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all testimonials for Admin listing with product details
     */
    public function getAllForAdmin(): array {
        $stmt = $this->db->query("
            SELECT t.*, p.name as product_name, p.sku as product_sku
            FROM testimonials t
            LEFT JOIN products p ON t.product_id = p.id
            ORDER BY t.display_order ASC, t.id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Pick a color based on reviewer name
     */
    public static function pickAvatarColor(string $name): string {
        $colors = self::$avatarColors;
        $hash = crc32(trim(strtolower($name)));
        $index = abs($hash) % count($colors);
        return $colors[$index];
    }
}
