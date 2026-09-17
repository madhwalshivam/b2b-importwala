<?php

namespace App\Services;

use App\Core\Database;
use App\Models\ProductColor;
use App\Models\ProductColorSize;

/**
 * VariationService
 * Business logic for managing Single (Color-only) vs Double (Color + Nested Size) product variations.
 */
class VariationService
{
    protected \PDO $db;
    protected ProductColor $colorModel;
    protected ProductColorSize $sizeModel;

    public function __construct()
    {
        $this->db         = Database::getInstance();
        $this->colorModel = new ProductColor();
        $this->sizeModel  = new ProductColorSize();
    }

    /**
     * Get the structured variation matrix for a product.
     * Returns variation_mode and colors (with nested sizes if mode = double).
     */
    public function getNestedVariantMatrix(int $productId): array
    {
        $stmt = $this->db->prepare("SELECT `variation_mode` FROM `products` WHERE `id` = ?");
        $stmt->execute([$productId]);
        $mode = $stmt->fetchColumn() ?: 'none';

        $colors = [];
        if ($mode !== 'none') {
            $colors = $this->colorModel->getByProduct($productId, true);
        }

        return [
            'variation_mode' => $mode,
            'colors'         => $colors,
        ];
    }

    /**
     * Save/synchronize variations for a product based on mode (none | single | double).
     *
     * @param int    $productId
     * @param string $mode       'none' | 'single' | 'double'
     * @param array  $colorsData Array of color entries from form/API
     */
    public function saveNestedVariations(int $productId, string $mode, array $colorsData): void
    {
        if (!in_array($mode, ['none', 'single', 'double'], true)) {
            $mode = 'none';
        }

        $this->db->beginTransaction();

        try {
            // 1. Update product variation_mode
            $upStmt = $this->db->prepare("UPDATE `products` SET `variation_mode` = ?, `updated_at` = NOW() WHERE `id` = ?");
            $upStmt->execute([$mode, $productId]);

            if ($mode === 'none') {
                // Clear all colors for this product
                $this->db->prepare("DELETE FROM `product_colors` WHERE `product_id` = ?")->execute([$productId]);
                $this->db->commit();
                return;
            }

            // Existing colors map
            $existingColors = $this->colorModel->getByProduct($productId, false);
            $existingColorIds = array_column($existingColors, 'id');
            $keptColorIds = [];

            foreach ($colorsData as $cIdx => $cData) {
                $colorId   = !empty($cData['id']) ? (int)$cData['id'] : null;
                $colorName = trim($cData['color_name'] ?? 'Default Color');
                $swatch    = !empty($cData['swatch_hex_or_image']) ? trim($cData['swatch_hex_or_image']) : null;

                if (empty($colorName)) {
                    continue;
                }

                $colorPayload = [
                    'product_id'          => $productId,
                    'color_name'          => $colorName,
                    'swatch_hex_or_image' => $swatch,
                    'display_order'       => $cIdx + 1,
                ];

                if ($mode === 'single') {
                    $colorPayload['sku']       = !empty($cData['sku']) ? trim($cData['sku']) : null;
                    $colorPayload['price']     = isset($cData['price']) && $cData['price'] !== '' ? (float)$cData['price'] : null;
                    $colorPayload['stock_qty'] = isset($cData['stock_qty']) && $cData['stock_qty'] !== '' ? (int)$cData['stock_qty'] : null;
                } else {
                    $colorPayload['sku']       = null;
                    $colorPayload['price']     = null;
                    $colorPayload['stock_qty'] = null;
                }

                if ($colorId && in_array($colorId, $existingColorIds, true)) {
                    $this->colorModel->updateColor($colorId, $colorPayload);
                    $keptColorIds[] = $colorId;
                } else {
                    $colorId = $this->colorModel->createColor($colorPayload);
                    $keptColorIds[] = $colorId;
                }

                // If mode === 'double', handle nested sizes under this color
                if ($mode === 'double') {
                    $existingSizes = $this->sizeModel->getByColor($colorId);
                    $existingSizeIds = array_column($existingSizes, 'id');
                    $keptSizeIds = [];

                    $sizesList = $cData['sizes'] ?? [];
                    foreach ($sizesList as $sIdx => $sData) {
                        $sizeId    = !empty($sData['id']) ? (int)$sData['id'] : null;
                        $sizeLabel = trim($sData['size_label'] ?? 'Standard');

                        if (empty($sizeLabel)) {
                            continue;
                        }

                        $sizePayload = [
                            'color_id'      => $colorId,
                            'size_label'    => $sizeLabel,
                            'sku'           => !empty($sData['sku']) ? trim($sData['sku']) : null,
                            'price'         => (float)($sData['price'] ?? 0),
                            'stock_qty'     => (int)($sData['stock_qty'] ?? 0),
                            'gst_percent'   => isset($sData['gst_percent']) && $sData['gst_percent'] !== '' ? (float)$sData['gst_percent'] : null,
                            'hsn_code'      => !empty($sData['hsn_code']) ? trim($sData['hsn_code']) : null,
                            'display_order' => $sIdx + 1,
                        ];

                        if ($sizeId && in_array($sizeId, $existingSizeIds, true)) {
                            $this->sizeModel->updateSize($sizeId, $sizePayload);
                            $keptSizeIds[] = $sizeId;
                        } else {
                            $newSizeId = $this->sizeModel->createSize($sizePayload);
                            $keptSizeIds[] = $newSizeId;
                        }
                    }

                    // Delete removed sizes for this color
                    $sizesToDelete = array_diff($existingSizeIds, $keptSizeIds);
                    foreach ($sizesToDelete as $stId) {
                        $this->sizeModel->deleteSize((int)$stId);
                    }
                } else {
                    // Mode = single: clear any size rows under this color if switching from double -> single
                    $existingSizes = $this->sizeModel->getByColor($colorId);
                    foreach ($existingSizes as $es) {
                        $this->sizeModel->deleteSize((int)$es['id']);
                    }
                }
            }

            // Delete removed colors
            $colorsToDelete = array_diff($existingColorIds, $keptColorIds);
            foreach ($colorsToDelete as $ctId) {
                $this->colorModel->deleteColor((int)$ctId);
            }

            // Sync nested variants to flat product_variants table for frontend backward compatibility
            $this->syncToFlatVariants($productId, $mode);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Generate Cartesian Product of Attribute Groups
     *
     * @param array $groups e.g. [['name' => 'Color', 'values' => ['Red', 'Blue']], ...]
     * @return array Array of combinations
     */
    public function generateCartesianCombinations(array $groups): array
    {
        $result = [[]];

        foreach ($groups as $group) {
            $name = trim($group['name'] ?? '');
            $values = $group['values'] ?? [];

            if (empty($name) || empty($values)) {
                continue;
            }

            $append = [];
            foreach ($result as $product) {
                foreach ($values as $val) {
                    $val = trim($val);
                    if ($val === '') {
                        continue;
                    }
                    $append[] = array_merge($product, [
                        ['name' => $name, 'value' => $val]
                    ]);
                }
            }
            $result = $append;
        }

        // If no valid attributes were found, result will still be [[]], so return empty array
        return ($result === [[]]) ? [] : $result;
    }

    /**
     * Helper to resolve or create Attribute & Value
     *
     * @param string $attrName
     * @param string $attrValue
     * @param string $displayType 'button' | 'swatch'
     * @return int Attribute Value ID
     */
    public function resolveOrCreateAttributeValue(string $attrName, string $attrValue, string $displayType = 'button'): int
    {
        $attrModel = new \App\Models\VariationAttribute();
        $attrId = $attrModel->findOrCreate($attrName, $displayType);

        $valModel = new \App\Models\VariationAttributeValue();
        return $valModel->findOrCreate($attrId, $attrValue);
    }

    /**
     * Synchronizes the nested variation tables (product_colors, product_color_sizes)
     * to the flat legacy product_variants table used by the frontend.
     */
    public function syncToFlatVariants(int $productId, string $mode): void
    {
        // 1. Delete old variants for this product
        $this->db->prepare("DELETE FROM `product_variants` WHERE `product_id` = ?")->execute([$productId]);

        if ($mode === 'none') {
            return;
        }

        $colors = $this->colorModel->getByProduct($productId, false);
        if (empty($colors)) return;

        $sortOrder = 1;

        if ($mode === 'single') {
            foreach ($colors as $c) {
                $stmt = $this->db->prepare("
                    INSERT INTO `product_variants` 
                    (`product_id`, `attribute_label`, `attribute_value`, `sku`, `image_url`, `wholesale_price`, `one_piece_price`, `stock_quantity`, `sort_order`, `is_active`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $productId,
                    'Color',
                    $c['color_name'],
                    $c['sku'] ?? null,
                    $c['swatch_hex_or_image'] ?? null,
                    $c['price'] ?? 0,
                    $c['price'] ?? 0,
                    $c['stock_qty'] ?? 0,
                    $sortOrder++,
                    1
                ]);
            }
        } elseif ($mode === 'double') {
            foreach ($colors as $c) {
                $sizes = $this->sizeModel->getByColor((int)$c['id']);
                foreach ($sizes as $s) {
                    $stmt = $this->db->prepare("
                        INSERT INTO `product_variants` 
                        (`product_id`, `attribute_label`, `attribute_value`, `sku`, `image_url`, `wholesale_price`, `one_piece_price`, `stock_quantity`, `sort_order`, `is_active`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $productId,
                        'Color / Size',
                        $c['color_name'] . ' - ' . $s['size_label'],
                        $s['sku'] ?? null,
                        $c['swatch_hex_or_image'] ?? null,
                        $s['price'] ?? 0,
                        $s['price'] ?? 0,
                        $s['stock_qty'] ?? 0,
                        $sortOrder++,
                        1
                    ]);
                }
            }
        }
    }
}
