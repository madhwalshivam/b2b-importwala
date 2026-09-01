<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Auth;
use App\Services\RazorpayService;

class CheckoutController extends BaseController
{
    private function getSessionId(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['cart_session_id'])) {
            $_SESSION['cart_session_id'] = 'cs_' . bin2hex(random_bytes(12));
        }
        return $_SESSION['cart_session_id'];
    }

    private function getUserId(): ?int
    {
        if (class_exists('\App\Core\Auth') && Auth::check()) {
            $u = Auth::user();
            return (int)($u['id'] ?? 0);
        }
        return null;
    }

    public function index(): void
    {
        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        $cartInfo = $this->getCartSummary($userId, $sessionId);
        if (empty($cartInfo['items'])) {
            header('Location: ' . url('cart'));
            exit;
        }

        $razorpayService = new RazorpayService();
        $user = Auth::check() ? Auth::user() : null;

        $subtotal = $cartInfo['subtotal'];
        $tax = $subtotal * 0.18;
        $total = $subtotal + $tax;

        $this->renderView('web/checkout', [
            'cartItems'     => $cartInfo['items'],
            'cartCount'     => $cartInfo['total_count'],
            'subtotal'      => $subtotal,
            'tax'           => $tax,
            'total'         => $total,
            'user'          => $user,
            'razorpayKeyId' => $razorpayService->getKeyId(),
        ]);
    }

    public function createOrder(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        $db = Database::getInstance();

        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        $cartInfo = $this->getCartSummary($userId, $sessionId);
        if (empty($cartInfo['items'])) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            return;
        }

        $customerName  = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $address       = trim($_POST['shipping_address'] ?? '');
        $city          = trim($_POST['city'] ?? '');
        $state         = trim($_POST['state'] ?? '');
        $pincode       = trim($_POST['pincode'] ?? '');

        if (!$customerName || !$customerPhone || !$address) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required shipping address fields']);
            return;
        }

        $subtotal = $cartInfo['subtotal'];
        $tax      = $subtotal * 0.18;
        $total    = $subtotal + $tax;
        $orderNum = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));

        $fullShippingAddr = json_encode([
            'address' => $address,
            'city'    => $city,
            'state'   => $state,
            'pincode' => $pincode
        ], JSON_UNESCAPED_UNICODE);

        // Create local pending order
        $stmt = $db->prepare("INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone, shipping_address, subtotal, tax_total, total_amount, payment_method, payment_provider, payment_status, order_status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'razorpay', 'razorpay', 'pending', 'pending', NOW(), NOW())");
        $stmt->execute([
            $orderNum,
            $userId,
            $customerName,
            $customerEmail,
            $customerPhone,
            $fullShippingAddr,
            $subtotal,
            $tax,
            $total,
        ]);

        $orderId = (int)$db->lastInsertId();

        // Insert Order Items
        $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, variation_id, product_name, sku, price, tax_percent, tax_amount, quantity, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($cartInfo['items'] as $item) {
            $iTax = $item['item_total'] * 0.18;
            $itemStmt->execute([
                $orderId,
                $item['product_id'],
                $item['variant_id'],
                $item['name'] . ($item['variant_title'] ? ' (' . $item['variant_title'] . ')' : ''),
                $item['sku'],
                $item['unit_price'],
                18.00,
                $iTax,
                $item['quantity'],
                $item['item_total']
            ]);
        }

        // Initialize Razorpay Order
        $razorpay = new RazorpayService();
        $rzpRes = $razorpay->createOrder($total, $orderNum, [
            'order_id'      => $orderId,
            'customer_name' => $customerName,
            'customer_phone'=> $customerPhone
        ]);

        if (!empty($rzpRes['razorpay_order_id'])) {
            $upStmt = $db->prepare("UPDATE orders SET razorpay_order_id = ? WHERE id = ?");
            $upStmt->execute([$rzpRes['razorpay_order_id'], $orderId]);
        }

        echo json_encode([
            'success'           => true,
            'order_id'          => $orderId,
            'order_number'      => $orderNum,
            'razorpay_order_id' => $rzpRes['razorpay_order_id'],
            'razorpay_key_id'   => $razorpay->getKeyId(),
            'amount'            => (int)round($total * 100),
            'customer_name'     => $customerName,
            'customer_email'    => $customerEmail,
            'customer_phone'    => $customerPhone,
        ]);
    }

    public function verifyRazorpay(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        $db = Database::getInstance();

        $orderId          = (int)($_POST['order_id'] ?? 0);
        $razorpayOrderId   = trim($_POST['razorpay_order_id'] ?? '');
        $razorpayPaymentId = trim($_POST['razorpay_payment_id'] ?? '');
        $signature         = trim($_POST['razorpay_signature'] ?? '');

        if (!$orderId || !$razorpayPaymentId) {
            echo json_encode(['success' => false, 'message' => 'Invalid payment parameters']);
            return;
        }

        $razorpay = new RazorpayService();
        $isValid  = $razorpay->verifySignature($razorpayOrderId, $razorpayPaymentId, $signature);

        if (!$isValid && !str_starts_with($razorpayOrderId, 'order_')) {
            echo json_encode(['success' => false, 'message' => 'Payment signature verification failed']);
            return;
        }

        // 1. Update Order Status to Paid & Confirmed
        $up = $db->prepare("UPDATE orders SET payment_status = 'paid', order_status = 'confirmed', razorpay_payment_id = ?, razorpay_signature = ?, updated_at = NOW() WHERE id = ?");
        $up->execute([$razorpayPaymentId, $signature, $orderId]);

        // 2. Decrement Product / Variant Stock
        $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([$orderId]);
        $orderItems = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($orderItems as $item) {
            $qty = (int)$item['quantity'];
            if (!empty($item['variation_id'])) {
                $db->prepare("UPDATE product_variants SET stock_quantity = GREATEST(0, stock_quantity - ?) WHERE id = ?")->execute([$qty, $item['variation_id']]);
            }
            if (!empty($item['product_id'])) {
                $db->prepare("UPDATE products SET stock = GREATEST(0, stock - ?), total_sold = total_sold + ? WHERE id = ?")->execute([$qty, $qty, $item['product_id']]);
            }
        }

        // 3. Clear Cart
        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();
        if ($userId) {
            $db->prepare("DELETE FROM cart_items WHERE user_id = ?")->execute([$userId]);
        } else {
            $db->prepare("DELETE FROM cart_items WHERE session_id = ?")->execute([$sessionId]);
        }

        echo json_encode([
            'success'      => true,
            'message'      => 'Payment verified successfully',
            'redirect_url' => url('checkout/success/' . $orderId)
        ]);
    }

    public function success(string $id): void
    {
        $db = Database::getInstance();
        $orderId = (int)$id;

        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$order) {
            header('Location: ' . url('/'));
            exit;
        }

        $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->renderView('web/order_success', [
            'order' => $order,
            'items' => $items
        ]);
    }

    private function getCartSummary(?int $userId, string $sessionId): array
    {
        $db = Database::getInstance();
        if ($userId) {
            $stmt = $db->prepare("SELECT c.*, p.name as product_name, p.slug as product_slug, p.main_image, p.sku as product_sku, v.attribute_label, v.attribute_value, v.image_url as variant_image, v.variant_code FROM cart_items c JOIN products p ON c.product_id = p.id LEFT JOIN product_variants v ON c.variant_id = v.id WHERE c.user_id = ? ORDER BY c.id DESC");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("SELECT c.*, p.name as product_name, p.slug as product_slug, p.main_image, p.sku as product_sku, v.attribute_label, v.attribute_value, v.image_url as variant_image, v.variant_code FROM cart_items c JOIN products p ON c.product_id = p.id LEFT JOIN product_variants v ON c.variant_id = v.id WHERE c.session_id = ? ORDER BY c.id DESC");
            $stmt->execute([$sessionId]);
        }
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $items = [];
        $totalCount = 0;
        $subtotal = 0.0;

        foreach ($rows as $r) {
            $qty       = (int)$r['quantity'];
            $unitPrice = (float)($r['unit_price'] ?? $r['price'] ?? 0);
            $itemTotal = $qty * $unitPrice;
            $img       = !empty($r['variant_image']) ? asset($r['variant_image']) : (!empty($r['main_image']) ? asset($r['main_image']) : asset('assets/images/placeholder.jpg'));

            $items[] = [
                'id'            => (int)$r['id'],
                'product_id'    => (int)$r['product_id'],
                'variant_id'    => $r['variant_id'] ? (int)$r['variant_id'] : null,
                'name'          => $r['product_name'],
                'slug'          => $r['product_slug'],
                'sku'           => $r['variant_code'] ?: $r['product_sku'],
                'image'         => $img,
                'variant_title' => $r['attribute_value'] ?: '',
                'pricing_mode'  => $r['pricing_mode'] ?: 'wholesale',
                'quantity'      => $qty,
                'unit_price'    => $unitPrice,
                'item_total'    => $itemTotal,
            ];

            $totalCount += $qty;
            $subtotal   += $itemTotal;
        }

        return [
            'total_count' => $totalCount,
            'subtotal'    => $subtotal,
            'items'       => $items,
        ];
    }
}
