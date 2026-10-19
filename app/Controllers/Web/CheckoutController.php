<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Services\GstCalculator;
use App\Services\GstService;
use App\Services\PaymentFulfillmentService;
use App\Services\RazorpayService;

class CheckoutController extends BaseController
{
    public function index(): void
    {
        \App\Services\CustomerAccess::requireCommerce('checkout', 'order');
        $source = ($_GET['source'] ?? '') === 'buynow' ? 'buynow' : 'cart';
        if (!empty($_GET['buy_product'])) {
            $built = $this->buildBuyNow(
                (int)$_GET['buy_product'],
                $this->linesFromQuery(),
                (string)($_GET['buy_mode'] ?? 'onepiece')
            );
            if (empty($built['ok'])) {
                $this->renderCheckoutError($built['message'] ?? 'This item cannot be purchased online.');
                return;
            }
            $_SESSION['buy_now'] = $built;
            $source = 'buynow';
        }

        if ($source !== 'buynow') {
            unset($_SESSION['buy_now']);
        }

        if ($source === 'buynow') {
            $snap = $_SESSION['buy_now'] ?? null;
            if (empty($snap['items'])) {
                $this->renderCheckoutError('Choose a product before paying.');
                return;
            }
            $items = $snap['items'];
            $subtotal = (float)$snap['subtotal'];
        } else {
            $userId = get_current_user_id();
            $sessionId = get_current_session_id();
            $this->recalculateCart($userId, $sessionId);
            $cart = $this->cartSummary($userId, $sessionId);
            if (empty($cart['items'])) {
                header('Location: ' . url('cart'));
                exit;
            }
            $items = $cart['items'];
            $subtotal = (float)$cart['subtotal'];
        }

        $quote = (new GstService())->quote($items, null);
        $subtotal = (float)$quote['items_total'];
        $tax = (float)$quote['total_gst'];
        $total = (float)$quote['grand_total'];
        $gateway = new RazorpayService();
        $user = $this->currentUser();
        $displayCurrency = strtoupper((string)($_SESSION['user_currency'] ?? $_COOKIE['user_currency'] ?? 'INR'));

        $this->renderView('web/checkout', [
            'cartItems' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'gst' => $quote,
            'states' => GstCalculator::STATES,
            'user' => $user,
            'source' => $source,
            'paymentReady' => $gateway->isReady(),
            'paymentMessage' => $gateway->isReady() ? '' : $gateway->getBlockReason(),
            'displayCurrency' => $displayCurrency,
            'checkoutError' => '',
        ]);
    }

    public function prepareBuyNow(): void
    {
        \App\Services\CustomerAccess::requireCommerce('checkout', 'order');
        $this->jsonHeaders();
        $productId = (int)($_POST['product_id'] ?? 0);
        $mode = (string)($_POST['pricing_mode'] ?? 'onepiece');
        $lines = [];
        if (!empty($_POST['lines']) && is_array($_POST['lines'])) {
            foreach ($_POST['lines'] as $line) {
                if (!is_array($line)) {
                    continue;
                }
                $lines[] = [
                    'variant_id' => (int)($line['variant_id'] ?? 0),
                    'qty' => (int)($line['qty'] ?? 0),
                ];
            }
        }
        if (!$lines) {
            $lines[] = [
                'variant_id' => (int)($_POST['variant_id'] ?? 0),
                'qty' => (int)($_POST['qty'] ?? 1),
            ];
        }

        $built = $this->buildBuyNow($productId, $lines, $mode);
        if (empty($built['ok'])) {
            echo json_encode(['success' => false, 'message' => $built['message'] ?? 'Unable to start checkout.']);
            return;
        }

        $_SESSION['buy_now'] = $built;
        echo json_encode([
            'success' => true,
            'redirect_url' => url('checkout?source=buynow'),
        ]);
    }

    public function createOrder(): void
    {
        \App\Services\CustomerAccess::requireCommerce('checkout', 'order');
        $this->jsonHeaders();
        $gateway = new RazorpayService();
        if (!$gateway->isReady()) {
            echo json_encode(['success' => false, 'message' => $gateway->getBlockReason()]);
            return;
        }

        $source = (($_POST['source'] ?? '') === 'buynow') ? 'buynow' : 'cart';
        if ($source === 'buynow') {
            $snap = $_SESSION['buy_now'] ?? null;
            if (empty($snap['items'])) {
                echo json_encode(['success' => false, 'message' => 'Your Buy Now selection expired. Please choose the product again.']);
                return;
            }
            $fresh = $this->buildBuyNow((int)$snap['product_id'], $snap['lines'], (string)$snap['pricing_mode']);
            if (empty($fresh['ok'])) {
                echo json_encode(['success' => false, 'message' => $fresh['message'] ?? 'This item cannot be purchased right now.']);
                return;
            }
            $items = $fresh['items'];
            $subtotal = (float)$fresh['subtotal'];
        } else {
            $userId = get_current_user_id();
            $sessionId = get_current_session_id();
            $this->recalculateCart($userId, $sessionId);
            $cart = $this->cartSummary($userId, $sessionId);
            if (empty($cart['items'])) {
                echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
                return;
            }
            $items = $cart['items'];
            $subtotal = (float)$cart['subtotal'];
        }

        $name = trim((string)($_POST['customer_name'] ?? ''));
        $email = trim((string)($_POST['customer_email'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string)($_POST['customer_phone'] ?? ''));
        $address = trim((string)($_POST['shipping_address'] ?? ''));
        $city = trim((string)($_POST['city'] ?? ''));
        $state = trim((string)($_POST['state'] ?? ''));
        $pincode = preg_replace('/\D+/', '', (string)($_POST['pincode'] ?? ''));

        if ($name === '' || strlen($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) < 10 || $address === '' || $city === '' || $state === '' || strlen($pincode) !== 6) {
            echo json_encode(['success' => false, 'message' => 'Please complete your name, email, 10-digit mobile, address, city, state and 6-digit PIN code.']);
            return;
        }
        $phone = substr($phone, -10);
        $state = GstCalculator::normalizeState($state);
        if (!in_array($state, GstCalculator::STATES, true)) {
            echo json_encode(['success' => false, 'message' => 'Choose a state from the list so GST can be split correctly.']);
            return;
        }

        $quote = (new GstService())->quote($items, $state);
        $subtotal = (float)$quote['items_total'];
        $tax = (float)$quote['total_gst'];
        $total = (float)$quote['grand_total'];
        $orderNum = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));
        $userId = get_current_user_id();
        $sessionId = get_current_session_id();

        $addr = json_encode([
            'address' => $address,
            'address_line1' => $address,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode,
            'country' => 'India',
        ], JSON_UNESCAPED_UNICODE);

        $db = Database::getInstance();
        try {
            $db->beginTransaction();
            $insertCols = ['order_number', 'customer_name', 'customer_email', 'customer_phone', 'shipping_address', 'subtotal', 'tax_total', 'shipping_charge', 'total_amount', 'payment_method', 'payment_status', 'order_status'];
            $insertVals = [$orderNum, $name, $email, $phone, $addr, $subtotal, $tax, 0, $total, 'razorpay', 'pending', 'pending'];
            if ($this->columnExists($db, 'orders', 'user_id')) {
                $insertCols[] = 'user_id';
                $insertVals[] = $userId ?: null;
            }
            if ($this->columnExists($db, 'orders', 'payment_provider')) {
                $insertCols[] = 'payment_provider';
                $insertVals[] = 'razorpay';
            }
            if ($this->columnExists($db, 'orders', 'checkout_source')) {
                $insertCols[] = 'checkout_source';
                $insertVals[] = $source;
            }
            if ($this->columnExists($db, 'orders', 'cart_session_id')) {
                $insertCols[] = 'cart_session_id';
                $insertVals[] = $sessionId;
            }
            foreach ([
                'total_taxable_amount' => $quote['total_taxable_amount'],
                'total_cgst' => $quote['total_cgst'],
                'total_sgst' => $quote['total_sgst'],
                'total_igst' => $quote['total_igst'],
                'total_gst' => $quote['total_gst'],
                'shipping_state' => $state,
            ] as $col => $val) {
                if ($this->columnExists($db, 'orders', $col)) {
                    $insertCols[] = $col;
                    $insertVals[] = $val;
                }
            }
            $ph = implode(', ', array_fill(0, count($insertCols), '?'));
            $sql = "INSERT INTO orders (" . implode(', ', $insertCols) . ", created_at, updated_at) VALUES ({$ph}, NOW(), NOW())";
            $db->prepare($sql)->execute($insertVals);
            $orderId = (int)$db->lastInsertId();

            foreach ($quote['lines'] as $index => $line) {
                $this->insertItem($db, $orderId, array_merge($items[$index] ?? [], $line));
            }

            $rz = $gateway->createOrder($total, $orderNum, [
                'order_number' => $orderNum,
                'customer_name' => $name,
            ]);
            if (empty($rz['success']) || empty($rz['razorpay_order_id'])) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => $rz['message'] ?? 'Payment could not be started.']);
                return;
            }

            $db->prepare("UPDATE orders SET razorpay_order_id = ? WHERE id = ?")->execute([$rz['razorpay_order_id'], $orderId]);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Checkout create order: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'We could not create your order. Please try again.']);
            return;
        }

        if (function_exists('admin_audit_log')) {
            admin_audit_log('Payment Started', 'Order ' . $orderNum . ' pending Razorpay ' . $rz['razorpay_order_id']);
        }

        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $orderNum,
            'razorpay_order_id' => $rz['razorpay_order_id'],
            'razorpay_key_id' => $gateway->getKeyId(),
            'amount' => (int)round($total * 100),
            'currency' => 'INR',
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
        ]);
    }

    public function gstQuote(): void
    {
        \App\Services\CustomerAccess::requireCommerce('checkout', 'order');
        $this->jsonHeaders();
        $source = (($_POST['source'] ?? '') === 'buynow') ? 'buynow' : 'cart';
        if ($source === 'buynow') {
            $snap = $_SESSION['buy_now'] ?? null;
            $items = $snap['items'] ?? [];
        } else {
            $userId = get_current_user_id();
            $sessionId = get_current_session_id();
            $cart = $this->cartSummary($userId, $sessionId);
            $items = $cart['items'];
        }
        $state = GstCalculator::normalizeState($_POST['state'] ?? '');
        if ($state !== '' && !in_array($state, GstCalculator::STATES, true)) {
            $state = '';
        }
        $quote = (new GstService())->quote($items, $state !== '' ? $state : null);
        echo json_encode([
            'success' => true,
            'items_total' => $quote['items_total'],
            'tax' => $quote['total_gst'],
            'grand_total' => $quote['grand_total'],
            'supply' => $quote['supply'],
            'rows' => $quote['rows'],
        ]);
    }

    public function verifyRazorpay(): void
    {
        \App\Services\CustomerAccess::requireCommerce('checkout', 'order');
        $this->jsonHeaders();
        $orderId = (int)($_POST['order_id'] ?? 0);
        $rzOrder = trim((string)($_POST['razorpay_order_id'] ?? ''));
        $rzPay = trim((string)($_POST['razorpay_payment_id'] ?? ''));
        $sig = trim((string)($_POST['razorpay_signature'] ?? ''));
        $method = trim((string)($_POST['method'] ?? ''));

        $gateway = new RazorpayService();
        if (!$gateway->verifySignature($rzOrder, $rzPay, $sig)) {
            error_log('Razorpay signature rejected for order row ' . $orderId);
            (new PaymentFulfillmentService())->markFailed($rzOrder, $rzPay, 'signature mismatch');
            echo json_encode(['success' => false, 'message' => 'Payment could not be verified. If money was deducted, it will be confirmed automatically, otherwise you can try again.']);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM orders WHERE id = ? AND razorpay_order_id = ? LIMIT 1");
        $stmt->execute([$orderId, $rzOrder]);
        if (!$stmt->fetchColumn()) {
            echo json_encode(['success' => false, 'message' => 'This payment does not match the order.']);
            return;
        }

        $result = (new PaymentFulfillmentService())->markPaid($rzOrder, $rzPay, $sig, $method !== '' ? $method : null);
        if (empty($result['success'])) {
            echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Payment verification failed.']);
            return;
        }

        if (!empty($result['newly_paid'])) {
            try {
                \App\Controllers\CheckoutApiController::pushOrderToShiprocket((int)$result['order_id']);
            } catch (\Throwable $e) {
                error_log('Shiprocket push after payment: ' . $e->getMessage());
            }
        }

        if (($_POST['source'] ?? '') === 'buynow') {
            unset($_SESSION['buy_now']);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Payment received.',
            'redirect_url' => url('checkout/success/' . (int)$result['order_id']),
        ]);
    }

    public function paymentFailed(): void
    {
        $this->jsonHeaders();
        $rzOrder = trim((string)($_POST['razorpay_order_id'] ?? ''));
        $rzPay = trim((string)($_POST['razorpay_payment_id'] ?? ''));
        $reason = trim((string)($_POST['reason'] ?? 'Payment was not completed'));
        if ($rzOrder !== '' && preg_match('/^order_[A-Za-z0-9]+$/', $rzOrder)) {
            (new PaymentFulfillmentService())->markFailed($rzOrder, $rzPay, $reason);
        }
        echo json_encode(['success' => true, 'message' => 'Payment was not completed. You can try again.']);
    }

    public function success(string $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([(int)$id]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$order || ($order['payment_status'] ?? '') !== 'paid') {
            header('Location: ' . url('cart'));
            exit;
        }
        $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([(int)$order['id']]);
        $this->renderView('web/order_success', [
            'order' => $order,
            'items' => $itemsStmt->fetchAll(\PDO::FETCH_ASSOC),
        ]);
    }

    private function renderCheckoutError(string $message): void
    {
        $this->renderView('web/checkout', [
            'cartItems' => [],
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'gst' => ['rows' => [], 'items_total' => 0, 'grand_total' => 0, 'total_gst' => 0, 'price_mode' => 'exclusive'],
            'states' => GstCalculator::STATES,
            'user' => $this->currentUser(),
            'source' => 'buynow',
            'paymentReady' => false,
            'paymentMessage' => $message,
            'displayCurrency' => 'INR',
            'checkoutError' => $message,
        ]);
    }

    private function linesFromQuery(): array
    {
        return [[
            'variant_id' => (int)($_GET['buy_variant'] ?? 0),
            'qty' => (int)($_GET['buy_qty'] ?? 1),
        ]];
    }

    private function buildBuyNow(int $productId, array $lines, string $mode): array
    {
        if (!in_array($mode, ['wholesale', 'onepiece'], true)) {
            $mode = 'onepiece';
        }
        if ($productId < 1 || !$lines) {
            return ['ok' => false, 'message' => 'Please choose a product to buy.'];
        }

        $db = Database::getInstance();
        $pStmt = $db->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $pStmt->execute([$productId]);
        $product = $pStmt->fetch(\PDO::FETCH_ASSOC);
        if (!$product) {
            return ['ok' => false, 'message' => 'This product is no longer available.'];
        }

        $moq = max(1, (int)($product['moq'] ?? 1));
        $items = [];
        $normalized = [];
        foreach ($lines as $line) {
            $qty = (int)($line['qty'] ?? 0);
            $variantId = (int)($line['variant_id'] ?? 0);
            if ($qty < 1) {
                continue;
            }
            if ($qty > 9999) {
                return ['ok' => false, 'message' => 'Quantity is too high.'];
            }
            $min = $mode === 'wholesale' ? $moq : 1;
            if ($qty < $min) {
                return ['ok' => false, 'message' => 'Minimum quantity for this price is ' . $min . '.'];
            }

            $variant = null;
            if ($variantId > 0) {
                $vStmt = $db->prepare("SELECT * FROM product_variants WHERE id = ? AND product_id = ? LIMIT 1");
                $vStmt->execute([$variantId, $productId]);
                $variant = $vStmt->fetch(\PDO::FETCH_ASSOC) ?: null;
                if (!$variant) {
                    return ['ok' => false, 'message' => 'The selected option is not available.'];
                }
                $stock = (int)($variant['stock_quantity'] ?? $variant['stock'] ?? 0);
                if ($stock > 0 && $qty > $stock) {
                    return ['ok' => false, 'message' => 'Only ' . $stock . ' units are in stock for the selected option.'];
                }
            }

            $unit = $this->calculateUnitPrice($productId, $variantId ?: null, $qty, $mode, $product, $variant);
            if ($unit <= 0) {
                return ['ok' => false, 'message' => 'This item does not have an online price. Please request a quote.'];
            }

            $label = '';
            if ($variant) {
                $label = trim((string)($variant['attribute_value'] ?? $variant['sku'] ?? ''));
            }
            $items[] = [
                'product_id' => $productId,
                'variant_id' => $variantId ?: null,
                'name' => (string)$product['name'],
                'sku' => $variant['sku'] ?? ($product['sku'] ?? ''),
                'image' => !empty($variant['image_url']) ? asset($variant['image_url']) : (!empty($product['main_image']) ? asset($product['main_image']) : asset('assets/images/placeholder.jpg')),
                'variant_title' => $label,
                'quantity' => $qty,
                'unit_price' => $unit,
                'item_total' => round($qty * $unit, 2),
            ];
            $normalized[] = ['variant_id' => $variantId, 'qty' => $qty];
        }

        if (!$items) {
            return ['ok' => false, 'message' => 'Set a quantity before buying.'];
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += $item['item_total'];
        }

        return [
            'ok' => true,
            'product_id' => $productId,
            'pricing_mode' => $mode,
            'lines' => $normalized,
            'items' => $items,
            'subtotal' => round($subtotal, 2),
        ];
    }

    private function insertItem(\PDO $db, int $orderId, array $item): void
    {
        $lineTax = round((float)($item['total_gst_amount'] ?? $item['gst_amount'] ?? 0), 2);
        $rate = round((float)($item['gst_rate'] ?? $item['rate'] ?? 0), 2);
        $lineTotal = round((float)($item['gross_amount'] ?? $item['item_total'] ?? 0), 2);
        $cols = ['order_id', 'product_id', 'product_name', 'sku', 'price', 'tax_percent', 'tax_amount', 'quantity', 'total_amount'];
        $name = $item['name'] . (!empty($item['variant_title']) ? ' (' . $item['variant_title'] . ')' : '');
        $vals = [$orderId, $item['product_id'], $name, (string)$item['sku'], $item['unit_price'], $rate, $lineTax, $item['quantity'], $lineTotal];
        $snapshots = [
            'hsn_code' => ($item['hsn_code'] ?? '') !== '' ? $item['hsn_code'] : null,
            'gst_rate' => $rate,
            'taxable_amount' => round((float)($item['taxable_amount'] ?? 0), 2),
            'cgst_amount' => round((float)($item['cgst_amount'] ?? 0), 2),
            'sgst_amount' => round((float)($item['sgst_amount'] ?? 0), 2),
            'igst_amount' => round((float)($item['igst_amount'] ?? 0), 2),
            'total_gst_amount' => $lineTax,
        ];
        foreach ($snapshots as $col => $val) {
            if ($this->columnExists($db, 'order_items', $col)) {
                $cols[] = $col;
                $vals[] = $val;
            }
        }
        if ($this->columnExists($db, 'order_items', 'variation_id')) {
            $cols[] = 'variation_id';
            $vals[] = $item['variant_id'];
        }
        if ($this->columnExists($db, 'order_items', 'variant_id')) {
            $cols[] = 'variant_id';
            $vals[] = $item['variant_id'];
        }
        if ($this->columnExists($db, 'order_items', 'variant_label')) {
            $cols[] = 'variant_label';
            $vals[] = $item['variant_title'] ?: null;
        }
        $ph = implode(', ', array_fill(0, count($cols), '?'));
        $db->prepare("INSERT INTO order_items (" . implode(', ', $cols) . ") VALUES ({$ph})")->execute($vals);
    }

    private function currentUser(): ?array
    {
        $id = $_SESSION['user_id'] ?? null;
        if ($id === null || $id === '') {
            return null;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([(string)$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                if (empty($row['name'])) {
                    $row['name'] = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
                }
                return $row;
            }

            $stmt = $db->prepare("SELECT id, name, email, phone FROM customers WHERE id = ? LIMIT 1");
            $stmt->execute([(string)$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('Checkout user prefill: ' . $e->getMessage());
            return null;
        }
    }

    private function jsonHeaders(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
    }

    private function calculateUnitPrice(int $productId, ?int $variantId, int $qty, string $mode, array $product, ?array $variant): float
    {
        if ($mode === 'onepiece') {
            if ($variant && (float)($variant['one_piece_price'] ?? 0) > 0) {
                return (float)$variant['one_piece_price'];
            }
            if (!empty($product['sale_price']) && (float)$product['sale_price'] > 0) {
                return (float)$product['sale_price'];
            }
            return (float)($product['price'] ?? 0);
        }

        $db = Database::getInstance();
        if ($variantId) {
            $tStmt = $db->prepare("SELECT unit_price FROM tiered_prices WHERE product_id = ? AND variant_id = ? AND min_qty <= ? ORDER BY min_qty DESC LIMIT 1");
            $tStmt->execute([$productId, $variantId, $qty]);
            $tier = $tStmt->fetchColumn();
            if ($tier !== false && (float)$tier > 0) {
                return (float)$tier;
            }
        }
        $tStmt = $db->prepare("SELECT unit_price FROM tiered_prices WHERE product_id = ? AND (variant_id IS NULL OR variant_id = 0) AND min_qty <= ? ORDER BY min_qty DESC LIMIT 1");
        $tStmt->execute([$productId, $qty]);
        $tier = $tStmt->fetchColumn();
        if ($tier !== false && (float)$tier > 0) {
            return (float)$tier;
        }
        if ($variant && (float)($variant['wholesale_price'] ?? 0) > 0) {
            return (float)$variant['wholesale_price'];
        }
        return (float)($product['price'] ?? 0);
    }

    private function recalculateCart(?int $userId, string $sessionId): void
    {
        $db = Database::getInstance();
        if ($userId) {
            $stmt = $db->prepare("SELECT * FROM cart_items WHERE user_id = ?");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM cart_items WHERE session_id = ?");
            $stmt->execute([$sessionId]);
        }
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $item) {
            $pStmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $pStmt->execute([$item['product_id']]);
            $p = $pStmt->fetch(\PDO::FETCH_ASSOC);
            if (!$p) {
                continue;
            }
            $v = null;
            if (!empty($item['variant_id'])) {
                $vStmt = $db->prepare("SELECT * FROM product_variants WHERE id = ?");
                $vStmt->execute([(int)$item['variant_id']]);
                $v = $vStmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            }
            $price = $this->calculateUnitPrice((int)$item['product_id'], !empty($item['variant_id']) ? (int)$item['variant_id'] : null, (int)$item['quantity'], $item['pricing_mode'] ?? 'wholesale', $p, $v);
            $db->prepare("UPDATE cart_items SET unit_price = ? WHERE id = ?")->execute([$price, $item['id']]);
        }
    }

    private function cartSummary(?int $userId, string $sessionId): array
    {
        $db = Database::getInstance();
        if ($userId) {
            $stmt = $db->prepare("SELECT c.*, p.name as product_name, p.slug as product_slug, p.main_image, p.sku as product_sku, v.attribute_value, v.image_url as variant_image, v.sku as variant_code FROM cart_items c JOIN products p ON c.product_id = p.id LEFT JOIN product_variants v ON c.variant_id = v.id WHERE c.user_id = ? ORDER BY c.id DESC");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("SELECT c.*, p.name as product_name, p.slug as product_slug, p.main_image, p.sku as product_sku, v.attribute_value, v.image_url as variant_image, v.sku as variant_code FROM cart_items c JOIN products p ON c.product_id = p.id LEFT JOIN product_variants v ON c.variant_id = v.id WHERE c.session_id = ? ORDER BY c.id DESC");
            $stmt->execute([$sessionId]);
        }

        $items = [];
        $subtotal = 0.0;
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $qty = (int)$r['quantity'];
            $unit = (float)($r['unit_price'] ?? 0);
            $line = round($qty * $unit, 2);
            $img = !empty($r['variant_image']) ? asset($r['variant_image']) : (!empty($r['main_image']) ? asset($r['main_image']) : asset('assets/images/placeholder.jpg'));
            $items[] = [
                'product_id' => (int)$r['product_id'],
                'variant_id' => !empty($r['variant_id']) ? (int)$r['variant_id'] : null,
                'name' => $r['product_name'],
                'sku' => $r['variant_code'] ?: $r['product_sku'],
                'image' => $img,
                'variant_title' => $r['attribute_value'] ?: '',
                'quantity' => $qty,
                'unit_price' => $unit,
                'item_total' => $line,
            ];
            $subtotal += $line;
        }
        return ['items' => $items, 'subtotal' => round($subtotal, 2)];
    }

    private function columnExists(\PDO $db, string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $column]);
        $cache[$key] = ((int)$stmt->fetchColumn()) > 0;
        return $cache[$key];
    }
}
