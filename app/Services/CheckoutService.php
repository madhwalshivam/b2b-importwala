<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use Exception;
use RuntimeException;

/**
 * Enterprise Idempotent & Concurrency-Safe Checkout Service
 */
class CheckoutService extends BaseService
{
    private CartService $cartService;

    public function __construct()
    {
        parent::__construct();
        $this->cartService = new CartService();
    }

    /**
     * Process checkout with idempotency check and atomic inventory reservation
     */
    public function processCheckout(string $userId, string $cartId, string $idempotencyKey, array $shippingAddress, array $billingAddress, string $paymentMethod = 'razorpay'): array
    {
        // 1. Idempotency Check in Redis
        $idempotencyCacheKey = "idempotency:{$idempotencyKey}";
        $existingResult = $this->cache->get($idempotencyCacheKey);
        if ($existingResult !== null) {
            return $existingResult;
        }

        // 2. Load Cart
        $cart = $this->cartService->getCart($cartId);
        if (empty($cart['items'])) {
            throw new InvalidArgumentException("Cart is empty.");
        }

        $db = Database::getWriteConnection();

        try {
            // Begin Transaction with ROW LOCKING (FOR UPDATE)
            $db->beginTransaction();

            $orderId = $this->generateUuid();
            $orderNumber = 'ORD-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));

            $subtotal = 0.0;
            $orderItemsToInsert = [];

            // 3. Lock & Reserve Stock for each variation
            foreach ($cart['items'] as $item) {
                $varId = (int)$item['variation_id'];
                $qty = (int)$item['quantity'];

                // Atomic stock check and lock
                $stmt = $db->prepare("SELECT `id`, `product_id`, `sku`, `stock_qty`, `reserved_qty` FROM `product_variations` WHERE `id` = :vid FOR UPDATE");
                $stmt->execute(['vid' => $varId]);
                $varRow = $stmt->fetch();

                if (!$varRow) {
                    throw new RuntimeException("Product variation SKU {$item['sku']} is no longer available.");
                }

                $availableQty = (int)$varRow['stock_qty'] - (int)$varRow['reserved_qty'];
                if ($availableQty < $qty) {
                    throw new RuntimeException("Insufficient stock for SKU {$item['sku']}. Available: {$availableQty}, Requested: {$qty}.");
                }

                // Update stock and reserved qty atomically
                $newStock = (int)$varRow['stock_qty'] - $qty;
                $updateStmt = $db->prepare("UPDATE `product_variations` SET `stock_qty` = :new_stock WHERE `id` = :vid");
                $updateStmt->execute(['new_stock' => $newStock, 'vid' => $varId]);

                // Record Inventory Audit Log
                $logStmt = $db->prepare("INSERT INTO `inventory_logs` (`variation_id`, `change_qty`, `previous_qty`, `new_qty`, `reason`, `reference_id`) 
                    VALUES (:vid, :change_qty, :prev_qty, :new_qty, 'order_reservation', :ref_id)");
                $logStmt->execute([
                    'vid' => $varId,
                    'change_qty' => -$qty,
                    'prev_qty' => (int)$varRow['stock_qty'],
                    'new_qty' => $newStock,
                    'ref_id' => $orderId,
                ]);

                $lineTotal = round($item['unit_price'] * $qty, 4);
                $subtotal += $lineTotal;

                $orderItemsToInsert[] = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'variation_id' => $varId,
                    'sku' => $item['sku'],
                    'product_name' => $item['product_title'],
                    'variation_details' => json_encode(['color' => $item['color_name'] ?? null]),
                    'unit_price' => $item['unit_price'],
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                ];
            }

            $totalAmount = round($subtotal, 4);

            // 4. Create Order Record
            $orderStmt = $db->prepare("INSERT INTO `orders` 
                (`id`, `order_number`, `user_id`, `idempotency_key`, `status`, `payment_status`, `payment_method`, `currency_code`, `currency_rate`, `subtotal`, `total_amount`, `total_weight_kg`, `shipping_address`, `billing_address`)
                VALUES
                (:id, :order_number, :user_id, :idempotency_key, 'pending', 'unpaid', :payment_method, 'USD', 1.000000, :subtotal, :total_amount, :weight, :shipping_addr, :billing_addr)");
            
            $orderStmt->execute([
                'id' => $orderId,
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'idempotency_key' => $idempotencyKey,
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'weight' => $cart['total_weight_kg'] ?? 0.0,
                'shipping_addr' => json_encode($shippingAddress),
                'billing_addr' => json_encode($billingAddress),
            ]);

            // 5. Insert Order Items
            $itemStmt = $db->prepare("INSERT INTO `order_items` (`order_id`, `product_id`, `variation_id`, `sku`, `product_name`, `variation_details`, `unit_price`, `quantity`, `line_total`) 
                VALUES (:order_id, :product_id, :variation_id, :sku, :product_name, :variation_details, :unit_price, :quantity, :line_total)");
            
            foreach ($orderItemsToInsert as $oItem) {
                $itemStmt->execute($oItem);
            }

            $db->commit();

            // 6. Clear Redis Cart
            $this->cartService->clearCart($cartId);

            $result = [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ];

            // Save idempotency result in Redis for 24 Hours
            $this->cache->set($idempotencyCacheKey, $result, 86400);

            return $result;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw new RuntimeException("Checkout Failed: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
