<?php

namespace App\Models;

use App\Core\Model;

/**
 * VariationAttributeValue — global attribute value registry (Red, Blue, S, M, L …)
 */
class VariationAttributeValue extends Model
{
    protected string $table = 'variation_attribute_values';

    /**
     * Find by (attribute_id + value) or insert.
     *
     * @param  int    $attributeId
     * @param  string $value
     * @return int    The attribute_value id
     */
    public function findOrCreate(int $attributeId, string $value): int
    {
        $value = trim($value);

        $stmt = $this->db->prepare(
            "SELECT `id` FROM `variation_attribute_values`
             WHERE `attribute_id` = ? AND LOWER(`value`) = LOWER(?)"
        );
        $stmt->execute([$attributeId, $value]);
        $row = $stmt->fetch();

        if ($row) {
            return (int)$row['id'];
        }

        $ins = $this->db->prepare(
            "INSERT INTO `variation_attribute_values` (`attribute_id`, `value`) VALUES (?, ?)"
        );
        $ins->execute([$attributeId, $value]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Get all attribute values (with attribute names) for a given variant id.
     *
     * @param  int $variantId
     * @return array  [{attribute_name, attribute_type, value, swatch_hex_or_image}]
     */
    public function getForVariant(int $variantId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                va.name               AS attribute_name,
                va.attribute_type,
                vav.id                AS value_id,
                vav.value,
                vav.swatch_hex_or_image
            FROM `product_variation_attribute_map` pvam
            JOIN `variation_attribute_values` vav ON vav.id = pvam.attribute_value_id
            JOIN `variation_attributes`      va  ON va.id  = vav.attribute_id
            WHERE pvam.variation_id = ?
            ORDER BY va.display_order ASC, va.name ASC
        ");
        $stmt->execute([$variantId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Build a human-readable label string for a variant.
     * e.g. "Color: Red, Size: M"
     *
     * @param  int $variantId
     * @return string
     */
    public function getLabelForVariant(int $variantId): string
    {
        $attrs = $this->getForVariant($variantId);
        if (empty($attrs)) {
            return '';
        }
        return implode(', ', array_map(
            fn($a) => $a['attribute_name'] . ': ' . $a['value'],
            $attrs
        ));
    }

    /**
     * Get all attribute_value_ids for a given variant (used for combo matching).
     *
     * @param  int $variantId
     * @return int[]
     */
    public function getValueIdsForVariant(int $variantId): array
    {
        $stmt = $this->db->prepare(
            "SELECT `attribute_value_id` FROM `product_variation_attribute_map` WHERE `variation_id` = ?"
        );
        $stmt->execute([$variantId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
    }

    /**
     * Update swatch hex/image for a value.
     *
     * @param  int    $valueId
     * @param  string $swatchHexOrImage
     * @return bool
     */
    public function updateSwatch(int $valueId, string $swatchHexOrImage): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE `variation_attribute_values` SET `swatch_hex_or_image` = ? WHERE `id` = ?"
        );
        return $stmt->execute([$swatchHexOrImage, $valueId]);
    }
}
