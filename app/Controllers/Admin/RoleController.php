<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Role;
use App\Core\Database;

class RoleController extends Controller {
    protected Role $roleModel;

    public function __construct() {
        parent::__construct();
        $this->roleModel = new Role();
    }

    public function index(): string {
        if (!Auth::hasPermission('employees.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $roles = $this->roleModel->getRolesWithStats();
        foreach ($roles as &$r) {
            $r['permission_ids'] = $this->roleModel->getRolePermissions((int)$r['id']);
        }
        unset($r);

        $db = Database::getInstance();
        $permissions = $db->query("SELECT * FROM permissions ORDER BY module ASC, id ASC")->fetchAll();

        // Group permissions by module
        $groupedPermissions = [];
        foreach ($permissions as $perm) {
            $groupedPermissions[$perm['module']][] = $perm;
        }

        return $this->render('admin/roles/index', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions
        ]);
    }

    public function store(): void {
        if (!Auth::hasPermission('employees.add')) {
            $this->redirect(url('admin/roles'));
        }

        $name = $this->request->input('name');
        $description = $this->request->input('description');
        $permissions = $_POST['permissions'] ?? [];

        $roleId = $this->roleModel->insert([
            'name' => $name,
            'slug' => slugify($name),
            'description' => $description
        ]);

        $this->roleModel->syncPermissions($roleId, $permissions);
        activity_log('Create Role', 'Employees', $roleId, "Created role: {$name}");

        $this->setFlash('success', 'Custom role created with permissions.');
        $this->redirect(url('admin/roles'));
    }

    public function edit(int $id): string {
        if (!Auth::hasPermission('employees.edit')) {
            $this->redirect(url('admin/roles'));
        }

        $role = $this->roleModel->find($id);
        $rolePermissions = $this->roleModel->getRolePermissions($id);

        $db = Database::getInstance();
        $permissions = $db->query("SELECT * FROM permissions ORDER BY module ASC, id ASC")->fetchAll();

        $groupedPermissions = [];
        foreach ($permissions as $perm) {
            $groupedPermissions[$perm['module']][] = $perm;
        }

        return $this->render('admin/roles/edit', [
            'role' => $role,
            'rolePermissions' => $rolePermissions,
            'groupedPermissions' => $groupedPermissions
        ]);
    }

    public function update(int $id): void {
        if (!Auth::hasPermission('employees.edit')) {
            $this->redirect(url('admin/roles'));
        }

        $name = $this->request->input('name');
        $description = $this->request->input('description');
        $permissions = $_POST['permissions'] ?? [];

        $this->roleModel->update($id, [
            'name' => $name,
            'slug' => slugify($name),
            'description' => $description
        ]);

        $this->roleModel->syncPermissions($id, $permissions);
        activity_log('Update Role', 'Employees', $id, "Updated role: {$name}");

        $this->setFlash('success', 'Role permissions updated.');
        $this->redirect(url('admin/roles'));
    }
}
