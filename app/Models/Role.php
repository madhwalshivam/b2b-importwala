<?php
namespace App\Models;

use App\Core\Model;

class Role extends Model {
    protected string $table = 'roles';

    public function getRolesWithStats(): array {
        $sql = "
            SELECT 
                r.*,
                (SELECT COUNT(*) FROM admin_users u WHERE u.role_id = r.id AND u.status = 'active') as active_employee_count,
                (SELECT COUNT(*) FROM admin_users u WHERE u.role_id = r.id) as total_employee_count,
                (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id) as permissions_count
            FROM roles r
            ORDER BY r.id ASC
        ";
        return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRolePermissions(int $roleId): array {
        $stmt = $this->db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function syncPermissions(int $roleId, array $permissionIds): void {
        $stmtDel = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmtDel->execute([$roleId]);

        if (empty($permissionIds)) return;

        $stmtIns = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($permissionIds as $permId) {
            $stmtIns->execute([$roleId, (int)$permId]);
        }
    }
}
