<?php
namespace App\Models;

use App\Core\Model;

class ScooterModel extends Model {
    protected string $table = 'scooter_models';

    public function getModelsByBrand(int $brandId): array {
        return $this->where("brand_id = ? AND status = 'active'", [$brandId], "sort_order ASC, name ASC");
    }

    public function getAllWithBrand(): array {
        $stmt = $this->db->prepare("
            SELECT sm.*, b.name as brand_name, b.logo as brand_logo 
            FROM scooter_models sm
            JOIN brands b ON sm.brand_id = b.id
            ORDER BY b.name ASC, sm.sort_order ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
