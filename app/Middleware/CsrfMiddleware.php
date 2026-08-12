<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware {
    public function execute(): void {
        $request = new Request();
        if ($request->getMethod() === 'POST') {
            $token = $_POST['_csrf_token'] ?? $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            $session = new Session();
            if (!$session->validateCsrfToken($token)) {
                if ($request->isAjax()) {
                    header('Content-Type: application/json');
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'CSRF token verification failed. Please refresh the page.']);
                    exit;
                }
                (new Response())->setStatusCode(403);
                die("CSRF Token Verification Failed.");
            }
        }
    }
}
