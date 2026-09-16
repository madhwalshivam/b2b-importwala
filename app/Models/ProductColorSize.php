<?php

namespace App\Models;

use App\Core\Model;

/**
 * ProductColorSize — Nested Size variation entity under a ProductColor
 * Used when product's variation_mode = 'double'.
 */
class ProductColorSize extends Model
{
    protected string $table = 'product_color_sizes';

    /**
     * Get all sizes for a color.
     *
     * @param  int $colorId
     * @return array
     */
    public function getByColor(int $colorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE `color_id` = ? ORDER BY `display_order` ASC, `id` ASC"
        );
        $stmt->execute([$colorId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Find a size row by SKU.
     */
    public function findBySku(string $sku): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `sku` = ?");
        $stmt->execute([trim($sku)]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create a size row under a color.
     */
    public function createSize(array $data): int
    {
        $sql = "INSERT INTO `{$this->table}` (
            `color_id`, `size_label`, `sku`, `price`, `stock_qty`, `gst_percent`, `hsn_code`, `image_id`, `display_order`
        ) VALUES (
            :color_id, :size_label, :sku, :price, :stock_qty, :gst_percent, :hsn_code, :image_id, :display_order
        )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':color_id'      => (int)$data['color_id'],
            ':size_label'    => trim($data['size_label'] ?? 'Standard'),
            ':sku'           => !empty($data['sku']) ? trim($data['sku']) : null,
            ':price'         => (float)($data['price'] ?? 0),
            ':stock_qty'     => (int)($data['stock_qty'] ?? 0),
            ':gst_percent'   => isset($data['gst_percent']) && $data['gst_percent'] !== '' ? (float)$data['gst_percent'] : null,
            ':hsn_code'      => !empty($data['hsn_code']) ? trim($data['hsn_code']) : null,
            ':image_id'      => !empty($data['image_id']) ? (int)$data['image_id'] : null,
            ':display_order' => (int)($data['display_order'] ?? 0),
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing size row.
     */
    public function updateSize(int $id, array $data): bool
    {
        $sql = "UPDATE `{$this->table}` SET
            `size_label`    = :size_label,
            `sku`           = :sku,
            `price`         = :price,
            `stock_qty`     = :stock_qty,
            `gst_percent`   = :gst_percent,
            `hsn_code`      = :hsn_code,
            `image_id`      = :image_id,
            `display_order` = :display_order
            WHERE `id`      = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'            => $id,
            ':size_label'    => trim($data['size_label'] ?? 'Standard'),
            ':sku'           => !empty($data['sku']) ? trim($data['sku']) : null,
            ':price'         => (float)($data['price'] ?? 0),
            ':stock_qty'     => (int)($data['stock_qty'] ?? 0),
            ':gst_percent'   => isset($data['gst_percent']) && $data['gst_percent'] !== '' ? (float)$data['gst_percent'] : null,
            ':hsn_code'      => !empty($data['hsn_code']) ? trim($data['hsn_code']) : null,
            ':image_id'      => !empty($data['image_id']) ? (int)$data['image_id'] : null,
            ':display_order' => (int)($data['display_order'] ?? 0),
        ]);
    }

    /**
     * Delete a size row by ID.
     */
    public function deleteSize(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }
}
