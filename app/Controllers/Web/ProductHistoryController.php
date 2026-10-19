<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\ProductHistoryService;

class ProductHistoryController extends BaseController
{
    public function index(): void
    {
        $userId = $this->userId();
        if ($userId === '') {
            header('Location: ' . url('login?return=account/history'));
            exit;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 24;
        $total = ProductHistoryService::count($userId);
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $items = ProductHistoryService::getRecent($userId, $perPage, ($page - 1) * $perPage);

        $this->renderView('web/history', [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'perPage' => $perPage,
        ]);
    }

    public function clear(): void
    {
        $userId = $this->userId();
        if ($userId === '') {
            $this->authRequired();
            return;
        }
        ProductHistoryService::clear($userId);
        $this->done(true, 'History cleared.', url('account/history'));
    }

    public function remove(): void
    {
        $userId = $this->userId();
        if ($userId === '') {
            $this->authRequired();
            return;
        }
        $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        ProductHistoryService::remove($userId, $productId);
        $this->done(true, 'Removed from history.', url('account/history'));
    }

    public function recent(): void
    {
        header('Cache-Control: private, no-store, no-cache, must-revalidate');
        header('Content-Type: application/json; charset=utf-8');
        $userId = $this->userId();
        if ($userId === '') {
            echo json_encode(['items' => []]);
            return;
        }
        $rows = ProductHistoryService::getRecent($userId, 12, 0);
        $items = [];
        foreach ($rows as $row) {
            $items[] = ProductHistoryService::toCardPayload($row);
        }
        echo json_encode(['items' => $items]);
    }

    private function userId(): string
    {
        $id = $_SESSION['user_id'] ?? '';
        if ($id === null || $id === '' || $id === '0') {
            return '';
        }
        return trim((string)$id);
    }

    private function isAjax(): bool
    {
        $xrw = (string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        return strcasecmp($xrw, 'XMLHttpRequest') === 0;
    }

    private function authRequired(): void
    {
        if ($this->isAjax()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['success' => false, 'code' => 'login_required', 'message' => 'Please log in.']);
            exit;
        }
        header('Location: ' . url('login?return=account/history'));
        exit;
    }

    private function done(bool $ok, string $message, string $redirect): void
    {
        if ($this->isAjax()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => $ok, 'message' => $message]);
            exit;
        }
        header('Location: ' . $redirect);
        exit;
    }
}
