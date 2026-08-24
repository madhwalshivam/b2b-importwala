<?php
namespace App\Models;

use App\Core\Model;

class Subcategory extends Model {
    protected string $table = 'categories';

    /**
     * Get all active subcategories
     */
    public function getActiveSubcategories(?int $categoryId = null): array {
        if ($categoryId !== null && $categoryId > 0) {
            $stmt = $this->db->prepare("
                SELECT s.*, c.name as category_name 
                FROM categories s
                JOIN categories c ON s.parent_id = c.id
                WHERE s.parent_id = ? AND s.status = 'active'
                ORDER BY s.sort_order ASC, s.name ASC
            ");
            $stmt->execute([$categoryId]);
            return $stmt->fetchAll() ?: [];
        }

        $stmt = $this->db->query("
            SELECT s.*, c.name as category_name 
            FROM categories s
            JOIN categories c ON s.parent_id = c.id
            WHERE s.parent_id IS NOT NULL AND s.status = 'active'
            ORDER BY s.sort_order ASC, s.name ASC
        ");
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get all subcategories with parent category name and product count
     */
    public function getAllWithCategory(): array {
        $sql = "SELECT s.*, c.name as category_name,
                       (SELECT COUNT(*) FROM products p WHERE p.category_id = s.id) as product_count
                FROM categories s
                LEFT JOIN categories c ON s.parent_id = c.id
                WHERE s.parent_id IS NOT NULL
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
                   (SELECT COUNT(*) FROM products p WHERE p.category_id = s.id) as product_count
            FROM categories s
            LEFT JOIN categories c ON s.parent_id = c.id
            WHERE s.parent_id = ?
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
                   (SELECT COUNT(*) FROM products p WHERE p.category_id = s.id) as product_count
            FROM categories s
            LEFT JOIN categories c ON s.parent_id = c.id
            WHERE s.id = ? AND s.parent_id IS NOT NULL
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Check if slug exists in subcategories or categories (ignoring specific subcategory ID)
     */
    public function slugExists(string $slug, int $ignoreId = 0): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $ignoreId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Count products assigned to this subcategory
     */
    public function getProductCount(int $subcategoryId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$subcategoryId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Create new subcategory
     */
    public function createSubcategory(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO categories (parent_id, name, slug, image, description, sort_order, status, is_featured)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
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
            UPDATE categories SET
                parent_id = ?,
                name = ?,
                slug = ?,
                image = ?,
                description = ?,
                sort_order = ?,
                status = ?
            WHERE id = ? AND parent_id IS NOT NULL
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
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ? AND parent_id IS NOT NULL");
        return $stmt->execute([$id]);
    }
}
