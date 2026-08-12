<?php
namespace App\Middleware;

use App\Core\Auth;
use App\Core\Response;

class AdminMiddleware {
    public function execute(): void {
        if (!Auth::check()) {
            (new Response())->redirect(url('admin/login'));
        }
    }
}
