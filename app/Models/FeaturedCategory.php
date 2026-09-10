<?php
namespace App\Models;

use App\Core\Model;

class FeaturedCategory extends Model
{
    protected string $table = 'featured_categories';

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM featured_categories ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll() ?: [];
    }

    public function getActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM featured_categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll() ?: [];
    }

    public function getActiveWithSubcategories(): array
    {
        // Fetch all top-level active categories from the database `categories` table
        $catStmt = $this->db->query("
            SELECT id AS real_parent_id,
                   id,
                   name,
                   slug,
                   COALESCE(NULLIF(image, ''), NULLIF(custom_icon, '')) AS image,
                   sort_order,
                   1 AS is_active
            FROM categories
            WHERE (status = 'active' OR status = 'enabled') AND (parent_id IS NULL OR parent_id = 0)
            ORDER BY sort_order ASC, name ASC
        ");
        $mainCategories = $catStmt->fetchAll() ?: [];

        $result = [];
        foreach ($mainCategories as $cat) {
            $realParentId = (int) $cat['id'];
            $parentSlug = !empty($cat['slug']) ? $cat['slug'] : slugify($cat['name']);
            $subcategories = [];

            // 1. Fetch subcategories from `subcategories` table with live product count
            $subStmt = $this->db->prepare("
                SELECT s.id, 
                       s.name, 
                       s.slug, 
                       COALESCE(NULLIF(s.image, ''), NULLIF(c.image, '')) AS image, 
                       s.sort_order, 
                       s.status,
                       CONCAT('/category/', ?, '/', s.slug) AS link_url,
                       (SELECT COUNT(DISTINCT p.id) FROM products p WHERE p.subcategory_id = s.id AND p.status = 'active') AS product_count
                FROM subcategories s
                LEFT JOIN categories c ON s.category_id = c.id
                WHERE s.category_id = ? AND (s.status = 'active' OR s.status = 'enabled')
                ORDER BY s.sort_order ASC, s.name ASC
            ");
            $subStmt->execute([$parentSlug, $realParentId]);
            $rawSub1 = $subStmt->fetchAll() ?: [];

            // 2. Fetch subcategories from `categories` table (parent_id = realParentId)
            $subStmt2 = $this->db->prepare("
                SELECT c.id, 
                       c.name, 
                       c.slug, 
                       COALESCE(NULLIF(c.image, ''), NULLIF(c.custom_icon, '')) AS image, 
                       c.sort_order, 
                       c.status,
                       CONCAT('/category/', ?, '/', c.slug) AS link_url,
                       (SELECT COUNT(DISTINCT p.id) FROM products p WHERE p.category_id = c.id AND p.status = 'active') AS product_count
                FROM categories c
                WHERE c.parent_id = ? AND (c.status = 'active' OR c.status = 'enabled')
                ORDER BY c.sort_order ASC, c.name ASC
            ");
            $subStmt2->execute([$parentSlug, $realParentId]);
            $rawSub2 = $subStmt2->fetchAll() ?: [];

            $mergedSubs = array_merge($rawSub1, $rawSub2);

            // Deduplicate subcategories (e.g. Bracelet vs Bracelets)
            if (!empty($mergedSubs)) {
                $dedupSubs = [];
                foreach ($mergedSubs as $sub) {
                    $normKey = $this->normalizeSubcategoryKey($sub['name'] ?? '');
                    if (isset($dedupSubs[$normKey])) {
                        $existingCount = (int) ($dedupSubs[$normKey]['product_count'] ?? 0);
                        $currentCount = (int) ($sub['product_count'] ?? 0);
                        if ($currentCount > $existingCount) {
                            $dedupSubs[$normKey] = $sub;
                        }
                    } else {
                        $dedupSubs[$normKey] = $sub;
                    }
                }
                $subcategories = array_values($dedupSubs);
            }

            // Ensure image is set for subcategories
            $activeSubcategories = [];
            foreach ($subcategories as $sub) {
                if (empty($sub['image'])) {
                    $sub['image'] = $cat['image'] ?? null;
                }
                $activeSubcategories[] = $sub;
            }

            // Always include all active top-level categories from admin panel
            $cat['subcategories'] = $activeSubcategories;
            $result[] = $cat;
        }

        return $result;
    }

    private function normalizeSubcategoryKey(string $name): string
    {
        $clean = strtolower(trim($name));
        $clean = preg_replace('/[^a-z0-9]+/', '', $clean);
        if (str_ends_with($clean, 'ies') && strlen($clean) > 4) {
            $clean = substr($clean, 0, -3) . 'y';
        } elseif (str_ends_with($clean, 'es') && strlen($clean) > 4) {
            $base = substr($clean, 0, -2);
            if (preg_match('/(ch|sh|x|z|s)$/', $base)) {
                $clean = $base;
            } else {
                $clean = substr($clean, 0, -1);
            }
        } elseif (str_ends_with($clean, 's') && !str_ends_with($clean, 'ss') && strlen($clean) > 3) {
            $clean = substr($clean, 0, -1);
        }
        return $clean;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM featured_categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createCategory(array $data): int
    {
        $slug = $this->generateUniqueSlug($data['name']);
        $stmt = $this->db->prepare("
            INSERT INTO featured_categories (name, slug, sort_order, is_active)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $slug,
            (int) ($data['sort_order'] ?? 0),
            isset($data['is_active']) ? (int) $data['is_active'] : 1
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateCategory(int $id, array $data): bool
    {
        $slug = !empty($data['name']) ? $this->generateUniqueSlug($data['name'], $id) : null;
        $params = [
            $data['name'],
            $slug,
            (int) ($data['sort_order'] ?? 0),
            isset($data['is_active']) ? (int) $data['is_active'] : 1,
            $id
        ];

        $stmt = $this->db->prepare("
            UPDATE featured_categories 
            SET name = ?, slug = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute($params);
    }

    public function deleteCategory(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM featured_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateSortOrder(array $orderMap): void
    {
        $stmt = $this->db->prepare("UPDATE featured_categories SET sort_order = ? WHERE id = ?");
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
