<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * VariationAttribute — global attribute registry (Color, Size, Material …)
 */
class VariationAttribute extends Model
{
    protected string $table = 'variation_attributes';

    /** @return array All attributes ordered by display_order ASC */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM `variation_attributes` ORDER BY `display_order` ASC, `name` ASC"
        );
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Find by name (case-insensitive) or create.
     *
     * @param  string $name  e.g. "Color", "Size"
     * @param  string $type  'swatch' | 'button' | 'text'
     * @return int           The attribute id
     */
    public function findOrCreate(string $name, string $type = 'button'): int
    {
        $name = trim($name);

        // Special case: Color is always a swatch
        if (strtolower($name) === 'color') {
            $type = 'swatch';
        }

        $stmt = $this->db->prepare(
            "SELECT `id` FROM `variation_attributes` WHERE LOWER(`name`) = LOWER(?)"
        );
        $stmt->execute([$name]);
        $row = $stmt->fetch();

        if ($row) {
            return (int)$row['id'];
        }

        // Insert
        $ins = $this->db->prepare(
            "INSERT INTO `variation_attributes` (`name`, `attribute_type`, `display_order`) VALUES (?, ?, ?)"
        );
        $ins->execute([$name, $type, 10]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * All attribute values for a given attribute id.
     *
     * @param  int $attributeId
     * @return array
     */
    public function getValuesForAttribute(int $attributeId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `variation_attribute_values`
             WHERE `attribute_id` = ?
             ORDER BY `display_order` ASC, `value` ASC"
        );
        $stmt->execute([$attributeId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Return all attributes used by a specific product's variants,
     * with their associated values and swatch data.
     *
     * @param  int $productId
     * @return array  [{id, name, attribute_type, values: [{id, value, swatch_hex_or_image}]}]
     */
    public function getAttributesForProduct(int $productId): array
    {
        $sql = "
            SELECT DISTINCT
                va.id         AS attribute_id,
                va.name       AS attribute_name,
                va.attribute_type,
                va.display_order,
                vav.id        AS value_id,
                vav.value     AS value,
                vav.swatch_hex_or_image,
                vav.display_order AS value_order
            FROM `product_variants` pv
            JOIN `product_variation_attribute_map` pvam ON pvam.variation_id = pv.id
            JOIN `variation_attribute_values` vav       ON vav.id = pvam.attribute_value_id
            JOIN `variation_attributes` va              ON va.id  = vav.attribute_id
            WHERE pv.product_id = ? AND pv.is_active = 1
            ORDER BY va.display_order ASC, va.name ASC, vav.display_order ASC, vav.value ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        $rows = $stmt->fetchAll() ?: [];

        // Group by attribute
        $grouped = [];
        foreach ($rows as $row) {
            $aid = (int)$row['attribute_id'];
            if (!isset($grouped[$aid])) {
                $grouped[$aid] = [
                    'id'             => $aid,
                    'name'           => $row['attribute_name'],
                    'attribute_type' => $row['attribute_type'],
                    'display_order'  => (int)$row['display_order'],
                    'values'         => [],
                ];
            }
            $grouped[$aid]['values'][] = [
                'id'                  => (int)$row['value_id'],
                'value'               => $row['value'],
                'swatch_hex_or_image' => $row['swatch_hex_or_image'],
                'display_order'       => (int)$row['value_order'],
            ];
        }

        return array_values($grouped);
    }
}
