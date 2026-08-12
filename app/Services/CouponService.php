<?php
namespace App\Services;

use App\Core\Database;

class CouponService {

    /**
     * Validate coupon code and calculate exact discount against cart items
     *
     * @param string $code
     * @param array $cartItems
     * @param float $subtotal
     * @param int|null $userId
     * @param string|null $sessionId
     * @return array Contains 'status' ('applied'|'error'), 'message', 'discount_amount', 'applicable_product_ids', etc.
     */
    public static function validateAndCalculate(string $code, array $cartItems, float $subtotal, ?int $userId = null, ?string $sessionId = null): array {
        $code = strtoupper(trim($code));
        if (empty($code)) {
            return ['status' => 'error', 'message' => 'Please enter a coupon code.'];
        }

        if (empty($cartItems)) {
            return ['status' => 'error', 'message' => 'Your cart is empty.'];
        }

        $db = Database::getInstance();

        // 1. Fetch Coupon Record
        $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$coupon) {
            return ['status' => 'error', 'message' => "Coupon code '{$code}' is invalid or inactive."];
        }

        // 2. Date Validity Check
        $nowStr = date('Y-m-d H:i:s');
        if (!empty($coupon['valid_from']) && $coupon['valid_from'] > $nowStr && strtotime($coupon['valid_from']) > (time() + 86400)) {
            return ['status' => 'error', 'message' => "Coupon '{$code}' is not active yet."];
        }

        if (!empty($coupon['valid_until']) && $coupon['valid_until'] < $nowStr) {
            return ['status' => 'error', 'message' => "Coupon '{$code}' has expired."];
        }

        // 3. Minimum Order Value Check
        $minOrder = (float)($coupon['min_order_value'] ?? 0);
        if ($minOrder > 0 && $subtotal < $minOrder) {
            $formattedMin = function_exists('format_price') ? format_price($minOrder) : '₹' . number_format($minOrder, 2);
            return [
                'status' => 'error',
                'message' => "Minimum order value of {$formattedMin} required to use coupon '{$code}'."
            ];
        }

        // 4. Overall Usage Limit Check
        if ($coupon['usage_limit_total'] !== null && $coupon['usage_limit_total'] > 0) {
            $stmtCount = $db->prepare("SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = ?");
            $stmtCount->execute([$coupon['id']]);
            $totalUsed = (int)$stmtCount->fetchColumn();
            if ($totalUsed >= (int)$coupon['usage_limit_total']) {
                return ['status' => 'error', 'message' => "Coupon '{$code}' usage limit has been reached."];
            }
        }

        // 5. Per-User / Session Usage Limit Check
        if ($coupon['usage_limit_per_user'] !== null && $coupon['usage_limit_per_user'] > 0) {
            $userUsed = 0;
            if (!empty($userId)) {
                $stmtUser = $db->prepare("SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = ? AND user_id = ?");
                $stmtUser->execute([$coupon['id'], $userId]);
                $userUsed = (int)$stmtUser->fetchColumn();
            } elseif (!empty($sessionId)) {
                $stmtSess = $db->prepare("SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = ? AND session_id = ?");
                $stmtSess->execute([$coupon['id'], $sessionId]);
                $userUsed = (int)$stmtSess->fetchColumn();
            }

            if ($userUsed >= (int)$coupon['usage_limit_per_user']) {
                return ['status' => 'error', 'message' => "You have already used coupon '{$code}' maximum allowed times."];
            }
        }

        // 6. Scope & Eligible Cart Items Check
        $scopeType = $coupon['scope_type'] ?? 'all_products';
        $eligibleSubtotal = 0.00;
        $applicableProductIds = [];

        if ($scopeType === 'specific_products') {
            $stmtProds = $db->prepare("SELECT product_id FROM coupon_products WHERE coupon_id = ?");
            $stmtProds->execute([$coupon['id']]);
            $allowedProdIds = array_map('intval', $stmtProds->fetchAll(\PDO::FETCH_COLUMN));

            foreach ($cartItems as $item) {
                $pId = (int)($item['id'] ?? 0);
                if (in_array($pId, $allowedProdIds, true)) {
                    $applicableProductIds[] = $pId;
                    $lineTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                    $eligibleSubtotal += $lineTotal;
                }
            }

            if (empty($applicableProductIds)) {
                return ['status' => 'error', 'message' => "Coupon '{$code}' is not applicable to any items in your cart."];
            }
        } elseif ($scopeType === 'specific_categories') {
            $stmtCats = $db->prepare("SELECT category_id FROM coupon_categories WHERE coupon_id = ?");
            $stmtCats->execute([$coupon['id']]);
            $allowedCatIds = array_map('intval', $stmtCats->fetchAll(\PDO::FETCH_COLUMN));

            foreach ($cartItems as $item) {
                $pId = (int)($item['id'] ?? 0);
                $catId = (int)($item['category_id'] ?? 0);

                // If item does not have category_id attached, fetch from DB
                if ($catId <= 0 && $pId > 0) {
                    $stC = $db->prepare("SELECT category_id FROM products WHERE id = ?");
                    $stC->execute([$pId]);
                    $catId = (int)$stC->fetchColumn();
                }

                if (in_array($catId, $allowedCatIds, true)) {
                    $applicableProductIds[] = $pId;
                    $lineTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                    $eligibleSubtotal += $lineTotal;
                }
            }

            if (empty($applicableProductIds)) {
                return ['status' => 'error', 'message' => "Coupon '{$code}' is not valid for the categories in your cart."];
            }
        } else {
            // 'all_products'
            $eligibleSubtotal = $subtotal;
            foreach ($cartItems as $item) {
                $applicableProductIds[] = (int)($item['id'] ?? 0);
            }
        }

        // 7. Calculate Discount Amount
        $discountAmount = 0.00;
        $discType = $coupon['discount_type'] ?? 'flat';
        $discVal = (float)($coupon['discount_value'] ?? 0);

        if ($discType === 'flat') {
            $discountAmount = min($discVal, $eligibleSubtotal);
        } elseif ($discType === 'percentage') {
            $calc = ($eligibleSubtotal * $discVal) / 100;
            if (!empty($coupon['max_discount_cap']) && (float)$coupon['max_discount_cap'] > 0) {
                $calc = min($calc, (float)$coupon['max_discount_cap']);
            }
            $discountAmount = min($calc, $eligibleSubtotal);
        }

        $discountAmount = round($discountAmount, 2);

        return [
            'status' => 'applied',
            'message' => "Coupon '{$coupon['code']}' applied successfully!",
            'coupon_id' => (int)$coupon['id'],
            'code' => $coupon['code'],
            'description' => $coupon['description'],
            'discount_type' => $discType,
            'discount_value' => $discVal,
            'discount_amount' => $discountAmount,
            'eligible_subtotal' => round($eligibleSubtotal, 2),
            'new_total' => max(0, round($subtotal - $discountAmount, 2)),
            'applicable_product_ids' => array_values(array_unique($applicableProductIds))
        ];
    }

    /**
     * Record coupon redemption upon completed checkout
     */
    public static function recordUsage(int $couponId, ?int $userId, ?string $sessionId, int $orderId, float $discountApplied): bool {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO coupon_usage (coupon_id, user_id, session_id, order_id, discount_applied, used_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([$couponId, $userId, $sessionId, $orderId, $discountApplied]);
        } catch (\Throwable $e) {
            error_log("Failed to record coupon usage: " . $e->getMessage());
            return false;
        }
    }
}
