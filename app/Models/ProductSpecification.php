<?php
namespace App\Models;

use App\Core\Model;

class ProductSpecification extends Model {
    protected string $table = 'product_specifications';

    /**
     * Get all specifications for a product ordered by sort_order.
     */
    public function getByProduct(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `product_id` = ? ORDER BY `sort_order` ASC, `id` ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Save/Replace all specifications for a product.
     */
    public function saveSpecifications(int $productId, array $specs): void {
        // Clear existing specs first for clean replace
        $stmtDel = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `product_id` = ?");
        $stmtDel->execute([$productId]);

        if (empty($specs)) return;

        $stmtIns = $this->db->prepare("INSERT INTO `{$this->table}` (`product_id`, `spec_key`, `spec_value`, `sort_order`) VALUES (?, ?, ?, ?)");
        $order = 1;
        foreach ($specs as $spec) {
            $key = trim($spec['key'] ?? $spec['spec_key'] ?? '');
            $val = trim($spec['value'] ?? $spec['spec_value'] ?? '');
            if (!empty($key) && !empty($val)) {
                $stmtIns->execute([$productId, $key, $val, $order++]);
            }
        }
    }

    /**
     * Save or update a single specification record.
     */
    public function saveSingleSpec(int $productId, int $specId, string $key, string $value): int {
        if ($specId > 0) {
            $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `spec_key` = ?, `spec_value` = ? WHERE `id` = ? AND `product_id` = ?");
            $stmt->execute([$key, $value, $specId, $productId]);
            return $specId;
        } else {
            $maxStmt = $this->db->prepare("SELECT MAX(sort_order) as max_ord FROM `{$this->table}` WHERE `product_id` = ?");
            $maxStmt->execute([$productId]);
            $row = $maxStmt->fetch();
            $maxOrd = (int)($row['max_ord'] ?? 0) + 1;

            $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (`product_id`, `spec_key`, `spec_value`, `sort_order`) VALUES (?, ?, ?, ?)");
            $stmt->execute([$productId, $key, $value, $maxOrd]);
            return (int)$this->db->lastInsertId();
        }
    }

    /**
     * Delete single spec by ID.
     */
    public function deleteSpec(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }
}
