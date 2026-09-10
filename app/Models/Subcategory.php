<?php
namespace App\Models;

use App\Core\Model;

class Subcategory extends Model {
    protected string $table = 'subcategories';

    /**
     * Get all active subcategories
     */
    public function getActiveSubcategories(?int $categoryId = null): array {
        if ($categoryId !== null && $categoryId > 0) {
            $stmt = $this->db->prepare("
                SELECT s.*, c.name as category_name 
                FROM subcategories s
                JOIN categories c ON s.category_id = c.id
                WHERE s.category_id = ? AND s.status = 'active'
                ORDER BY s.sort_order ASC, s.name ASC
            ");
            $stmt->execute([$categoryId]);
            return $stmt->fetchAll() ?: [];
        }

        $stmt = $this->db->query("
            SELECT s.*, c.name as category_name 
            FROM subcategories s
            JOIN categories c ON s.category_id = c.id
            WHERE s.status = 'active'
            ORDER BY s.sort_order ASC, s.name ASC
        ");
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get all subcategories with parent category name and product count
     */
    public function getAllWithCategory(): array {
        $sql = "SELECT s.*, c.name as category_name,
                       (SELECT COUNT(*) FROM products p WHERE p.subcategory_id = s.id) as product_count
                FROM subcategories s
                LEFT JOIN categories c ON s.category_id = c.id
                ORDER BY c.name ASC, s.sort_order ASC, s.name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get subcategories by parent category ID
     */
    public function getByCategoryId(int $categoryId): array {
        $stmt = $this->db->prepare("
            SELECT s.*, c.name as category_name,
                   (SELECT COUNT(*) FROM products p WHERE p.subcategory_id = s.id) as product_count
            FROM subcategories s
            LEFT JOIN categories c ON s.category_id = c.id
            WHERE s.category_id = ?
            ORDER BY s.sort_order ASC, s.name ASC
        ");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Find subcategory by ID with category details
     */
    public function findWithCategory(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT s.*, c.name as category_name,
                   (SELECT COUNT(*) FROM products p WHERE p.subcategory_id = s.id) as product_count
            FROM subcategories s
            LEFT JOIN categories c ON s.category_id = c.id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Check if slug exists in subcategories (ignoring specific subcategory ID)
     */
    public function slugExists(string $slug, int $ignoreId = 0): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM subcategories WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $ignoreId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Count products assigned to this subcategory
     */
    public function getProductCount(int $subcategoryId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE subcategory_id = ?");
        $stmt->execute([$subcategoryId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Create new subcategory
     */
    public function createSubcategory(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO subcategories (category_id, name, slug, image, description, sort_order, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$data['category_id'],
            $data['name'],
            $data['slug'],
            $data['image'] ?? null,
            $data['description'] ?? null,
            (int)($data['sort_order'] ?? 0),
            $data['status'] ?? 'active'
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update existing subcategory
     */
    public function updateSubcategory(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE subcategories SET
                category_id = ?,
                name = ?,
                slug = ?,
                image = ?,
                description = ?,
                sort_order = ?,
                status = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            (int)$data['category_id'],
            $data['name'],
            $data['slug'],
            $data['image'] ?? null,
            $data['description'] ?? null,
            (int)($data['sort_order'] ?? 0),
            $data['status'] ?? 'active',
            $id
        ]);
    }

    /**
     * Delete subcategory
     */
    public function deleteSubcategory(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM subcategories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
