<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Helpers\Paginator;

class ActivityLogController extends Controller {
    public function index(): string {
        if (!Auth::hasPermission('logs.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $page = (int)($this->request->input('page', 1));
        $perPage = (int)($this->request->input('per_page', 20));
        $offset = ($page - 1) * $perPage;

        $db = Database::getInstance();
        $total = (int)$db->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();

        $stmt = $db->prepare("SELECT * FROM activity_logs ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute();
        $logs = $stmt->fetchAll();

        $paginator = new Paginator($total, $perPage, $page, url('admin/activity-logs'), $_GET);

        return $this->render('admin/logs/index', [
            'logs' => $logs,
            'paginator' => $paginator
        ]);
    }
}
