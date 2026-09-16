<?php

namespace App\Models;

use App\Core\Model;

/**
 * ProductColor — Top-level Color variation entity
 * When variation_mode = 'single', price/stock/sku live directly on this table.
 * When variation_mode = 'double', this table groups nested ProductColorSizes.
 */
class ProductColor extends Model
{
    protected string $table = 'product_colors';

    /**
     * Get all colors for a product, optionally with nested sizes.
     *
     * @param  int  $productId
     * @param  bool $withSizes
     * @return array
     */
    public function getByProduct(int $productId, bool $withSizes = true): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE `product_id` = ? ORDER BY `display_order` ASC, `id` ASC"
        );
        $stmt->execute([$productId]);
        $colors = $stmt->fetchAll() ?: [];

        if ($withSizes && !empty($colors)) {
            $pcsModel = new ProductColorSize();
            foreach ($colors as &$c) {
                $c['sizes'] = $pcsModel->getByColor((int)$c['id']);
            }
            unset($c);
        }

        return $colors;
    }

    /**
     * Create a new color row.
     */
    public function createColor(array $data): int
    {
        $sql = "INSERT INTO `{$this->table}` (
            `product_id`, `color_name`, `swatch_hex_or_image`, `sku`, `price`, `stock_qty`, `display_order`
        ) VALUES (
            :product_id, :color_name, :swatch_hex_or_image, :sku, :price, :stock_qty, :display_order
        )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id'          => (int)$data['product_id'],
            ':color_name'          => trim($data['color_name'] ?? 'Default Color'),
            ':swatch_hex_or_image' => !empty($data['swatch_hex_or_image']) ? trim($data['swatch_hex_or_image']) : null,
            ':sku'                 => !empty($data['sku']) ? trim($data['sku']) : null,
            ':price'               => isset($data['price']) && $data['price'] !== '' ? (float)$data['price'] : null,
            ':stock_qty'           => isset($data['stock_qty']) && $data['stock_qty'] !== '' ? (int)$data['stock_qty'] : null,
            ':display_order'       => (int)($data['display_order'] ?? 0),
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing color row.
     */
    public function updateColor(int $id, array $data): bool
    {
        $sql = "UPDATE `{$this->table}` SET
            `color_name`          = :color_name,
            `swatch_hex_or_image` = :swatch_hex_or_image,
            `sku`                 = :sku,
            `price`               = :price,
            `stock_qty`           = :stock_qty,
            `display_order`       = :display_order
            WHERE `id`            = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'                  => $id,
            ':color_name'          => trim($data['color_name'] ?? 'Default Color'),
            ':swatch_hex_or_image' => !empty($data['swatch_hex_or_image']) ? trim($data['swatch_hex_or_image']) : null,
            ':sku'                 => !empty($data['sku']) ? trim($data['sku']) : null,
            ':price'               => isset($data['price']) && $data['price'] !== '' ? (float)$data['price'] : null,
            ':stock_qty'           => isset($data['stock_qty']) && $data['stock_qty'] !== '' ? (int)$data['stock_qty'] : null,
            ':display_order'       => (int)($data['display_order'] ?? 0),
        ]);
    }

    /**
     * Delete a color by ID (cascade deletes nested sizes).
     */
    public function deleteColor(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }
}
