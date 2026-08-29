<?php
namespace App\Models;

use App\Core\Model;

class Brand extends Model {
    protected string $table = 'brands';

    public function getActiveBrands(): array {
        return $this->where("status = 'active'", [], "sort_order ASC, name ASC");
    }

    public function getFeaturedBrands(): array {
        return $this->where("status = 'active' AND is_featured = 1", [], "sort_order ASC, name ASC");
    }

    public function getAllOrdered(): array {
        return $this->all("sort_order ASC, id ASC");
    }

    public function updateSortOrders(array $orderedIds): bool {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET sort_order = ? WHERE id = ?");
            foreach ($orderedIds as $index => $id) {
                $stmt->execute([(int)$index, (int)$id]);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    public function getMaxSortOrder(): int {
        $stmt = $this->db->query("SELECT MAX(sort_order) FROM {$this->table}");
        return (int)$stmt->fetchColumn();
    }
}
