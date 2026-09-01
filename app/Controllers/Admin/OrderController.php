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
}
