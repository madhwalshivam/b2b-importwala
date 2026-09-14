<?php
namespace App\Models;

use App\Core\Model;

class FeaturedSubcategory extends Model
{
    protected string $table = 'featured_subcategories';

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM featured_subcategories ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll() ?: [];
    }

    public function getActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM featured_subcategories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $items = $stmt->fetchAll() ?: [];

        if (empty($items)) {
            $stmt2 = $this->db->query("
                SELECT s.id, s.name, s.slug, 
                       COALESCE(NULLIF(s.image, ''), NULLIF(c.image, '')) AS image, 
                       CONCAT('/category/', COALESCE(NULLIF(c.slug, ''), 'jewellery'), '/', s.slug) AS link_url, 
                       s.sort_order, 
                       1 AS is_active
                FROM subcategories s
                LEFT JOIN categories c ON (s.category_id = c.id OR s.category_id = c.slug)
                WHERE (s.status = 'active' OR s.status = 'enabled')
                ORDER BY s.sort_order ASC, s.name ASC
            ");
            $items = $stmt2->fetchAll() ?: [];
        }

        return $items;
    }

    public function getByCategory(int $categoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM featured_subcategories 
            WHERE featured_category_id = ? 
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll() ?: [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM featured_subcategories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createSubcategory(array $data): int
    {
        $slug = $this->generateUniqueSlug($data['name']);
        $stmt = $this->db->prepare("
            INSERT INTO featured_subcategories 
            (featured_category_id, name, slug, image, link_url, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int) $data['featured_category_id'],
            $data['name'],
            $slug,
            $data['image'] ?? '',
            $data['link_url'] ?? '',
            (int) ($data['sort_order'] ?? 0),
            isset($data['is_active']) ? (int) $data['is_active'] : 1
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateSubcategory(int $id, array $data): bool
    {
        $slug = !empty($data['name']) ? $this->generateUniqueSlug($data['name'], $id) : null;
        $stmt = $this->db->prepare("
            UPDATE featured_subcategories 
            SET featured_category_id = ?, name = ?, slug = ?, image = ?, link_url = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            (int) $data['featured_category_id'],
            $data['name'],
            $slug,
            $data['image'] ?? '',
            $data['link_url'] ?? '',
            (int) ($data['sort_order'] ?? 0),
            isset($data['is_active']) ? (int) $data['is_active'] : 1,
            $id
        ]);
    }

    public function deleteSubcategory(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM featured_subcategories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateSortOrder(array $orderMap): void
    {
        $stmt = $this->db->prepare("UPDATE featured_subcategories SET sort_order = ? WHERE id = ?");
        foreach ($orderMap as $id => $order) {
            $stmt->execute([(int) $order, (int) $id]);
        }
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $slug = $base;
        $count = 1;

        while (true) {
            if ($ignoreId) {
                $stmt = $this->db->prepare("SELECT id FROM featured_subcategories WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $ignoreId]);
            } else {
                $stmt = $this->db->prepare("SELECT id FROM featured_subcategories WHERE slug = ?");
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
