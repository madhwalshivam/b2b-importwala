<?php
namespace App\Models;

use App\Core\Model;

class ProductVariant extends Model {
    protected string $table = 'product_variants';

    /**
     * Get all variants for a given product ID.
     */
    public function getByProduct(int $productId, bool $activeOnly = true): array {
        $sql = "SELECT * FROM `{$this->table}` WHERE `product_id` = ?";
        if ($activeOnly) {
            $sql .= " AND `is_active` = 1";
        }
        $sql .= " ORDER BY `sort_order` ASC, `id` ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Create a new variant.
     */
    public function createVariant(array $data): int {
        $sql = "INSERT INTO `{$this->table}` (
            `product_id`, `variant_code`, `image_url`, `attribute_label`, `attribute_value`,
            `weight`, `dimensions`, `stock_quantity`, `wholesale_price`, `one_piece_price`, `sort_order`, `is_active`
        ) VALUES (
            :product_id, :variant_code, :image_url, :attribute_label, :attribute_value,
            :weight, :dimensions, :stock_quantity, :wholesale_price, :one_piece_price, :sort_order, :is_active
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id'      => (int)$data['product_id'],
            ':variant_code'    => !empty($data['variant_code']) ? trim($data['variant_code']) : null,
            ':image_url'       => !empty($data['image_url']) ? trim($data['image_url']) : null,
            ':attribute_label' => !empty($data['attribute_label']) ? trim($data['attribute_label']) : 'Variant',
            ':attribute_value' => trim($data['attribute_value'] ?? 'Default'),
            ':weight'          => !empty($data['weight']) ? trim($data['weight']) : null,
            ':dimensions'      => !empty($data['dimensions']) ? trim($data['dimensions']) : null,
            ':stock_quantity'  => (int)($data['stock_quantity'] ?? 0),
            ':wholesale_price' => (float)($data['wholesale_price'] ?? 0),
            ':one_piece_price' => (float)($data['one_piece_price'] ?? 0),
            ':sort_order'      => (int)($data['sort_order'] ?? 0),
            ':is_active'       => !empty($data['is_active']) ? 1 : 0
        ]);
        
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing variant.
     */
    public function updateVariant(int $id, array $data): bool {
        $sql = "UPDATE `{$this->table}` SET
            `variant_code`    = :variant_code,
            `image_url`       = :image_url,
            `attribute_label` = :attribute_label,
            `attribute_value` = :attribute_value,
            `weight`          = :weight,
            `dimensions`      = :dimensions,
            `stock_quantity`  = :stock_quantity,
            `wholesale_price` = :wholesale_price,
            `one_piece_price` = :one_piece_price,
            `sort_order`      = :sort_order,
            `is_active`       = :is_active
            WHERE `id`        = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'              => $id,
            ':variant_code'    => !empty($data['variant_code']) ? trim($data['variant_code']) : null,
            ':image_url'       => !empty($data['image_url']) ? trim($data['image_url']) : null,
            ':attribute_label' => !empty($data['attribute_label']) ? trim($data['attribute_label']) : 'Variant',
            ':attribute_value' => trim($data['attribute_value'] ?? 'Default'),
            ':weight'          => !empty($data['weight']) ? trim($data['weight']) : null,
            ':dimensions'      => !empty($data['dimensions']) ? trim($data['dimensions']) : null,
            ':stock_quantity'  => (int)($data['stock_quantity'] ?? 0),
            ':wholesale_price' => (float)($data['wholesale_price'] ?? 0),
            ':one_piece_price' => (float)($data['one_piece_price'] ?? 0),
            ':sort_order'      => (int)($data['sort_order'] ?? 0),
            ':is_active'       => !empty($data['is_active']) ? 1 : 0
        ]);
    }

    /**
     * Delete a variant by ID.
     */
    public function deleteVariant(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }
}
