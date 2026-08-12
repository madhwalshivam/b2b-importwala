<?php
namespace App\Models;

use App\Core\Database;

class ProductImage {
    private \PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /** Get all images for a product */
    public function getByProduct(int $productId): array {
        $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** Add image to product gallery */
    public function add(int $productId, string $url): int {
        return $this->insert(['product_id' => $productId, 'image_url' => $url]);
    }

    /** Insert a new image record */
    public function insert(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO product_images (product_id, image_url, image_path, sort_order, is_primary)
            VALUES (?, ?, ?, ?, ?)
        ");
        $imageUrl = $data['image_url'] ?? $data['image_path'] ?? '';
        $stmt->execute([
            $data['product_id'],
            $imageUrl,
            $imageUrl, // keep image_path in sync
            $data['sort_order'] ?? 0,
            $data['is_primary'] ?? 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Set an image as primary */
    public function setPrimary(int $imageId, int $productId): void {
        // Clear all primaries for product
        $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?")->execute([$productId]);
        // Set this one
        $this->db->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?")->execute([$imageId]);

        // Also update products.main_image
        $imgRow = $this->db->prepare("SELECT image_url, image_path FROM product_images WHERE id = ?");
        $imgRow->execute([$imageId]);
        $r = $imgRow->fetch(\PDO::FETCH_ASSOC);
        $mainImage = $r['image_url'] ?: $r['image_path'];
        if ($mainImage) {
            $this->db->prepare("UPDATE products SET main_image = ? WHERE id = ?")->execute([$mainImage, $productId]);
        }
    }

    /** Save reorder (array of image ids in new order) */
    public function reorder(array $imageIds): void {
        $stmt = $this->db->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
        foreach ($imageIds as $order => $id) {
            $stmt->execute([$order, (int)$id]);
        }
    }

    /** Delete an image by id, returns image url for potential file cleanup */
    public function delete(int $imageId): ?string {
        $stmt = $this->db->prepare("SELECT image_url, image_path, product_id, is_primary FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;

        $this->db->prepare("DELETE FROM product_images WHERE id = ?")->execute([$imageId]);

        // If was primary, promote next image
        if ($row['is_primary']) {
            $next = $this->db->prepare("SELECT id FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1");
            $next->execute([$row['product_id']]);
            $nextRow = $next->fetch(\PDO::FETCH_ASSOC);
            if ($nextRow) $this->setPrimary($nextRow['id'], $row['product_id']);
        }

        return $row['image_url'] ?: $row['image_path'];
    }
}
