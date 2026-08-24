<?php
namespace App\Models;

use App\Core\Model;

class FeaturedCategory extends Model {
    protected string $table = 'featured_categories';

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM featured_categories ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll() ?: [];
    }

    public function getActive(): array {
        $stmt = $this->db->query("SELECT * FROM featured_categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll() ?: [];
    }

    public function getActiveWithSubcategories(): array {
        $stmt = $this->db->query("
            SELECT fc.*, 
                   COALESCE(NULLIF(c.image, ''), NULLIF(c.custom_icon, ''), fc.image) AS main_category_image,
                   c.id AS real_parent_id
            FROM featured_categories fc
            LEFT JOIN categories c ON (LOWER(fc.slug) = LOWER(c.slug) OR LOWER(fc.name) = LOWER(c.name)) AND c.parent_id IS NULL
            WHERE fc.is_active = 1 
            ORDER BY fc.sort_order ASC, fc.id ASC
        ");
        $categories = $stmt->fetchAll() ?: [];
        if (empty($categories)) {
            return [];
        }

        $result = [];
        foreach ($categories as $cat) {
            $subcategories = [];

            // 1. Fetch live subcategories from categories table if parent category exists
            if (!empty($cat['real_parent_id'])) {
                $subStmt = $this->db->prepare("
                    SELECT id, 
                           name, 
                           slug, 
                           image, 
                           sort_order, 
                           status,
                           CONCAT('/catalog?q=', LOWER(REPLACE(name, ' ', '-'))) AS link_url
                    FROM categories 
                    WHERE parent_id = ? AND status = 'active' 
                    ORDER BY sort_order ASC, name ASC
                ");
                $subStmt->execute([$cat['real_parent_id']]);
                $subcategories = $subStmt->fetchAll() ?: [];
            }

            // 2. Fallback to featured_subcategories table if no live subcategories in categories table
            if (empty($subcategories)) {
                $subStmt = $this->db->prepare("
                    SELECT * FROM featured_subcategories 
                    WHERE featured_category_id = ? AND is_active = 1 
                    ORDER BY sort_order ASC, id ASC
                ");
                $subStmt->execute([$cat['id']]);
                $subcategories = $subStmt->fetchAll() ?: [];
            }

            // Only include category tab if it has subcategories
            if (!empty($subcategories)) {
                $cat['subcategories'] = $subcategories;
                $result[] = $cat;
            }
        }

        return $result;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM featured_categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createCategory(array $data): int {
        $slug = $this->generateUniqueSlug($data['name']);
        $stmt = $this->db->prepare("
            INSERT INTO featured_categories (name, slug, sort_order, is_active)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $slug,
            (int)($data['sort_order'] ?? 0),
            isset($data['is_active']) ? (int)$data['is_active'] : 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateCategory(int $id, array $data): bool {
        $slug = !empty($data['name']) ? $this->generateUniqueSlug($data['name'], $id) : null;
        $params = [
            $data['name'],
            $slug,
            (int)($data['sort_order'] ?? 0),
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $id
        ];

        $stmt = $this->db->prepare("
            UPDATE featured_categories 
            SET name = ?, slug = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute($params);
    }

    public function deleteCategory(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM featured_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateSortOrder(array $orderMap): void {
        $stmt = $this->db->prepare("UPDATE featured_categories SET sort_order = ? WHERE id = ?");
        foreach ($orderMap as $id => $order) {
            $stmt->execute([(int)$order, (int)$id]);
        }
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $slug = $base;
        $count = 1;

        while (true) {
            if ($ignoreId) {
                $stmt = $this->db->prepare("SELECT id FROM featured_categories WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $ignoreId]);
            } else {
                $stmt = $this->db->prepare("SELECT id FROM featured_categories WHERE slug = ?");
                $stmt->execute([$slug]);
            }

            if (!$stmt->fetch()) {
                break;
            }
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}
