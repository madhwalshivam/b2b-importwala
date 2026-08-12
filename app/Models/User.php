<?php
namespace App\Models;

use App\Core\Model;

class User extends Model {
    protected string $table = 'admin_users';

    public function getAllWithRoles(): array {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM admin_users u
            JOIN roles r ON u.role_id = r.id
            ORDER BY u.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
