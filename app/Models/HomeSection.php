<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class HomeSection extends Model {
    protected string $table = 'homepage_sections';

    public function getActiveSections(): array {
        return $this->where("status = 'active'", [], "sort_order ASC, id ASC");
    }

    /**
     * Get all homepage sections ordered by sort_order
     */
    public function getAllSections(): array {
        return $this->db->query("SELECT * FROM {$this->table} ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Map slugs / keys to standard key format
     */
    public static function normalizeKey(string $key): string {
        $key = str_replace('-', '_', strtolower(trim($key)));
        $aliases = [
            'featured' => 'featured_products',
            'deals'    => 'featured_deals',
            'bestsellers' => 'best_sellers',
            'newarrivals' => 'new_arrivals',
            'flashsale'  => 'flash_sale'
        ];
        return $aliases[$key] ?? $key;
    }

    /**
     * Find section by ID, section_key, or slug
     */
    public function findByKey(string $key): ?array {
        if (is_numeric($key) && (int)$key > 0) {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1");
            $stmt->execute([(int)$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) return $row;
        }

        $cleanKey = trim($key);
        $normalizedKey = self::normalizeKey($cleanKey);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE section_key = ? OR slug = ? OR section_key = ? LIMIT 1");
        $stmt->execute([$cleanKey, $cleanKey, $normalizedKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Find section by slug
     */
    public function findBySlug(string $slug): ?array {
        $cleanSlug = slugify($slug);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = ? OR section_key = ? LIMIT 1");
        $stmt->execute([$cleanSlug, str_replace('-', '_', $cleanSlug)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Generate unique slug for a section
     */
    public function generateUniqueSlug(string $title, ?int $ignoreId = null): string {
        $baseSlug = slugify($title);
        if (empty($baseSlug)) {
            $baseSlug = 'section-' . time();
        }

        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $sql = "SELECT id FROM {$this->table} WHERE (slug = ? OR section_key = ?)";
            $params = [$slug, str_replace('-', '_', $slug)];
            if ($ignoreId !== null && $ignoreId > 0) {
                $sql .= " AND id != ?";
                $params[] = $ignoreId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Create a new custom homepage section
     */
    public function createSection(array $data): int {
        $title = trim($data['title'] ?? '');
        $rawSlug = trim($data['slug'] ?? '');
        $slug = !empty($rawSlug) ? slugify($rawSlug) : slugify($title);
        $slug = $this->generateUniqueSlug($slug);

        $subtitle = trim($data['subtitle'] ?? '');
        $maxProducts = max(1, (int)($data['max_products'] ?? 8));
        $displayCount = max(1, (int)($data['homepage_display_count'] ?? 5));
        $sortOrder = (int)($data['sort_order'] ?? 0);
        $status = in_array(strtolower((string)($data['status'] ?? '')), ['active', 'enabled', '1', 'true']) ? 'active' : 'inactive';
        $sectionKey = str_replace('-', '_', $slug);

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (section_key, slug, title, subtitle, max_products, homepage_display_count, sort_order, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$sectionKey, $slug, $title, $subtitle, $maxProducts, $displayCount, $sortOrder, $status]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update section configuration and metadata
     */
    public function updateSectionConfig(int $sectionId, array $data): bool {
        $updateFields = [];
        $params = [];

        if (isset($data['title']) && !empty($data['title'])) {
            $updateFields[] = "title = ?";
            $params[] = trim($data['title']);
        }
        if (isset($data['slug']) && !empty($data['slug'])) {
            $slug = $this->generateUniqueSlug($data['slug'], $sectionId);
            $updateFields[] = "slug = ?";
            $params[] = $slug;
            $updateFields[] = "section_key = ?";
            $params[] = str_replace('-', '_', $slug);
        }
        if (isset($data['subtitle'])) {
            $updateFields[] = "subtitle = ?";
            $params[] = trim($data['subtitle']);
        }
        if (isset($data['max_products'])) {
            $updateFields[] = "max_products = ?";
            $params[] = max(1, (int)$data['max_products']);
        }
        if (isset($data['homepage_display_count'])) {
            $updateFields[] = "homepage_display_count = ?";
            $params[] = max(1, (int)$data['homepage_display_count']);
        }
        if (isset($data['sort_order'])) {
            $updateFields[] = "sort_order = ?";
            $params[] = (int)$data['sort_order'];
        }
        if (isset($data['status']) || isset($data['enabled'])) {
            $rawStatus = $data['status'] ?? $data['enabled'] ?? 'inactive';
            $statusVal = in_array(strtolower((string)$rawStatus), ['active', 'enabled', '1', 'true']) ? 'active' : 'inactive';
            $updateFields[] = "status = ?";
            $params[] = $statusVal;
        }

        if (empty($updateFields)) return true;

        $params[] = $sectionId;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a custom section and its product mappings
     */
    public function deleteSection(int $sectionId): bool {
        $stmtDel = $this->db->prepare("DELETE FROM homepage_section_products WHERE section_id = ?");
        $stmtDel->execute([$sectionId]);
        return $this->delete($sectionId);
    }

    /**
     * Get products attached to a section with ordering (and optional smart fallback for active storefront sections)
     */
    public function getSectionProducts(int $sectionId, ?int $limit = null, bool $useFallback = true): array {
        $limitClause = $limit !== null && $limit > 0 ? "LIMIT {$limit}" : "";
        $sql = "
            SELECT p.*, c.name as category_name, b.name as brand_name, hsp.display_order
            FROM homepage_section_products hsp
            JOIN products p ON hsp.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE hsp.section_id = ? AND p.status = 'active'
            ORDER BY hsp.display_order ASC, hsp.id ASC
            {$limitClause}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sectionId]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($products) && $useFallback) {
            $stmtSec = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1");
            $stmtSec->execute([$sectionId]);
            $section = $stmtSec->fetch(PDO::FETCH_ASSOC);

            if ($section && ($section['status'] === 'active' || $section['status'] === 'enabled')) {
                $key = $section['section_key'] ?? '';
                $fallbackWhere = "p.status = 'active'";

                if ($key === 'featured_products') {
                    $fallbackWhere .= " AND p.is_featured = 1";
                } elseif ($key === 'best_sellers') {
                    $fallbackWhere .= " AND p.is_best_seller = 1";
                } elseif ($key === 'new_arrivals') {
                    $fallbackWhere .= " AND p.is_new_arrival = 1";
                }

                $sqlFallback = "
                    SELECT p.*, c.name as category_name, b.name as brand_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN brands b ON p.brand_id = b.id
                    WHERE {$fallbackWhere}
                    ORDER BY p.id DESC
                    {$limitClause}
                ";
                $stmtFallback = $this->db->prepare($sqlFallback);
                $stmtFallback->execute();
                $products = $stmtFallback->fetchAll(PDO::FETCH_ASSOC);

                // If still empty, grab any active products
                if (empty($products)) {
                    $sqlAny = "
                        SELECT p.*, c.name as category_name, b.name as brand_name
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN brands b ON p.brand_id = b.id
                        WHERE p.status = 'active'
                        ORDER BY p.id DESC
                        {$limitClause}
                    ";
                    $stmtAny = $this->db->prepare($sqlAny);
                    $stmtAny->execute();
                    $products = $stmtAny->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }

        foreach ($products as &$p) {
            $p['main_image'] = asset($p['main_image'] ?? 'assets/images/placeholder.jpg');
        }
        unset($p);

        return $products;
    }

    /**
     * Get paginated products for dedicated View All section page
     */
    public function getSectionProductsPaginated(int $sectionId, int $page = 1, int $perPage = 16): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        // Check total count of mapped products
        $stmtCount = $this->db->prepare("
            SELECT COUNT(DISTINCT p.id) 
            FROM homepage_section_products hsp
            JOIN products p ON hsp.product_id = p.id
            WHERE hsp.section_id = ? AND p.status = 'active'
        ");
        $stmtCount->execute([$sectionId]);
        $total = (int)$stmtCount->fetchColumn();

        if ($total > 0) {
            $sqlData = "
                SELECT p.*, c.name as category_name, b.name as brand_name, hsp.display_order
                FROM homepage_section_products hsp
                JOIN products p ON hsp.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE hsp.section_id = ? AND p.status = 'active'
                ORDER BY hsp.display_order ASC, hsp.id ASC
                LIMIT {$perPage} OFFSET {$offset}
            ";
            $stmtData = $this->db->prepare($sqlData);
            $stmtData->execute([$sectionId]);
            $items = $stmtData->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Fallback for active section with no explicit products assigned
            $section = $this->find($sectionId);
            $key = $section['section_key'] ?? '';
            $fallbackWhere = "p.status = 'active'";

            if ($key === 'featured_products') {
                $fallbackWhere .= " AND p.is_featured = 1";
            } elseif ($key === 'best_sellers') {
                $fallbackWhere .= " AND p.is_best_seller = 1";
            } elseif ($key === 'new_arrivals') {
                $fallbackWhere .= " AND p.is_new_arrival = 1";
            }

            $stmtFallbackCount = $this->db->query("SELECT COUNT(id) FROM products p WHERE {$fallbackWhere}");
            $total = (int)$stmtFallbackCount->fetchColumn();

            $sqlData = "
                SELECT p.*, c.name as category_name, b.name as brand_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE {$fallbackWhere}
                ORDER BY p.id DESC
                LIMIT {$perPage} OFFSET {$offset}
            ";
            $stmtData = $this->db->query($sqlData);
            $items = $stmtData->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach ($items as &$item) {
            $item['main_image'] = asset($item['main_image'] ?? 'assets/images/placeholder.jpg');
        }
        unset($item);

        return [
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => max(1, (int)ceil($total / $perPage))
        ];
    }

    /**
     * Save product IDs for a section in specific order
     */
    public function saveSectionProducts(int $sectionId, array $productIds): bool {
        $this->db->beginTransaction();
        try {
            // Delete existing
            $stmt = $this->db->prepare("DELETE FROM homepage_section_products WHERE section_id = ?");
            $stmt->execute([$sectionId]);

            // Insert new in order
            if (!empty($productIds)) {
                $insertStmt = $this->db->prepare("INSERT INTO homepage_section_products (section_id, product_id, display_order) VALUES (?, ?, ?)");
                $order = 1;
                foreach ($productIds as $pid) {
                    $pid = (int)$pid;
                    if ($pid > 0) {
                        $insertStmt->execute([$sectionId, $pid, $order++]);
                    }
                }
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Fetch all enabled sections with their selected products for storefront homepage
     */
    public function getEnabledSectionsWithProducts(): array {
        $sections = $this->db->query("SELECT * FROM {$this->table} WHERE status = 'active' OR status = 'enabled' ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($sections as $section) {
            $displayCount = (int)($section['homepage_display_count'] ?? 0);
            $limitParam = $displayCount > 0 ? $displayCount : null;
            $products = $this->getSectionProducts((int)$section['id'], $limitParam, true);
            $section['products'] = $products;
            $result[$section['slug'] ?: $section['section_key']] = $section;
        }

        return $result;
    }
}
