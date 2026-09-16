<?php
namespace App\Models;

use App\Core\Model;

class ProductVariant extends Model {
    protected string $table = 'product_variants';

    /**
     * Get all variants for a given product ID, enriched with their attribute values.
     */
    public function getByProduct(int $productId, bool $activeOnly = true): array {
        $sql = "SELECT * FROM `{$this->table}` WHERE `product_id` = ?";
        if ($activeOnly) {
            $sql .= " AND `is_active` = 1";
        }
        $sql .= " ORDER BY `sort_order` ASC, `id` ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        $variants = $stmt->fetchAll() ?: [];

        // Enrich each variant with its attribute values
        $avModel = new VariationAttributeValue();
        foreach ($variants as &$v) {
            $v['attributes'] = $avModel->getForVariant((int)$v['id']);
            $v['variant_label'] = $avModel->getLabelForVariant((int)$v['id']);
            // Backward-compat: if no new-style attributes mapped, fall back to flat columns
            if (empty($v['attributes']) && !empty($v['attribute_value'])) {
                $v['attributes'] = [[
                    'attribute_name'      => $v['attribute_label'] ?? 'Variant',
                    'attribute_type'      => 'button',
                    'value_id'            => null,
                    'value'               => $v['attribute_value'],
                    'swatch_hex_or_image' => null,
                ]];
                $v['variant_label'] = ($v['attribute_label'] ?? 'Variant') . ': ' . $v['attribute_value'];
            }
        }
        unset($v);

        return $variants;
    }

    /**
     * Resolve a specific variant by its set of attribute_value_ids for a product.
     * Returns the variant row (with attributes) or null if combo not found.
     *
     * @param  int   $productId
     * @param  int[] $attributeValueIds  Must match EXACTLY (order-independent)
     * @return array|null
     */
    public function getVariantByAttributeCombo(int $productId, array $attributeValueIds): ?array
    {
        if (empty($attributeValueIds)) {
            return null;
        }

        $count = count($attributeValueIds);
        $inClause = implode(',', array_fill(0, $count, '?'));

        // Find variants that match ALL supplied attribute_value_ids AND have no extra ones
        $sql = "
            SELECT pv.id
            FROM `product_variants` pv
            WHERE pv.product_id = ? AND pv.is_active = 1
              AND (
                SELECT COUNT(*)
                FROM `product_variation_attribute_map` pvam
                WHERE pvam.variation_id = pv.id
                  AND pvam.attribute_value_id IN ({$inClause})
              ) = ?
              AND (
                SELECT COUNT(*)
                FROM `product_variation_attribute_map` pvam2
                WHERE pvam2.variation_id = pv.id
              ) = ?
        ";

        $params = array_merge([$productId], array_values($attributeValueIds), [$count, $count]);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $variants = $this->getByProduct($productId, true);
        foreach ($variants as $v) {
            if ((int)$v['id'] === (int)$row['id']) {
                return $v;
            }
        }
        return null;
    }

    /**
     * Build the full variation matrix for a product page.
     * Returns structured data consumed by the storefront JS.
     *
     * @param  int $productId
     * @return array {attributes: [...], combinations: [...]}
     */
    public function getVariantMatrix(int $productId): array
    {
        $vService = new \App\Services\VariationService();
        $nested = $vService->getNestedVariantMatrix($productId);
        if ($nested['variation_mode'] !== 'none' && !empty($nested['colors'])) {
            return $nested;
        }

        // Fallback to legacy product_variants if product_colors is empty
        $vaModel  = new VariationAttribute();
        $avModel  = new VariationAttributeValue();

        $attributes    = $vaModel->getAttributesForProduct($productId);
        $variants      = $this->getByProduct($productId, true);

        $combinations = [];
        foreach ($variants as $v) {
            $valueIds = $avModel->getValueIdsForVariant((int)$v['id']);
            $combinations[] = [
                'variation_id'       => (int)$v['id'],
                'sku'                => $v['sku'] ?? $v['variant_code'] ?? null,
                'attribute_value_ids'=> array_map('intval', $valueIds),
                'wholesale_price'    => (float)$v['wholesale_price'],
                'one_piece_price'    => (float)$v['one_piece_price'],
                'stock'              => (int)$v['stock_quantity'],
                'image_url'          => $v['image_url'] ?? null,
                'is_active'          => (bool)$v['is_active'],
                'attributes'         => $v['attributes'],
                'variant_label'      => $v['variant_label'] ?? '',
            ];
        }

        return [
            'variation_mode' => !empty($attributes) ? 'single' : 'none',
            'attributes'   => $attributes,
            'combinations' => $combinations,
            'colors'       => [],
        ];
    }

    /**
     * Create a new variant and associate its attribute values in the junction table.
     *
     * @param array $data  Expects optional key 'attributes' => [[name=>?, value=>?], ...]
     */
    public function createVariant(array $data): int {
        $sql = "INSERT INTO `{$this->table}` (
            `product_id`, `variant_code`, `sku`, `image_url`, `attribute_label`, `attribute_value`,
            `weight`, `dimensions`, `stock_quantity`, `wholesale_price`, `one_piece_price`,
            `gst_percent`, `hsn_code`, `sort_order`, `is_active`
        ) VALUES (
            :product_id, :variant_code, :sku, :image_url, :attribute_label, :attribute_value,
            :weight, :dimensions, :stock_quantity, :wholesale_price, :one_piece_price,
            :gst_percent, :hsn_code, :sort_order, :is_active
        )";

        $sku = !empty($data['sku']) ? trim($data['sku']) : (!empty($data['variant_code']) ? trim($data['variant_code']) : null);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id'      => (int)$data['product_id'],
            ':variant_code'    => $sku,
            ':sku'             => $sku,
            ':image_url'       => !empty($data['image_url']) ? trim($data['image_url']) : null,
            ':attribute_label' => !empty($data['attribute_label']) ? trim($data['attribute_label']) : 'Variant',
            ':attribute_value' => trim($data['attribute_value'] ?? 'Default'),
            ':weight'          => !empty($data['weight']) ? trim($data['weight']) : null,
            ':dimensions'      => !empty($data['dimensions']) ? trim($data['dimensions']) : null,
            ':stock_quantity'  => (int)($data['stock_quantity'] ?? 0),
            ':wholesale_price' => (float)($data['wholesale_price'] ?? 0),
            ':one_piece_price' => (float)($data['one_piece_price'] ?? 0),
            ':gst_percent'     => isset($data['gst_percent']) && $data['gst_percent'] !== '' ? (float)$data['gst_percent'] : null,
            ':hsn_code'        => !empty($data['hsn_code']) ? trim($data['hsn_code']) : null,
            ':sort_order'      => (int)($data['sort_order'] ?? 0),
            ':is_active'       => !empty($data['is_active']) ? 1 : 0
        ]);

        $variantId = (int)$this->db->lastInsertId();

        // Link attribute values in junction table
        if ($variantId > 0 && !empty($data['attributes']) && is_array($data['attributes'])) {
            $this->syncAttributeValues($variantId, $data['attributes']);
        }

        return $variantId;
    }

    /**
     * Update an existing variant and refresh its attribute junction rows.
     */
    public function updateVariant(int $id, array $data): bool {
        $sku = !empty($data['sku']) ? trim($data['sku']) : (!empty($data['variant_code']) ? trim($data['variant_code']) : null);

        $sql = "UPDATE `{$this->table}` SET
            `variant_code`    = :variant_code,
            `sku`             = :sku,
            `image_url`       = :image_url,
            `attribute_label` = :attribute_label,
            `attribute_value` = :attribute_value,
            `weight`          = :weight,
            `dimensions`      = :dimensions,
            `stock_quantity`  = :stock_quantity,
            `wholesale_price` = :wholesale_price,
            `one_piece_price` = :one_piece_price,
            `gst_percent`     = :gst_percent,
            `hsn_code`        = :hsn_code,
            `sort_order`      = :sort_order,
            `is_active`       = :is_active
            WHERE `id`        = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id'              => $id,
            ':variant_code'    => $sku,
            ':sku'             => $sku,
            ':image_url'       => !empty($data['image_url']) ? trim($data['image_url']) : null,
            ':attribute_label' => !empty($data['attribute_label']) ? trim($data['attribute_label']) : 'Variant',
            ':attribute_value' => trim($data['attribute_value'] ?? 'Default'),
            ':weight'          => !empty($data['weight']) ? trim($data['weight']) : null,
            ':dimensions'      => !empty($data['dimensions']) ? trim($data['dimensions']) : null,
            ':stock_quantity'  => (int)($data['stock_quantity'] ?? 0),
            ':wholesale_price' => (float)($data['wholesale_price'] ?? 0),
            ':one_piece_price' => (float)($data['one_piece_price'] ?? 0),
            ':gst_percent'     => isset($data['gst_percent']) && $data['gst_percent'] !== '' ? (float)$data['gst_percent'] : null,
            ':hsn_code'        => !empty($data['hsn_code']) ? trim($data['hsn_code']) : null,
            ':sort_order'      => (int)($data['sort_order'] ?? 0),
            ':is_active'       => !empty($data['is_active']) ? 1 : 0
        ]);

        // Refresh attribute values if provided
        if (!empty($data['attributes']) && is_array($data['attributes'])) {
            $this->syncAttributeValues($id, $data['attributes']);
        }

        return $result;
    }

    /**
     * Delete a variant by ID. Junction rows removed via CASCADE FK.
     */
    public function deleteVariant(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Sync attribute values for a variant in the junction table.
     * Clears existing and inserts fresh rows.
     *
     * @param  int   $variantId
     * @param  array $attributes  [[name => 'Color', value => 'Red', type => 'swatch'], ...]
     */
    public function syncAttributeValues(int $variantId, array $attributes): void
    {
        // Remove existing junction rows
        $this->db->prepare(
            "DELETE FROM `product_variation_attribute_map` WHERE `variation_id` = ?"
        )->execute([$variantId]);

        $vaModel  = new VariationAttribute();
        $avModel  = new VariationAttributeValue();

        $stmtIns = $this->db->prepare(
            "INSERT IGNORE INTO `product_variation_attribute_map` (`variation_id`, `attribute_value_id`) VALUES (?, ?)"
        );

        foreach ($attributes as $attr) {
            $attrName  = trim($attr['name']  ?? $attr['attribute_name'] ?? '');
            $attrValue = trim($attr['value'] ?? $attr['attribute_value'] ?? '');
            $attrType  = trim($attr['type']  ?? $attr['attribute_type'] ?? 'button');
            $swatchHex = trim($attr['swatch_hex_or_image'] ?? $attr['swatch'] ?? '');

            if (empty($attrName) || empty($attrValue)) {
                continue;
            }

            $attributeId    = $vaModel->findOrCreate($attrName, $attrType);
            $attributeValId = $avModel->findOrCreate($attributeId, $attrValue);

            // Update swatch if provided
            if (!empty($swatchHex)) {
                $avModel->updateSwatch($attributeValId, $swatchHex);
            }

            $stmtIns->execute([$variantId, $attributeValId]);
        }
    }
}
