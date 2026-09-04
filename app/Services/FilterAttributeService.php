<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class FilterAttributeService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get active filter attributes relevant for a given category (or all global ones)
     */
    public function getAttributesForCategory(?int $categoryId = null): array
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
            $sql = "
                SELECT DISTINCT fa.* 
                FROM filter_attributes fa
                WHERE fa.is_active = 1 AND fa.is_global = 1
                ORDER BY fa.sort_order ASC, fa.id ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }

        $attributes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($attributes as &$attr) {
            $optStmt = $this->db->prepare("SELECT * FROM filter_attribute_options WHERE attribute_id = ? AND is_active = 1 ORDER BY sort_order ASC, value ASC");
            $optStmt->execute([$attr['id']]);
            $attr['options'] = $optStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($attr);

        return $attributes;
    }

    /**
     * Get assigned attribute values for a specific product ID
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
                $result[$attrId] = [
                    'option_ids' => [],
                    'values' => [],
                ];
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
     * Save product attribute values for a product
     */
    public function saveProductAttributeValues(int $productId, array $attributeData): void
    {
        // Clear existing product attribute values
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
                $optId = is_numeric($valData) ? (int)$valData : null;
                $textVal = is_numeric($valData) ? null : trim((string)$valData);
                $ins->execute([$productId, $attrId, $optId, $textVal]);
            }
        }
    }

    /**
     * Helper to find or create option under an attribute by string value
     */
    public function getOrCreateOption(int $attributeId, string $value, bool $autoCreate = true): ?int
    {
        $value = trim($value);
        if (empty($value)) return null;

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
        $ins = $this->db->prepare("INSERT INTO filter_attribute_options (attribute_id, value, slug, sort_order) VALUES (?, ?, ?, 99)");
        $ins->execute([$attributeId, $value, $slug]);
        return (int)$this->db->lastInsertId();
    }
}
