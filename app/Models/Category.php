<?php
namespace App\Models;

use App\Core\Model;

class Category extends Model {
    protected string $table = 'categories';

    public function getActiveCategories(): array {
        return $this->where("status = 'active'", [], "sort_order ASC, name ASC");
    }

    public function getFeaturedCategories(): array {
        return $this->where("status = 'active' AND is_featured = 1", [], "sort_order ASC");
    }

    public function getSubcategories(int $categoryId): array {
        $stmt = $this->db->prepare("SELECT * FROM subcategories WHERE category_id = ? AND status = 'active' ORDER BY sort_order ASC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all categories with parent category name and product count
     */
    public function getAllWithDetails(): array {
        $sql = "SELECT c.*, 
                       p.name AS parent_name,
                       (SELECT COUNT(*) FROM products pr WHERE pr.category_id = c.id) AS product_count
                FROM categories c
                LEFT JOIN categories p ON c.parent_id = p.id
                ORDER BY c.sort_order ASC, c.name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Find category with parent name and product count
     */
    public function findWithDetails(int $id): ?array {
        $sql = "SELECT c.*, 
                       p.name AS parent_name,
                       (SELECT COUNT(*) FROM products pr WHERE pr.category_id = c.id) AS product_count
                FROM categories c
                LEFT JOIN categories p ON c.parent_id = p.id
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get product count linked to category
     */
    public function getProductCount(int $categoryId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Reassign products to a new category ID
     */
    public function reassignProducts(int $fromCategoryId, ?int $toCategoryId): bool {
        if ($toCategoryId !== null) {
            $stmt = $this->db->prepare("UPDATE products SET category_id = ? WHERE category_id = ?");
            return $stmt->execute([$toCategoryId, $fromCategoryId]);
        }
        return true;
    }

    /**
     * Check if slug exists (ignoring specified ID for edit)
     */
    public function slugExists(string $slug, int $ignoreId = 0): bool {
        $sql = "SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug, $ignoreId]);
        return ((int)$stmt->fetchColumn()) > 0;
    }
}
