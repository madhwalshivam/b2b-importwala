<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class HomeSection extends Model {
    protected string $table = 'homepage_sections';

    public function getActiveSections(): array {
        return $this->where("status = 'active'", [], "sort_order ASC");
    }

    /**
     * Keys of the 5 product sections managed via Homepage Sections Panel
     */
    public static array $productSectionKeys = [
        'featured_products',
        'best_sellers',
        'new_arrivals'
    ];

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
     * Get all 5 product management sections
     */
    public function getProductSections(): array {
        $placeholders = implode(',', array_fill(0, count(self::$productSectionKeys), '?'));
        $sql = "SELECT * FROM {$this->table} WHERE section_key IN ({$placeholders}) ORDER BY sort_order ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(self::$productSectionKeys);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find section by key or slug
     */
    public function findByKey(string $key): ?array {
        $normalizedKey = self::normalizeKey($key);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE section_key = ? LIMIT 1");
        $stmt->execute([$normalizedKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get products attached to a section with ordering
     */
    public function getSectionProducts(int $sectionId, ?int $limit = null): array {
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
     * Update section metadata (title, subtitle, status/enabled, max_products)
     */
    public function updateSectionConfig(int $sectionId, array $data): bool {
        $updateData = [];
        if (isset($data['title'])) $updateData['title'] = trim($data['title']);
        if (isset($data['subtitle'])) $updateData['subtitle'] = trim($data['subtitle']);
        if (isset($data['status'])) $updateData['status'] = $data['status'] === 'active' || $data['status'] === '1' || $data['status'] === true ? 'active' : 'inactive';
        if (isset($data['enabled'])) $updateData['status'] = !empty($data['enabled']) ? 'active' : 'inactive';
        if (isset($data['max_products'])) $updateData['max_products'] = max(1, (int)$data['max_products']);

        if (empty($updateData)) return true;

        return $this->update($sectionId, $updateData);
    }

    /**
     * Fetch all enabled sections with their selected products for storefront homepage
     */
    public function getEnabledSectionsWithProducts(): array {
        $sections = $this->getProductSections();
        $result = [];

        foreach ($sections as $section) {
            if ($section['status'] === 'active') {
                $max = (int)($section['max_products'] ?: 8);
                $products = $this->getSectionProducts((int)$section['id'], $max);
                
                // Only include if section has products
                if (!empty($products)) {
                    $section['products'] = $products;
                    $result[$section['section_key']] = $section;
                }
            }
        }

        return $result;
    }
}
