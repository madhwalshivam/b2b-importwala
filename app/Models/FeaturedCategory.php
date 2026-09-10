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
        // 1. Fetch categories marked as is_featured = 1 in `categories` table
        $catStmt = $this->db->query("
            SELECT id AS real_parent_id,
                   name,
                   slug,
                   COALESCE(NULLIF(image, ''), NULLIF(custom_icon, '')) AS image,
                   sort_order,
                   1 AS is_active
            FROM categories
            WHERE is_featured = 1 AND status = 'active' AND parent_id IS NULL
            ORDER BY sort_order ASC, id ASC
        ");
        $categoriesFromCat = $catStmt->fetchAll() ?: [];

        // 2. Fetch categories from `featured_categories` table
        $fcStmt = $this->db->query("
            SELECT fc.*, 
                   COALESCE(NULLIF(c.image, ''), NULLIF(c.custom_icon, ''), fc.image) AS main_category_image,
                   c.id AS real_parent_id
            FROM featured_categories fc
            LEFT JOIN categories c ON (LOWER(fc.slug) = LOWER(c.slug) OR LOWER(fc.name) = LOWER(c.name)) AND c.parent_id IS NULL
            WHERE fc.is_active = 1 
            ORDER BY fc.sort_order ASC, fc.id ASC
        ");
        $categoriesFromFc = $fcStmt->fetchAll() ?: [];

        // Merge categories (avoid duplicates by lowercase slug/name)
        $mergedMap = [];
        foreach ($categoriesFromCat as $c) {
            $key = strtolower(trim($c['slug'] ?: $c['name']));
            $mergedMap[$key] = [
                'id'                  => $c['real_parent_id'],
                'name'                => $c['name'],
                'slug'                => $c['slug'],
                'image'               => $c['image'],
                'main_category_image' => $c['image'],
                'real_parent_id'      => $c['real_parent_id'],
                'sort_order'          => (int)$c['sort_order'],
                'is_active'           => 1,
            ];
        }

        foreach ($categoriesFromFc as $fc) {
            $key = strtolower(trim($fc['slug'] ?: $fc['name']));
            if (isset($mergedMap[$key])) {
                if (empty($mergedMap[$key]['image']) && !empty($fc['main_category_image'])) {
                    $mergedMap[$key]['image'] = $fc['main_category_image'];
                    $mergedMap[$key]['main_category_image'] = $fc['main_category_image'];
                }
                if (!empty($fc['id'])) {
                    $mergedMap[$key]['fc_id'] = $fc['id'];
                }
            } else {
                $mergedMap[$key] = [
                    'id'                  => $fc['id'],
                    'fc_id'               => $fc['id'],
                    'name'                => $fc['name'],
                    'slug'                => $fc['slug'],
                    'image'               => $fc['main_category_image'] ?: ($fc['image'] ?? null),
                    'main_category_image' => $fc['main_category_image'] ?: ($fc['image'] ?? null),
                    'real_parent_id'      => $fc['real_parent_id'] ?? null,
                    'sort_order'          => (int)($fc['sort_order'] ?? 0),
                    'is_active'           => 1,
                ];
            }
        }

        $allCategories = array_values($mergedMap);
        usort($allCategories, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);

        $result = [];
        foreach ($allCategories as $cat) {
            $subcategories = [];
            $realParentId = $cat['real_parent_id'] ?? null;
            $parentSlug = $cat['slug'] ?? 'catalog';

            // 1. Fetch subcategories from `subcategories` table
            if (!empty($realParentId)) {
                $subStmt = $this->db->prepare("
                    SELECT id, 
                           name, 
                           slug, 
                           image, 
                           sort_order, 
                           status,
                           CONCAT('/category/', ?, '/', slug) AS link_url
                    FROM subcategories 
                    WHERE category_id = ? AND status = 'active' 
                    ORDER BY sort_order ASC, name ASC
                ");
                $subStmt->execute([$parentSlug, $realParentId]);
                $subcategories = $subStmt->fetchAll() ?: [];

                // 2. Fetch subcategories from `categories` table (parent_id = realParentId)
                if (empty($subcategories)) {
                    $subStmt2 = $this->db->prepare("
                        SELECT id, 
                               name, 
                               slug, 
                               image, 
                               sort_order, 
                               status,
                               CONCAT('/category/', ?, '/', slug) AS link_url
                        FROM categories 
                        WHERE parent_id = ? AND status = 'active' 
                        ORDER BY sort_order ASC, name ASC
                    ");
                    $subStmt2->execute([$parentSlug, $realParentId]);
                    $subcategories = $subStmt2->fetchAll() ?: [];
                }
            }

            // 3. Fallback to `featured_subcategories` table if fc_id exists and subcategories still empty
            if (empty($subcategories) && !empty($cat['fc_id'])) {
                $subStmt3 = $this->db->prepare("
                    SELECT * FROM featured_subcategories 
                    WHERE featured_category_id = ? AND is_active = 1 
                    ORDER BY sort_order ASC, id ASC
                ");
                $subStmt3->execute([$cat['fc_id']]);
                $subcategories = $subStmt3->fetchAll() ?: [];
            }

            // Deduplicate subcategories (e.g. Bracelet vs Bracelets)
            if (!empty($subcategories)) {
                $dedupSubs = [];
                foreach ($subcategories as $sub) {
                    $normKey = $this->normalizeSubcategoryKey($sub['name'] ?? '');
                    if (isset($dedupSubs[$normKey])) {
                        if (str_ends_with(strtolower(trim($sub['name'] ?? '')), 's')) {
                            $dedupSubs[$normKey] = $sub;
                        }
                    } else {
                        $dedupSubs[$normKey] = $sub;
                    }
                }
                $subcategories = array_values($dedupSubs);
            }

            // Ensure images & link_urls are filled for subcategories
            foreach ($subcategories as &$sub) {
                if (empty($sub['image'])) {
                    $sub['image'] = $cat['main_category_image'] ?? $cat['image'] ?? null;
                }
                if (empty($sub['link_url'])) {
                    if (!empty($sub['slug'])) {
                        $sub['link_url'] = '/category/' . $parentSlug . '/' . $sub['slug'];
                    } else {
                        $sub['link_url'] = '/catalog?q=' . strtolower(urlencode($sub['name']));
                    }
                }
            }
            unset($sub);

            // Include category tab if it has subcategories
            if (!empty($subcategories)) {
                $cat['subcategories'] = $subcategories;
                $result[] = $cat;
            }
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
