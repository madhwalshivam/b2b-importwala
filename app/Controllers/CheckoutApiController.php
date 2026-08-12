<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Order;
use App\Services\NotificationService;
use App\Services\CouponService;
use Lib\Payment\PaymentGatewayFactory;
use Lib\Shipping\ShippingProviderFactory;

class CheckoutApiController extends Controller {

    /**
     * Create Razorpay Order endpoint (/api/checkout/create-order)
     */
    public function createOrder(): void {
        header('Content-Type: application/json');

        if (!check_api_rate_limit('checkout_create', 30, 60)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => 'Too many payment requests. Please wait a moment.']);
            exit;
        }

        if (empty($_SESSION['cart'])) {
            echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
            exit;
        }

        if (empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login to complete your order.']);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $cartData = $this->getCartData();
        if (empty($cartData['items']) || (float)$cartData['grand_total'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart total.']);
            exit;
        }

        $name = trim((string)$this->request->input('name', ''));
        $email = trim((string)$this->request->input('email', ''));
        $phone = trim((string)$this->request->input('phone', ''));
        $addressLine1 = trim((string)$this->request->input('address_line1', ''));
        $addressLine2 = trim((string)$this->request->input('address_line2', ''));
        $city = trim((string)$this->request->input('city', ''));
        $state = trim((string)$this->request->input('state', ''));
        $pincode = trim((string)$this->request->input('pincode', ''));
        $country = trim((string)$this->request->input('country', 'India'));

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $cleanPincode = preg_replace('/[^0-9]/', '', $pincode);

        if (empty($name) || empty($email) || strlen($cleanPhone) < 10 || empty($addressLine1) || empty($city) || empty($state) || strlen($cleanPincode) !== 6) {
            echo json_encode(['success' => false, 'message' => 'Please complete all required shipping address fields.']);
            exit;
        }

        $addressSnapshot = [
            'full_name' => $name,
            'phone' => $cleanPhone,
            'email' => $email,
            'address_line1' => $addressLine1,
            'address_line2' => $addressLine2,
            'city' => $city,
            'state' => $state,
            'pincode' => $cleanPincode,
            'country' => $country
        ];

        $db = Database::getInstance();
        $orderModel = new Order();
        $orderNumber = $orderModel->generateOrderNumber();
        $grandTotal = (float)$cartData['grand_total'];

        try {
            $db->beginTransaction();

            // Stock Verification & Lock
            $stmtLock = $db->prepare("SELECT id, name, stock FROM products WHERE id = ? FOR UPDATE");
            foreach ($cartData['items'] as $item) {
                $stmtLock->execute([$item['id']]);
                $prod = $stmtLock->fetch(\PDO::FETCH_ASSOC);
                if (!$prod || (int)$prod['stock'] < (int)$item['quantity']) {
                    $db->rollBack();
                    echo json_encode(['success' => false, 'message' => "Insufficient stock for '{$item['name']}'."]);
                    exit;
                }
            }

            // Call Payment Gateway Factory to create Order on Razorpay API
            $paymentGateway = PaymentGatewayFactory::make();
            $gwResult = $paymentGateway->createOrder($grandTotal, 'INR', $orderNumber, [
                'name' => $name,
                'email' => $email,
                'phone' => $cleanPhone
            ]);

            if (empty($gwResult['success']) || empty($gwResult['gateway_order_id'])) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Failed to initialize payment with gateway. ' . ($gwResult['error_message'] ?? '')]);
                exit;
            }

            $razorpayOrderId = $gwResult['gateway_order_id'];
            $keyId = $gwResult['key_id'];

            // Insert Pending Order in Database
            $orderId = $orderModel->insert([
                'order_number' => $orderNumber,
                'customer_id' => $userId,
                'user_id' => $userId,
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $cleanPhone,
                'shipping_address' => json_encode($addressSnapshot),
                'subtotal' => $cartData['subtotal'],
                'tax_total' => $cartData['tax_total'],
                'shipping_charge' => $cartData['shipping_charge'],
                'discount_amount' => $cartData['discount'],
                'coupon_code' => $cartData['coupon_code'],
                'total_amount' => $grandTotal,
                'payment_method' => 'razorpay',
                'payment_provider' => 'razorpay',
                'payment_status' => 'pending',
                'razorpay_order_id' => $razorpayOrderId,
                'order_status' => 'pending',
                'shipping_status' => 'not_shipped'
            ]);

            // Insert Order Items
            $stmtItem = $db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, sku, price, tax_percent, tax_amount, quantity, total_amount, weight_kg)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($cartData['items'] as $item) {
                $lineSub = $item['price'] * $item['quantity'];
                $lineTax = $lineSub * ($item['tax_percent'] / 100);
                $stmtItem->execute([
                    $orderId,
                    $item['id'],
                    $item['name'],
                    $item['sku'],
                    $item['price'],
                    $item['tax_percent'],
                    $lineTax,
                    $item['quantity'],
                    $lineSub + $lineTax,
                    0.50
                ]);
            }

            $db->commit();

            $_SESSION['pending_order_id'] = $orderId;

            echo json_encode([
                'success' => true,
                'is_mock' => !empty($gwResult['is_mock']) || str_contains($keyId, 'placeholder'),
                'razorpay_order_id' => $razorpayOrderId,
                'key_id' => $keyId,
                'amount' => (int)round($grandTotal * 100),
                'currency' => 'INR',
                'order_number' => $orderNumber,
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $cleanPhone
            ]);
            exit;

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Create Order API Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Order creation failed: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Verify Razorpay Payment Signature endpoint (/api/checkout/verify-payment)
     */
    public function verifyPayment(): void {
        header('Content-Type: application/json');

        $razorpayOrderId = trim((string)$this->request->input('razorpay_order_id', ''));
        $razorpayPaymentId = trim((string)$this->request->input('razorpay_payment_id', ''));
        $razorpaySignature = trim((string)$this->request->input('razorpay_signature', ''));

        if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing payment parameters for verification.']);
            exit;
        }

        $paymentGateway = PaymentGatewayFactory::make();
        $isValid = $paymentGateway->verifyPayment([
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => $razorpayPaymentId,
            'razorpay_signature' => $razorpaySignature
        ]);

        if (!$isValid) {
            http_response_code(400);
            error_log("Payment Signature Verification Failed for Razorpay Order: {$razorpayOrderId}");
            echo json_encode(['success' => false, 'message' => 'Payment signature verification failed. Payment rejected for security.']);
            exit;
        }

        $db = Database::getInstance();
        $stmtOrder = $db->prepare("SELECT * FROM orders WHERE razorpay_order_id = ? LIMIT 1");
        $stmtOrder->execute([$razorpayOrderId]);
        $order = $stmtOrder->fetch(\PDO::FETCH_ASSOC);

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found in system.']);
            exit;
        }

        $orderId = (int)$order['id'];
        $orderNumber = $order['order_number'];

        // Update Order to PAID
        $stmtUpdate = $db->prepare("
            UPDATE orders SET
                payment_status = 'paid',
                order_status = 'confirmed',
                razorpay_payment_id = ?,
                razorpay_signature = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmtUpdate->execute([$razorpayPaymentId, $razorpaySignature, $orderId]);

        // Decrement Product Stock
        $stmtItems = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);

        $stmtStock = $db->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?");
        foreach ($items as $itm) {
            if (!empty($itm['product_id'])) {
                $stmtStock->execute([$itm['quantity'], $itm['product_id']]);
            }
        }

        // Record Coupon Usage if applicable
        if (!empty($_SESSION['applied_coupon']['coupon_id'])) {
            CouponService::recordUsage(
                (int)$_SESSION['applied_coupon']['coupon_id'],
                (int)($order['customer_id'] ?? $_SESSION['user_id']),
                session_id(),
                $orderId,
                (float)($order['discount_amount'] ?? 0)
            );
        }

        // Push Order Automatically to Shiprocket
        self::pushOrderToShiprocket($orderId);

        // Clear Cart Session
        unset($_SESSION['cart']);
        unset($_SESSION['applied_coupon']);
        unset($_SESSION['pending_order_id']);

        echo json_encode([
            'success' => true,
            'message' => 'Payment verified successfully!',
            'redirect_url' => url("order-success/{$orderNumber}")
        ]);
        exit;
    }

    /**
     * Razorpay Webhook Endpoint (/api/webhooks/razorpay)
     */
    public function razorpayWebhook(): void {
        $rawBody = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        if (empty($rawBody) || empty($signature)) {
            http_response_code(400);
            echo "Missing webhook body or signature header.";
            exit;
        }

        $pgSettings = PaymentGatewayFactory::getSettings();
        $webhookSecret = \App\Helpers\Encryption::decrypt($pgSettings['webhook_secret'] ?? '');

        if (!empty($webhookSecret)) {
            $expectedSignature = hash_hmac('sha256', $rawBody, $webhookSecret);
            if (!hash_equals($expectedSignature, $signature)) {
                http_response_code(400);
                echo "Invalid webhook signature.";
                exit;
            }
        }

        $payload = json_decode($rawBody, true);
        $event = $payload['event'] ?? '';

        if (in_array($event, ['payment.captured', 'order.paid'])) {
            $paymentEntity = $payload['payload']['payment']['entity'] ?? [];
            $razorpayOrderId = $paymentEntity['order_id'] ?? '';
            $razorpayPaymentId = $paymentEntity['id'] ?? '';

            if (!empty($razorpayOrderId)) {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT id, payment_status FROM orders WHERE razorpay_order_id = ? LIMIT 1");
                $stmt->execute([$razorpayOrderId]);
                $order = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($order && $order['payment_status'] !== 'paid') {
                    $orderId = (int)$order['id'];
                    $stmtUp = $db->prepare("UPDATE orders SET payment_status = 'paid', order_status = 'confirmed', razorpay_payment_id = ? WHERE id = ?");
                    $stmtUp->execute([$razorpayPaymentId, $orderId]);

                    // Automatically Push to Shiprocket
                    self::pushOrderToShiprocket($orderId);
                }
            }
        }

        http_response_code(200);
        echo "Webhook processed.";
        exit;
    }

    /**
     * Shiprocket Webhook Endpoint (/api/webhooks/shiprocket)
     * Real-time shipping status updates from Shiprocket
     */
    public function shiprocketWebhook(): void {
        header('Content-Type: application/json');

        $rawBody = file_get_contents('php://input');
        $data = json_decode($rawBody, true);

        if (empty($data)) {
            $data = $_REQUEST;
        }

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Empty webhook payload']);
            exit;
        }

        $orderNumber = trim((string)($data['order_id'] ?? $data['channel_order_id'] ?? $data['sr_order_id'] ?? ''));
        $awbCode     = trim((string)($data['awb'] ?? $data['awb_code'] ?? ''));
        $srStatus    = trim((string)($data['current_status'] ?? $data['shipment_status'] ?? $data['status'] ?? ''));
        $courierName = trim((string)($data['courier_name'] ?? $data['courier'] ?? ''));

        if (empty($orderNumber) && empty($awbCode)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing order_id or awb identifier']);
            exit;
        }

        $db = Database::getInstance();
        $order = null;

        if (!empty($orderNumber)) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? OR shiprocket_order_id = ? LIMIT 1");
            $stmt->execute([$orderNumber, $orderNumber]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if (!$order && !empty($awbCode)) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE awb_code = ? OR tracking_number = ? LIMIT 1");
            $stmt->execute([$awbCode, $awbCode]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if (!$order) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Matching order not found']);
            exit;
        }

        $orderId = (int)$order['id'];
        $mapped = self::mapShiprocketStatus($srStatus);

        $updateFields = [];
        $updateParams = [];

        if (!empty($mapped['shipping_status'])) {
            $updateFields[] = "shipping_status = ?";
            $updateParams[] = $mapped['shipping_status'];
        }

        if (!empty($mapped['order_status'])) {
            $updateFields[] = "order_status = ?";
            $updateParams[] = $mapped['order_status'];
        }

        if (!empty($awbCode)) {
            $updateFields[] = "awb_code = ?";
            $updateParams[] = $awbCode;
            $updateFields[] = "tracking_number = ?";
            $updateParams[] = $awbCode;
            $updateFields[] = "tracking_url = ?";
            $updateParams[] = 'https://shiprocket.co/tracking/' . $awbCode;
        }

        if (!empty($courierName)) {
            $updateFields[] = "courier_name = ?";
            $updateParams[] = $courierName;
        }

        $updateFields[] = "updated_at = NOW()";

        if (!empty($updateFields)) {
            $updateParams[] = $orderId;
            $sql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmtUp = $db->prepare($sql);
            $stmtUp->execute($updateParams);
        }

        activity_log('Shiprocket Webhook Status Update', 'Orders', $orderId, "Updated order #{$order['order_number']} status to {$srStatus} (Shipping: {$mapped['shipping_status']})");

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => "Order #{$order['order_number']} updated successfully to {$srStatus}",
            'shipping_status' => $mapped['shipping_status'] ?? $order['shipping_status'],
            'order_status' => $mapped['order_status'] ?? $order['order_status']
        ]);
        exit;
    }

    /**
     * Map Shiprocket Status strings to system shipping_status and order_status
     */
    public static function mapShiprocketStatus(string $srStatus): array {
        $status = strtoupper(trim($srStatus));

        switch ($status) {
            case 'DELIVERED':
            case 'FULFILLED':
            case 'COMPLETED':
                return ['shipping_status' => 'delivered', 'order_status' => 'completed'];

            case 'SHIPPED':
            case 'IN TRANSIT':
            case 'OUT FOR DELIVERY':
            case 'PICKED UP':
            case 'PICKUP SCHEDULED':
            case 'DISPATCHED':
            case 'REACHED AT DESTINATION HUB':
            case 'OUT FOR PICKUP':
                return ['shipping_status' => 'shipped', 'order_status' => 'confirmed'];

            case 'CANCELED':
            case 'CANCELLED':
            case 'RTO IN TRANSIT':
            case 'RTO DELIVERED':
            case 'RTO INITIATED':
            case 'RETURNED':
                return ['shipping_status' => 'cancelled', 'order_status' => 'cancelled'];

            case 'NEW':
            case 'PROCESSING':
            case 'PACKED':
            case 'READY TO SHIP':
            default:
                return ['shipping_status' => 'processing', 'order_status' => 'confirmed'];
        }
    }

    /**
     * Track Order Endpoint (/api/orders/track)
     */
    public function trackOrder(): void {
        header('Content-Type: application/json');

        $orderId = $this->request->input('order_id', $this->request->input('id', ''));
        $awbCode = $this->request->input('awb', '');

        $db = Database::getInstance();
        $targetOrder = null;

        if (empty($awbCode) && !empty($orderId)) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? OR order_number = ? LIMIT 1");
            $stmt->execute([$orderId, $orderId]);
            $targetOrder = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($targetOrder && !empty($targetOrder['awb_code'])) {
                $awbCode = $targetOrder['awb_code'];
            }
        }

        if (empty($awbCode)) {
            echo json_encode(['success' => false, 'message' => 'No tracking number or AWB code available for this order yet.']);
            exit;
        }

        $shippingProvider = ShippingProviderFactory::make();
        $trackingData = $shippingProvider->getTrackingStatus($awbCode);

        // Auto-sync status into DB if tracking returned valid status
        if (!empty($trackingData['status']) && $targetOrder) {
            $mapped = self::mapShiprocketStatus($trackingData['status']);
            if (!empty($mapped['shipping_status']) && $mapped['shipping_status'] !== $targetOrder['shipping_status']) {
                $stmtUp = $db->prepare("UPDATE orders SET shipping_status = ?, order_status = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$mapped['shipping_status'], $mapped['order_status'], $targetOrder['id']]);
                $targetOrder['shipping_status'] = $mapped['shipping_status'];
                $targetOrder['order_status'] = $mapped['order_status'];
            }
        }

        echo json_encode([
            'success' => true,
            'awb_code' => $awbCode,
            'shipping_status' => $targetOrder['shipping_status'] ?? 'processing',
            'tracking' => $trackingData
        ]);
        exit;
    }

    /**
     * Helper to push paid order to Shiprocket
     */
    public static function pushOrderToShiprocket(int $orderId): array {
        try {
            $db = Database::getInstance();
            $stmtOrder = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
            $stmtOrder->execute([$orderId]);
            $order = $stmtOrder->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }

            $stmtItems = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmtItems->execute([$orderId]);
            $items = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);

            $shippingProvider = ShippingProviderFactory::make();
            $result = $shippingProvider->createShipment([
                'order_number' => $order['order_number'],
                'customer_name' => $order['customer_name'],
                'customer_email' => $order['customer_email'],
                'customer_phone' => $order['customer_phone'],
                'address' => json_decode($order['shipping_address'] ?? '{}', true),
                'total_amount' => $order['total_amount'],
                'payment_method' => $order['payment_method'],
                'items' => $items
            ]);

            if (!empty($result['success'])) {
                $stmtUpdate = $db->prepare("
                    UPDATE orders SET
                        shipping_status = 'processing',
                        shiprocket_order_id = ?,
                        shiprocket_shipment_id = ?,
                        awb_code = ?,
                        courier_name = ?,
                        tracking_url = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmtUpdate->execute([
                    $result['shiprocket_order_id'] ?? null,
                    $result['shipment_id'] ?? null,
                    $result['awb_code'] ?? null,
                    $result['courier_name'] ?? 'Shiprocket Courier',
                    $result['tracking_url'] ?? null,
                    $orderId
                ]);
            }

            return $result;
        } catch (\Throwable $e) {
            error_log("Push to Shiprocket exception: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function getCartData(): array {
        $cartController = new CartController();
        $ref = new \ReflectionClass($cartController);
        $method = $ref->getMethod('getCart');
        $method->setAccessible(true);
        return $method->invoke($cartController);
    }
}
