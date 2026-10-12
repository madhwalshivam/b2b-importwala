<?php

namespace App\Services;

use App\Core\Database;

class PaymentFulfillmentService
{
    public function markPaid(string $razorpayOrderId, string $paymentId, string $signature, ?string $method = null): array
    {
        $razorpayOrderId = trim($razorpayOrderId);
        $paymentId = trim($paymentId);
        if ($razorpayOrderId === '' || $paymentId === '') {
            return ['success' => false, 'message' => 'Payment details are incomplete.'];
        }

        $db = Database::getInstance();
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("SELECT * FROM orders WHERE razorpay_order_id = ? LIMIT 1 FOR UPDATE");
            $stmt->execute([$razorpayOrderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                $db->rollBack();
                $this->log(null, 'payment.missing_order', $razorpayOrderId, $paymentId, 'failed', 'No local order for this Razorpay order');
                return ['success' => false, 'message' => 'We could not match this payment to an order.'];
            }

            $orderId = (int)$order['id'];
            if (($order['payment_status'] ?? '') === 'paid') {
                $this->upgradeWebhookSignature($db, $order, $signature, $method, $paymentId);
                $db->commit();
                return [
                    'success' => true,
                    'already' => true,
                    'newly_paid' => false,
                    'order_id' => $orderId,
                    'order_number' => $order['order_number'],
                ];
            }

            $sets = [
                "payment_status = 'paid'",
                "order_status = 'confirmed'",
                "razorpay_payment_id = ?",
                "razorpay_signature = ?",
                "updated_at = NOW()",
            ];
            $params = [$paymentId, $signature];

            if ($this->columnExists($db, 'orders', 'paid_at')) {
                $sets[] = "paid_at = NOW()";
            }
            if ($method && $this->columnExists($db, 'orders', 'razorpay_method')) {
                $sets[] = "razorpay_method = ?";
                $params[] = mb_substr($method, 0, 40);
            }
            $params[] = $orderId;

            $db->prepare("UPDATE orders SET " . implode(', ', $sets) . " WHERE id = ?")->execute($params);
            $this->reduceStock($db, $orderId);
            $this->clearCartIfNeeded($db, $order);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Payment fulfill failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment was received but could not be saved. Please contact support with your payment reference.'];
        }

        $this->log($orderId, 'payment.paid', $razorpayOrderId, $paymentId, 'paid', $method ?: 'paid');
        if (function_exists('admin_audit_log')) {
            admin_audit_log('Payment Captured', 'Order ' . ($order['order_number'] ?? $orderId) . ' paid via Razorpay ' . $paymentId);
        }

        return [
            'success' => true,
            'already' => false,
            'newly_paid' => true,
            'order_id' => $orderId,
            'order_number' => $order['order_number'] ?? '',
        ];
    }

    public function markFailed(string $razorpayOrderId, string $paymentId = '', string $reason = ''): void
    {
        $razorpayOrderId = trim($razorpayOrderId);
        if ($razorpayOrderId === '') {
            return;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT id, payment_status, order_number FROM orders WHERE razorpay_order_id = ? LIMIT 1");
            $stmt->execute([$razorpayOrderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$order || ($order['payment_status'] ?? '') === 'paid') {
                return;
            }
            if (($order['payment_status'] ?? '') === 'failed') {
                return;
            }

            $db->prepare("UPDATE orders SET payment_status = 'failed', updated_at = NOW() WHERE id = ? AND payment_status = 'pending'")
                ->execute([(int)$order['id']]);
            $this->log((int)$order['id'], 'payment.failed', $razorpayOrderId, $paymentId, 'failed', mb_substr($reason, 0, 240));
            if (function_exists('admin_audit_log')) {
                admin_audit_log('Payment Failed', 'Order ' . ($order['order_number'] ?? '') . ' payment failed');
            }
        } catch (\Throwable $e) {
            error_log('Payment fail mark error: ' . $e->getMessage());
        }
    }

    private function upgradeWebhookSignature(\PDO $db, array $order, string $signature, ?string $method, string $paymentId): void
    {
        $current = (string)($order['razorpay_signature'] ?? '');
        if ($signature === '' || str_starts_with($signature, 'webhook:') || !str_starts_with($current, 'webhook:')) {
            return;
        }
        $sets = ["razorpay_signature = ?", "razorpay_payment_id = ?"];
        $params = [$signature, $paymentId];
        if ($method && $this->columnExists($db, 'orders', 'razorpay_method')) {
            $sets[] = "razorpay_method = ?";
            $params[] = mb_substr($method, 0, 40);
        }
        $params[] = (int)$order['id'];
        $db->prepare("UPDATE orders SET " . implode(', ', $sets) . " WHERE id = ?")->execute($params);
    }

    private function reduceStock(\PDO $db, int $orderId): void
    {
        $items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $items->execute([$orderId]);
        $hasVariantCol = $this->columnExists($db, 'order_items', 'variation_id');
        $hasVariantStock = $this->columnExists($db, 'product_variants', 'stock_quantity');
        $hasProductStock = $this->columnExists($db, 'products', 'stock');
        $hasSold = $this->columnExists($db, 'products', 'total_sold');

        foreach ($items->fetchAll(\PDO::FETCH_ASSOC) as $item) {
            $qty = (int)($item['quantity'] ?? 0);
            if ($qty < 1) {
                continue;
            }
            $variantId = $hasVariantCol ? (int)($item['variation_id'] ?? $item['variant_id'] ?? 0) : 0;
            if ($variantId > 0 && $hasVariantStock) {
                $db->prepare("UPDATE product_variants SET stock_quantity = GREATEST(0, stock_quantity - ?) WHERE id = ?")
                    ->execute([$qty, $variantId]);
            }
            $productId = (int)($item['product_id'] ?? 0);
            if ($productId > 0 && $hasProductStock) {
                if ($hasSold) {
                    $db->prepare("UPDATE products SET stock = GREATEST(0, stock - ?), total_sold = total_sold + ? WHERE id = ?")
                        ->execute([$qty, $qty, $productId]);
                } else {
                    $db->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?")
                        ->execute([$qty, $productId]);
                }
            }
        }
    }

    private function clearCartIfNeeded(\PDO $db, array $order): void
    {
        $source = (string)($order['checkout_source'] ?? 'cart');
        if ($source === 'buynow') {
            return;
        }
        $userId = (int)($order['user_id'] ?? 0);
        $sessionId = (string)($order['cart_session_id'] ?? '');
        if ($userId > 0) {
            $db->prepare("DELETE FROM cart_items WHERE user_id = ?")->execute([$userId]);
        } elseif ($sessionId !== '') {
            $db->prepare("DELETE FROM cart_items WHERE session_id = ?")->execute([$sessionId]);
        }
    }

    private function log(?int $orderId, string $event, string $rzOrder, string $rzPay, string $status, string $message): void
    {
        try {
            $db = Database::getInstance();
            if (!$this->tableExists($db, 'payment_logs')) {
                return;
            }
            $db->prepare("INSERT INTO payment_logs (order_id, event, razorpay_order_id, razorpay_payment_id, status, message) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$orderId, $event, $rzOrder, $rzPay, $status, mb_substr($message, 0, 480)]);
        } catch (\Throwable $e) {
            error_log('payment_logs write failed: ' . $e->getMessage());
        }
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

    private function tableExists(\PDO $db, string $table): bool
    {
        $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);
        return ((int)$stmt->fetchColumn()) > 0;
    }
}
