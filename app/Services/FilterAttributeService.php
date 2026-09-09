<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class FilterAttributeService
{
    private PDO $db;

    /** Cache slug → attribute row to avoid repeated DB hits during bulk import */
    private array $slugCache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get active filter attributes relevant for a given category (storefront).
     * Admin-only filters are EXCLUDED.
     */
    public function getAttributesForCategory(?int $categoryId = null): array
    {
        // Auto-sync specifications to filter attributes if product_filter_attribute_values is empty
        try {
            $chk = $this->db->query("SELECT COUNT(*) FROM product_filter_attribute_values")->fetchColumn();
            if ((int)$chk === 0) {
                $this->syncProductSpecificationsToFilterAttributes();
            }
        } catch (\Throwable $e) {}

        if ($categoryId && $categoryId > 0) {
            $sql = "
                SELECT DISTINCT fa.*
                FROM filter_attributes fa
                LEFT JOIN filter_attribute_categories fac ON fa.id = fac.attribute_id
                WHERE fa.is_active = 1
                  AND fa.is_admin_only = 0
                  AND (fa.is_global = 1 OR fac.category_id = ?)
                ORDER BY fa.sort_order ASC, fa.id ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$categoryId]);
        } else {
            $sql = "
                SELECT DISTINCT fa.*
                FROM filter_attributes fa
                WHERE fa.is_active = 1
                  AND fa.is_admin_only = 0
                  AND fa.is_global = 1
                ORDER BY fa.sort_order ASC, fa.id ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }

        $attributes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hydrated = $this->hydrateOptions($attributes);

        // Return only attributes that have options populated
        return array_values(array_filter($hydrated, fn($a) => !empty($a['options'])));
    }

    /**
     * Get active filter attributes including admin-only ones.
     * Used by the admin product edit/create page.
     *
     * @param int|null $categoryId Optional category ID to filter by. If 0 or null, returns all active attributes.
     * @return array
     */
    public function getAttributesForAdmin(?int $categoryId = null): array
    {
        if ($categoryId && $categoryId > 0) {
            $sql = "
                SELECT DISTINCT fa.*
                FROM filter_attributes fa
                LEFT JOIN filter_attribute_categories fac ON fa.id = fac.attribute_id
                WHERE fa.is_active = 1
                  AND (fa.is_global = 1 OR fac.category_id = ?)
                ORDER BY fa.sort_order ASC, fa.id ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$categoryId]);
        } else {
            $stmt = $this->db->query("
                SELECT fa.*
                FROM filter_attributes fa
                WHERE fa.is_active = 1
                ORDER BY fa.sort_order ASC, fa.id ASC
            ");
        }
        $attributes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->hydrateOptions($attributes);
    }

    /**
     * Fast lookup of a single attribute by slug (with in-memory cache).
     * Returns the attribute row with id, name, type etc., or null if not found.
     */
    public function getAttributeBySlug(string $slug): ?array
    {
        if (isset($this->slugCache[$slug])) {
            return $this->slugCache[$slug];
        }

        $stmt = $this->db->prepare("SELECT * FROM filter_attributes WHERE slug = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $this->slugCache[$slug] = $row;
        return $row;
    }

    /**
     * Get assigned attribute values for a specific product ID.
     */
    public function getProductAttributeValues(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT attribute_id, option_id, value
            FROM product_filter_attribute_values
            WHERE product_id = ?
        ");
        $stmt->execute([$productId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            $attrId = (int)$r['attribute_id'];
            if (!isset($result[$attrId])) {
                $result[$attrId] = ['option_ids' => [], 'values' => []];
            }
            if (!empty($r['option_id'])) {
                $result[$attrId]['option_ids'][] = (int)$r['option_id'];
            }
            if (!empty($r['value'])) {
                $result[$attrId]['values'][] = $r['value'];
            }
        }
        return $result;
    }

    /**
     * Save product attribute values (replaces all existing values for the product).
     */
    public function saveProductAttributeValues(int $productId, array $attributeData): void
    {
        $del = $this->db->prepare("DELETE FROM product_filter_attribute_values WHERE product_id = ?");
        $del->execute([$productId]);

        $ins = $this->db->prepare("
            INSERT INTO product_filter_attribute_values (product_id, attribute_id, option_id, value)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($attributeData as $attrId => $valData) {
            $attrId = (int)$attrId;
            if (empty($attrId)) continue;

            if (is_array($valData)) {
                foreach ($valData as $optId) {
                    $optId = (int)$optId;
                    if ($optId > 0) {
                        $ins->execute([$productId, $attrId, $optId, null]);
                    }
                }
            } elseif (!empty($valData)) {
                $optId   = is_numeric($valData) ? (int)$valData : null;
                $textVal = is_numeric($valData) ? null : trim((string)$valData);
                $ins->execute([$productId, $attrId, $optId, $textVal]);
            }
        }
    }

    /**
     * Find or create an option under an attribute by string value.
     * Skips junk/blank values automatically.
     */
    public function getOrCreateOption(int $attributeId, string $value, bool $autoCreate = true): ?int
    {
        $value = trim($value);

        // Skip blanks and known junk values
        $junk = ['', 'n/a', 'none', 'nil', '-', '--', 'na', 'not applicable'];
        if (in_array(strtolower($value), $junk, true)) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT id FROM filter_attribute_options WHERE attribute_id = ? AND LOWER(value) = LOWER(?) LIMIT 1");
        $stmt->execute([$attributeId, $value]);
        $optId = $stmt->fetchColumn();

        if ($optId) {
            return (int)$optId;
        }

        if (!$autoCreate) {
            return null;
        }

        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($value));
        $ins  = $this->db->prepare("INSERT INTO filter_attribute_options (attribute_id, value, slug, sort_order) VALUES (?, ?, ?, 99)");
        $ins->execute([$attributeId, $value, $slug]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Automatically sync specifications from `product_specifications` into `filter_attributes`,
     * `filter_attribute_options`, and `product_filter_attribute_values`.
     *
     * @param int|null $targetProductId Optional product ID to sync. If null, syncs all products.
     * @return int Count of synced attribute values.
     */
    public function syncProductSpecificationsToFilterAttributes(?int $targetProductId = null): int
    {
        $attrStmt = $this->db->query("SELECT * FROM filter_attributes WHERE is_active = 1");
        $existingAttrs = $attrStmt->fetchAll(PDO::FETCH_ASSOC);

        $attrMap = [];
        foreach ($existingAttrs as $a) {
            $attrMap[strtolower(trim($a['name']))] = (int)$a['id'];
            $attrMap[strtolower(trim($a['slug']))] = (int)$a['id'];
        }

        $aliases = [
            'brand'                  => 'brand_name',
            'kind / product type'    => 'kind',
            'material'               => 'material',
            'metal type'             => 'metal_type',
            'metal'                  => 'metal_type',
            'material/metal type'    => 'material',
            'style'                  => 'style',
            'style classification'   => 'style_classification',
            'trendy element'         => 'trendy_element',
            'popular elements'       => 'popular_elements',
        ];

        if ($targetProductId && $targetProductId > 0) {
            $stmt = $this->db->prepare("SELECT * FROM product_specifications WHERE product_id = ?");
            $stmt->execute([$targetProductId]);
            $delStmt = $this->db->prepare("DELETE FROM product_filter_attribute_values WHERE product_id = ?");
            $delStmt->execute([$targetProductId]);
        } else {
            $stmt = $this->db->query("SELECT * FROM product_specifications");
            $delStmt = $this->db->query("DELETE FROM product_filter_attribute_values");
        }

        $allSpecs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($allSpecs)) {
            return 0;
        }

        $insPfav = $this->db->prepare("
            INSERT IGNORE INTO product_filter_attribute_values (product_id, attribute_id, option_id, value)
            VALUES (?, ?, ?, ?)
        ");

        $insertedCount = 0;

        foreach ($allSpecs as $spec) {
            $productId = (int)$spec['product_id'];
            $rawKey = trim($spec['spec_key']);
            $rawValue = trim($spec['spec_value']);

            if (empty($rawKey) || empty($rawValue)) continue;

            $keyLower = strtolower($rawKey);
            $mappedKey = $aliases[$keyLower] ?? $keyLower;

            $attrId = $attrMap[$mappedKey] ?? null;

            if (!$attrId) {
                $slugKey = preg_replace('/[^a-z0-9]+/', '_', $keyLower);
                $attrId = $attrMap[$slugKey] ?? null;
            }

            if (!$attrId && strlen($rawKey) >= 2 && !in_array($keyLower, ['moq (wholesale)', 'package dimensions', 'item net weight', 'weight & profile'])) {
                $slug = preg_replace('/[^a-z0-9]+/', '_', trim(strtolower($rawKey)));
                $insAttr = $this->db->prepare("INSERT INTO filter_attributes (name, slug, type, is_global, is_admin_only, is_active, sort_order) VALUES (?, ?, 'multi_select', 1, 0, 1, 50)");
                $insAttr->execute([$rawKey, $slug]);
                $attrId = (int)$this->db->lastInsertId();
                $attrMap[$keyLower] = $attrId;
                $attrMap[$slug] = $attrId;
            }

            if (!$attrId) continue;

            $vals = [];
            if (strpos($rawValue, '||') !== false) {
                $vals = array_map('trim', explode('||', $rawValue));
            } elseif (in_array($keyLower, ['color', 'metal color', 'size', 'popular elements', 'trendy element']) && strpos($rawValue, ',') !== false) {
                $vals = array_map('trim', explode(',', $rawValue));
            } else {
                $vals = [$rawValue];
            }

            foreach ($vals as $val) {
                if (empty($val)) continue;
                $optId = $this->getOrCreateOption($attrId, $val, true);
                if ($optId) {
                    $insPfav->execute([$productId, $attrId, $optId, $val]);
                    $insertedCount++;
                }
            }
        }

        return $insertedCount;
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /**
     * Hydrate an array of attribute rows with their options sub-array.
     */
    private function hydrateOptions(array $attributes): array
    {
        foreach ($attributes as &$attr) {
            $optStmt = $this->db->prepare(
                "SELECT * FROM filter_attribute_options
                 WHERE attribute_id = ? AND is_active = 1
                 ORDER BY sort_order ASC, value ASC"
            );
            $optStmt->execute([$attr['id']]);
            $attr['options'] = $optStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($attr);
        return $attributes;
    }
}

