<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Order;
use App\Helpers\Paginator;

class OrderController extends Controller {
    protected Order $orderModel;

    public function __construct() {
        parent::__construct();
        $this->orderModel = new Order();
    }

    public function index(): string {
        if (!Auth::hasPermission('orders.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $page = (int)($this->request->input('page', 1));
        $perPage = (int)($this->request->input('per_page', 20));
        $status = $this->request->input('status', '');
        $search = $this->request->input('search', '');

        $whereConditions = ["1=1"];
        $params = [];

        if (!empty($status)) {
            $whereConditions[] = "order_status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $whereConditions[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereSql = implode(' AND ', $whereConditions);
        $result = $this->orderModel->paginate($page, $perPage, $whereSql, $params, 'id DESC');
        $paginator = new Paginator($result['total'], $result['per_page'], $result['current_page'], url('admin/orders'), $_GET);

        return $this->render('admin/orders/index', [
            'orders' => $result['items'],
            'paginator' => $paginator,
            'status' => $status,
            'search' => $search
        ]);
    }

    public function show(int $id): string {
        if (!Auth::hasPermission('orders.view')) {
            $this->redirect(url('admin/orders'));
        }

        $order = $this->orderModel->find($id);
        if (!$order) {
            $this->setFlash('error', 'Order not found.');
            $this->redirect(url('admin/orders'));
        }

        // Live Auto-Sync from Shiprocket if AWB exists
        if (!empty($order['awb_code']) || !empty($order['tracking_number'])) {
            try {
                $awb = $order['awb_code'] ?: $order['tracking_number'];
                $provider = \Lib\Shipping\ShippingProviderFactory::make();
                $trackRes = $provider->getTrackingStatus($awb);
                if (!empty($trackRes['status'])) {
                    $mapped = \App\Controllers\CheckoutApiController::mapShiprocketStatus($trackRes['status']);
                    if (!empty($mapped['shipping_status']) && $mapped['shipping_status'] !== $order['shipping_status']) {
                        $this->orderModel->update($id, [
                            'shipping_status' => $mapped['shipping_status'],
                            'order_status'    => $mapped['order_status'],
                            'courier_name'    => $trackRes['courier_name'] ?? $order['courier_name']
                        ]);
                        $order['shipping_status'] = $mapped['shipping_status'];
                        $order['order_status']    = $mapped['order_status'];
                    }
                }
            } catch (\Throwable $e) {
                // Ignore sync warning
            }
        }

        $items = $this->orderModel->getOrderItems($id);
        $shippingAddress = json_decode($order['shipping_address'] ?? '{}', true);

        return $this->render('admin/orders/show', [
            'order' => $order,
            'items' => $items,
            'shippingAddress' => $shippingAddress
        ]);
    }

    public function updateStatus(int $id): void {
        if (!Auth::hasPermission('orders.edit')) {
            $this->setFlash('error', 'You do not have permission to edit orders.');
            $this->redirect(url("admin/orders/{$id}"));
        }

        $orderStatus    = trim((string)$this->request->input('order_status', ''));
        $shippingStatus = trim((string)$this->request->input('shipping_status', ''));
        $paymentStatus  = trim((string)$this->request->input('payment_status', ''));
        $trackingNumber = trim((string)$this->request->input('tracking_number', ''));
        $courierName    = trim((string)$this->request->input('courier_name', ''));

        $oldOrder = $this->orderModel->find($id);
        if (!$oldOrder) {
            $this->setFlash('error', 'Order not found.');
            $this->redirect(url('admin/orders'));
        }

        // Auto-map shipping_status if not explicitly selected
        if (empty($shippingStatus)) {
            switch ($orderStatus) {
                case 'shipped':
                    $shippingStatus = 'shipped';
                    break;
                case 'delivered':
                case 'completed':
                    $shippingStatus = 'delivered';
                    break;
                case 'cancelled':
                    $shippingStatus = 'cancelled';
                    break;
                case 'packed':
                case 'confirmed':
                    $shippingStatus = 'processing';
                    break;
                default:
                    $shippingStatus = $oldOrder['shipping_status'] ?? 'not_shipped';
                    break;
            }
        }

        // Auto-map payment_status for delivered orders if not explicitly set
        if (empty($paymentStatus)) {
            if (in_array($orderStatus, ['delivered', 'completed']) || $shippingStatus === 'delivered') {
                $paymentStatus = 'paid';
            } else {
                $paymentStatus = $oldOrder['payment_status'] ?? 'pending';
            }
        }

        // Normalize order_status
        $dbOrderStatus = $orderStatus;
        if ($orderStatus === 'delivered') {
            $dbOrderStatus = 'completed';
        }

        $updateData = [
            'order_status'    => $dbOrderStatus,
            'shipping_status' => $shippingStatus,
            'payment_status'  => $paymentStatus,
            'tracking_number' => $trackingNumber,
            'awb_code'        => $trackingNumber,
            'courier_name'    => $courierName,
            'updated_at'      => date('Y-m-d H:i:s')
        ];

        if (!empty($trackingNumber)) {
            $updateData['tracking_url'] = 'https://shiprocket.co/tracking/' . $trackingNumber;
        }

        $this->orderModel->update($id, $updateData);

        // Restock inventory if order is cancelled or refunded
        if (!in_array($oldOrder['order_status'], ['cancelled', 'refunded']) && in_array($dbOrderStatus, ['cancelled', 'refunded'])) {
            $db = \App\Core\Database::getInstance();
            $items = $this->orderModel->getOrderItems($id);
            $stmtRestock = $db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $stmtRestock->execute([$item['quantity'], $item['product_id']]);
                }
            }
            activity_log('Restock Inventory', 'Orders', $id, "Restocked inventory items for cancelled/refunded order ID {$id}");
        }

        activity_log('Update Order Status', 'Orders', $id, "Manually updated order #{$oldOrder['order_number']} to Order: {$dbOrderStatus}, Shipping: {$shippingStatus}, Payment: {$paymentStatus}");

        $this->setFlash('success', 'Order status and shipping details updated successfully.');
        $this->redirect(url("admin/orders/{$id}"));
    }

    public function invoice(int $id): string {
        if (!Auth::hasPermission('orders.view')) {
            $this->redirect(url('admin/orders'));
        }

        $order = $this->orderModel->find($id);
        if (!$order) {
            $this->redirect(url('admin/orders'));
        }

        $items = $this->orderModel->getOrderItems($id);
        $shippingAddress = json_decode($order['shipping_address'], true);

        return $this->render('admin/orders/invoice', [
            'order' => $order,
            'items' => $items,
            'shippingAddress' => $shippingAddress,
            'company' => $GLOBALS['app_config']['company']
        ]);
    }

    public function retryShiprocket(int $id): void {
        if (!Auth::hasPermission('orders.edit')) {
            $this->redirect(url("admin/orders/{$id}"));
        }

        $result = \App\Controllers\CheckoutApiController::pushOrderToShiprocket($id);
        if (!empty($result['success'])) {
            activity_log('Push to Shiprocket', 'Orders', $id, "Successfully pushed order ID {$id} to Shiprocket");
            $this->setFlash('success', 'Order successfully pushed to Shiprocket! AWB Code: ' . ($result['awb_code'] ?? 'Assigned'));
        } else {
            activity_log('Shiprocket Push Failed', 'Orders', $id, "Failed to push order ID {$id} to Shiprocket: " . ($result['message'] ?? 'Error'));
            $this->setFlash('error', 'Shiprocket Push Failed: ' . ($result['message'] ?? 'Unknown error'));
        }

        $this->redirect(url("admin/orders/{$id}"));
    }

    public function cancelShipment(int $id): void {
        if (!Auth::hasPermission('orders.edit')) {
            $this->redirect(url("admin/orders/{$id}"));
        }

        $order = $this->orderModel->find($id);
        if (!$order || empty($order['shiprocket_shipment_id'])) {
            $this->setFlash('error', 'No active Shiprocket shipment found to cancel.');
            $this->redirect(url("admin/orders/{$id}"));
            return;
        }

        $shippingProvider = \Lib\Shipping\ShippingProviderFactory::make();
        $res = $shippingProvider->cancelShipment($order['shiprocket_shipment_id']);

        if (!empty($res['success'])) {
            $this->orderModel->update($id, ['shipping_status' => 'cancelled']);
            activity_log('Cancel Shipment', 'Orders', $id, "Cancelled Shiprocket shipment for order ID {$id}");
            $this->setFlash('success', 'Shipment cancelled successfully on Shiprocket.');
        } else {
            $this->setFlash('error', 'Shipment cancellation failed: ' . ($res['message'] ?? 'Error'));
        }

        $this->redirect(url("admin/orders/{$id}"));
    }
}

