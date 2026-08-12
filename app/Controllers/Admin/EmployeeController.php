<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;
use App\Models\Role;
use App\Helpers\Paginator;

class EmployeeController extends Controller {
    protected User $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    public function index(): string {
        if (!Auth::hasPermission('employees.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $page = (int)($this->request->input('page', 1));
        $perPage = (int)($this->request->input('per_page', 20));

        $result = $this->userModel->paginate($page, $perPage, '1=1', [], 'id DESC');
        $paginator = new Paginator($result['total'], $result['per_page'], $result['current_page'], url('admin/employees'), $_GET);

        $roleModel = new Role();
        $roles = $roleModel->all('name ASC');

        return $this->render('admin/employees/index', [
            'employees' => $this->userModel->getAllWithRoles(),
            'paginator' => $paginator,
            'roles' => $roles
        ]);
    }

    public function store(): void {
        if (!Auth::hasPermission('employees.add')) {
            $this->redirect(url('admin/employees'));
        }

        $name = trim((string)$this->request->input('name', ''));
        $email = trim((string)$this->request->input('email', ''));
        $phone = trim((string)$this->request->input('phone', ''));
        $username = trim((string)$this->request->input('username', ''));
        $password = (string)$this->request->input('password', '');
        $roleId = (int)$this->request->input('role_id', 0);
        $status = $this->request->input('status', 'active');

        if (empty($name) || empty($email) || empty($username) || empty($password) || $roleId <= 0) {
            $this->setFlash('error', 'Please fill in all required fields.');
            $this->redirect(url('admin/employees'));
            return;
        }

        $id = $this->userModel->insert([
            'role_id' => $roleId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'photo' => null,
            'status' => in_array($status, ['active', 'inactive']) ? $status : 'active'
        ]);

        activity_log('Add Employee', 'Employees', $id, "Added employee account: {$name} ({$username})");

        $this->setFlash('success', 'Employee account created successfully.');
        $this->redirect(url('admin/employees'));
    }

    public function update(int $id): void {
        if (!Auth::hasPermission('employees.edit')) {
            $this->redirect(url('admin/employees'));
        }

        $employee = $this->userModel->find($id);
        if (!$employee) {
            $this->setFlash('error', 'Employee account not found.');
            $this->redirect(url('admin/employees'));
            return;
        }

        $data = [
            'role_id' => (int)$this->request->input('role_id', $employee['role_id']),
            'name' => trim((string)$this->request->input('name', $employee['name'])),
            'email' => trim((string)$this->request->input('email', $employee['email'])),
            'phone' => trim((string)$this->request->input('phone', $employee['phone'])),
            'username' => trim((string)$this->request->input('username', $employee['username'])),
            'status' => $this->request->input('status', $employee['status'])
        ];

        $password = (string)$this->request->input('password', '');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $this->userModel->update($id, $data);
        activity_log('Update Employee', 'Employees', $id, "Updated employee ID: {$id} ({$data['name']})");

        $this->setFlash('success', 'Employee account updated successfully.');
        $this->redirect(url('admin/employees'));
    }

    public function delete(int $id): void {
        if (!Auth::hasPermission('employees.delete')) {
            $this->redirect(url('admin/employees'));
        }

        if ($id === 1) {
            $this->setFlash('error', 'Super Admin account cannot be deleted.');
            $this->redirect(url('admin/employees'));
            return;
        }

        $this->userModel->delete($id);
        activity_log('Delete Employee', 'Employees', $id, "Deleted employee ID: {$id}");

        $this->setFlash('success', 'Employee account deleted successfully.');
        $this->redirect(url('admin/employees'));
    }
}
