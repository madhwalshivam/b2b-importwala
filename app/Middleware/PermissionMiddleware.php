<?php
namespace App\Middleware;

use App\Core\Auth;
use App\Core\Response;
use App\Core\View;

class PermissionMiddleware {
    protected string $permissionKey;

    public function __construct(string $permissionKey = '') {
        $this->permissionKey = $permissionKey;
    }

    public function execute(): void {
        if (!Auth::check()) {
            (new Response())->redirect(url('admin/login'));
        }

        if (!empty($this->permissionKey) && !Auth::hasPermission($this->permissionKey)) {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Permission denied: ' . $this->permissionKey]);
                exit;
            }
            (new Response())->setStatusCode(403);
            echo (new View())->render('errors/403', ['permission' => $this->permissionKey]);
            exit;
        }
    }
}
