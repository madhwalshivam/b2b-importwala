<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Auth;

class OrderController extends BaseController
{
    public function index(): void
    {
        if (!Auth::hasPermission('orders.view') && !Auth::hasPermission('dashboard.view')) {
            header('Location: ' . url('admin/login'));
            exit;
        }

        $db = Database::getInstance();
        $status = $_GET['status'] ?? '';
        
        $query = "SELECT * FROM orders";
        $params = [];

        if (!empty($status)) {
            $query .= " WHERE order_status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY id DESC";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/orders/index', [
            'orders'        => $orders,
            'currentStatus' => $status
        ]);
    }

    public function view(string $id): void
    {
        header('Content-Type: application/json');
        $db = Database::getInstance();
        $orderId = (int)$id;

        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }

        $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'order'   => $order,
            'items'   => $items
        ]);
    }

    public function updateStatus(): void
    {
        header('Content-Type: application/json');
        $db = Database::getInstance();

        $orderId       = (int)($_POST['order_id'] ?? 0);
        $orderStatus   = trim($_POST['order_status'] ?? '');
        $paymentStatus = trim($_POST['payment_status'] ?? '');

        if (!$orderId || !$orderStatus) {
            echo json_encode(['success' => false, 'message' => 'Invalid status parameters']);
            return;
        }

        $up = $db->prepare("UPDATE orders SET order_status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?");
        $up->execute([$orderStatus, $paymentStatus, $orderId]);

        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);
    }

    public function delete(string $id): void
    {
        if (!Auth::hasPermission('orders.delete') && !Auth::hasPermission('orders.view') && !Auth::hasPermission('dashboard.view')) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
                return;
            }
            header('Location: ' . url('admin/orders'));
            exit;
        }

        $db = Database::getInstance();
        $orderId = (int)$id;

        if ($orderId <= 0) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
                return;
            }
            header('Location: ' . url('admin/orders'));
            exit;
        }

        try {
            $db->beginTransaction();

            // 1. Delete associated coupon usage if table exists
            try {
                $db->prepare("DELETE FROM coupon_usage WHERE order_id = ?")->execute([$orderId]);
            } catch (\Throwable $e) {
                // Table might not exist or ignore harmless schema differences
            }

            // 2. Delete order items
            $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);

            // 3. Delete order record
            $stmt = $db->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);

            $db->commit();

            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
                return;
            }

            header('Location: ' . url('admin/orders'));
            exit;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to delete order: ' . $e->getMessage()]);
                return;
            }
            header('Location: ' . url('admin/orders'));
            exit;
        }
    }

    private function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));
    }
}
