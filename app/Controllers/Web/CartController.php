<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Auth;

class CartController extends BaseController
{
    private function getSessionId(): string
    {
        return get_current_session_id();
    }

    private function getUserId(): ?int
    {
        return get_current_user_id();
    }

    public function add(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        $db = Database::getInstance();

        $productId   = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        $variantId   = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
        $setExact    = !empty($_POST['set_exact_qty']);
        $rawQty      = (int)($_POST['quantity'] ?? 1);
        $qty         = $setExact ? max(0, $rawQty) : max(1, $rawQty);
        $pricingMode = trim($_POST['pricing_mode'] ?? 'wholesale');
        if (!in_array($pricingMode, ['wholesale', 'onepiece'])) {
            $pricingMode = 'wholesale';
        }

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            return;
        }

        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        // 1. Fetch Product details
        $pStmt = $db->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
        $pStmt->execute([$productId]);
        $product = $pStmt->fetch(\PDO::FETCH_ASSOC);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found or inactive']);
            return;
        }

        // 2. Fetch Variant details if provided
        $variant = null;
        if ($variantId) {
            $vStmt = $db->prepare("SELECT * FROM product_variants WHERE id = ? AND product_id = ?");
            $vStmt->execute([$variantId, $productId]);
            $variant = $vStmt->fetch(\PDO::FETCH_ASSOC);
        }

        // Check if existing item in cart
        if ($userId) {
            $checkStmt = $db->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL)) AND pricing_mode = ?");
            $checkStmt->execute([$userId, $productId, $variantId, $variantId, $pricingMode]);
        } else {
            $checkStmt = $db->prepare("SELECT * FROM cart_items WHERE session_id = ? AND product_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL)) AND pricing_mode = ?");
            $checkStmt->execute([$sessionId, $productId, $variantId, $variantId, $pricingMode]);
        }
        $existing = $checkStmt->fetch(\PDO::FETCH_ASSOC);

        if ($setExact) {
            if ($qty <= 0) {
                if ($existing) {
                    $delStmt = $db->prepare("DELETE FROM cart_items WHERE id = ?");
                    $delStmt->execute([$existing['id']]);
                }
            } else {
                $unitPrice = $this->calculateUnitPrice($productId, $variantId, $qty, $pricingMode, $product, $variant);
                if ($existing) {
                    $upStmt = $db->prepare("UPDATE cart_items SET quantity = ?, unit_price = ?, updated_at = NOW() WHERE id = ?");
                    $upStmt->execute([$qty, $unitPrice, $existing['id']]);
                } else {
                    $insStmt = $db->prepare("INSERT INTO cart_items (user_id, session_id, product_id, variant_id, pricing_mode, quantity, unit_price, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $insStmt->execute([$userId, $sessionId, $productId, $variantId, $pricingMode, $qty, $unitPrice]);
                }
            }
        } else {
            $newQty = $qty;
            if ($existing) {
                $newQty += (int)$existing['quantity'];
            }

            // Compute Unit Price based on Quantity & Mode
            $unitPrice = $this->calculateUnitPrice($productId, $variantId, $newQty, $pricingMode, $product, $variant);

            if ($existing) {
                $upStmt = $db->prepare("UPDATE cart_items SET quantity = ?, unit_price = ?, updated_at = NOW() WHERE id = ?");
                $upStmt->execute([$newQty, $unitPrice, $existing['id']]);
            } else {
                $insStmt = $db->prepare("INSERT INTO cart_items (user_id, session_id, product_id, variant_id, pricing_mode, quantity, unit_price, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $insStmt->execute([$userId, $sessionId, $productId, $variantId, $pricingMode, $newQty, $unitPrice]);
            }
        }

        // Recalculate all cart prices to ensure quantity break consistency across cart
        $this->recalculateCartTierPrices($userId, $sessionId);

        $cartInfo = $this->getCartSummary($userId, $sessionId);

        echo json_encode([
            'success'     => true,
            'message'     => 'Added to cart successfully',
            'product_name'=> $product['name'],
            'cart_count'  => $cartInfo['total_count'],
            'subtotal'    => number_format($cartInfo['subtotal'], 2),
            'items'       => $cartInfo['items']
        ]);
    }

    public function data(): void
    {
        header('Content-Type: application/json');
        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        $this->recalculateCartTierPrices($userId, $sessionId);
        $cartInfo = $this->getCartSummary($userId, $sessionId);

        echo json_encode([
            'success'    => true,
            'count'      => $cartInfo['total_count'],
            'subtotal'   => number_format($cartInfo['subtotal'], 2),
            'items'      => $cartInfo['items']
        ]);
    }

    public function update(): void
    {
        header('Content-Type: application/json');
        $db = Database::getInstance();

        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        $qty        = max(1, (int)($_POST['quantity'] ?? 1));

        if (!$cartItemId) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
            return;
        }

        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        $stmt = $db->prepare("SELECT * FROM cart_items WHERE id = ?");
        $stmt->execute([$cartItemId]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$item) {
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            return;
        }

        $upStmt = $db->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
        $upStmt->execute([$qty, $cartItemId]);

        $this->recalculateCartTierPrices($userId, $sessionId);
        $cartInfo = $this->getCartSummary($userId, $sessionId);

        echo json_encode([
            'success'    => true,
            'cart_count' => $cartInfo['total_count'],
            'subtotal'   => number_format($cartInfo['subtotal'], 2),
            'items'      => $cartInfo['items']
        ]);
    }

    public function remove(): void
    {
        header('Content-Type: application/json');
        $db = Database::getInstance();
        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        $productId  = (int)($_POST['product_id'] ?? 0);

        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        $item = null;
        if ($cartItemId) {
            $stmt = $db->prepare("SELECT * FROM cart_items WHERE id = ?");
            $stmt->execute([$cartItemId]);
            $item = $stmt->fetch(\PDO::FETCH_ASSOC);
            $delStmt = $db->prepare("DELETE FROM cart_items WHERE id = ?");
            $delStmt->execute([$cartItemId]);
        } elseif ($productId) {
            if ($userId) {
                $delStmt = $db->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
                $delStmt->execute([$userId, $productId]);
            } else {
                $delStmt = $db->prepare("DELETE FROM cart_items WHERE session_id = ? AND product_id = ?");
                $delStmt->execute([$sessionId, $productId]);
            }
        }

        $this->recalculateCartTierPrices($userId, $sessionId);
        $cartInfo = $this->getCartSummary($userId, $sessionId);

        echo json_encode([
            'success'      => true,
            'message'      => 'Item removed from cart',
            'product_id'   => $productId ?: ($item ? (int)$item['product_id'] : 0),
            'variant_id'   => ($item && !empty($item['variant_id'])) ? (int)$item['variant_id'] : null,
            'pricing_mode' => $item['pricing_mode'] ?? 'wholesale',
            'cart_qty'     => 0,
            'cart_count'   => $cartInfo['total_count'],
            'subtotal'     => number_format($cartInfo['subtotal'], 2),
            'items'        => $cartInfo['items']
        ]);
    }

    public function index(): void
    {
        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        $this->recalculateCartTierPrices($userId, $sessionId);
        $cartInfo = $this->getCartSummary($userId, $sessionId);

        $this->renderView('web/cart', [
            'cartItems' => $cartInfo['items'],
            'cartCount' => $cartInfo['total_count'],
            'subtotal'  => $cartInfo['subtotal'],
            'tax'       => $cartInfo['subtotal'] * 0.18,
            'total'     => $cartInfo['subtotal'] * 1.18,
        ]);
    }

    private function calculateUnitPrice(int $productId, ?int $variantId, int $qty, string $mode, array $product, ?array $variant): float
    {
        if ($mode === 'onepiece') {
            if ($variant && (float)$variant['one_piece_price'] > 0) {
                return (float)$variant['one_piece_price'];
            }
            if (!empty($product['sale_price']) && (float)$product['sale_price'] > 0) {
                return (float)$product['sale_price'];
            }
            return (float)($product['price'] ?? 0);
        }

        // Wholesale mode: check volume tier prices
        $db = Database::getInstance();
        if ($variantId) {
            $tStmt = $db->prepare("SELECT * FROM tiered_prices WHERE product_id = ? AND variant_id = ? AND min_qty <= ? ORDER BY min_qty DESC LIMIT 1");
            $tStmt->execute([$productId, $variantId, $qty]);
            $tier = $tStmt->fetch(\PDO::FETCH_ASSOC);
            if ($tier) {
                return (float)$tier['unit_price'];
            }
        }

        // Fallback to product-level tiers
        $tStmt = $db->prepare("SELECT * FROM tiered_prices WHERE product_id = ? AND (variant_id IS NULL OR variant_id = 0) AND min_qty <= ? ORDER BY min_qty DESC LIMIT 1");
        $tStmt->execute([$productId, $qty]);
        $tier = $tStmt->fetch(\PDO::FETCH_ASSOC);

        if ($tier) {
            return (float)$tier['unit_price'];
        }

        // Default base wholesale price
        if ($variant && (float)$variant['wholesale_price'] > 0) {
            return (float)$variant['wholesale_price'];
        }
        return (float)($product['price'] ?? 0);
    }

    private function recalculateCartTierPrices(?int $userId, string $sessionId): void
    {
        $db = Database::getInstance();
        if ($userId) {
            $stmt = $db->prepare("SELECT * FROM cart_items WHERE user_id = ?");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM cart_items WHERE session_id = ?");
            $stmt->execute([$sessionId]);
        }
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            $pStmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $pStmt->execute([$item['product_id']]);
            $p = $pStmt->fetch(\PDO::FETCH_ASSOC);

            $v = null;
            if ($item['variant_id']) {
                $vStmt = $db->prepare("SELECT * FROM product_variants WHERE id = ?");
                $vStmt->execute([$item['variant_id']]);
                $v = $vStmt->fetch(\PDO::FETCH_ASSOC);
            }

            if ($p) {
                $newPrice = $this->calculateUnitPrice((int)$item['product_id'], $item['variant_id'] ? (int)$item['variant_id'] : null, (int)$item['quantity'], $item['pricing_mode'] ?? 'wholesale', $p, $v);
                $up = $db->prepare("UPDATE cart_items SET unit_price = ? WHERE id = ?");
                $up->execute([$newPrice, $item['id']]);
            }
        }
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

    public function submitInquiry(): void
    {
        header('Content-Type: application/json');

        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $name    = trim($input['customer_name'] ?? $input['name'] ?? '');
        $phone   = trim($input['phone'] ?? '');
        $message = trim($input['customer_message'] ?? $input['message'] ?? '');
        $email   = trim($input['email'] ?? '');

        if (empty($name) || empty($phone)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please provide your Full Name and Mobile/Phone Number.'
            ]);
            return;
        }

        $this->recalculateCartTierPrices($userId, $sessionId);
        $cartInfo = $this->getCartSummary($userId, $sessionId);

        if (empty($cartInfo['items'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Your shopping cart is empty. Please add products before submitting an inquiry.'
            ]);
            return;
        }

        // Build snapshot items array for Inquiry model
        $snapshotItems = [];
        foreach ($cartInfo['items'] as $item) {
            $snapshotItems[] = [
                'product_id'             => (int)$item['product_id'],
                'product_name_snapshot' => $item['name'],
                'sku_snapshot'           => $item['sku'] ?? ('SKU-' . $item['product_id']),
                'product_image_snapshot' => $item['image'] ?? '',
                'variation_id'           => !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                'variation_name'         => !empty($item['variant_title']) ? $item['variant_title'] : '',
                'quantity'               => max(1, (int)$item['quantity']),
                'price_snapshot'         => (float)($item['unit_price'] ?? 0),
            ];
        }

        try {
            $inquiryModel = new \App\Models\Inquiry();
            $result = $inquiryModel->createInquiry([
                'customer_name'    => $name,
                'phone'            => $phone,
                'email'            => $email,
                'customer_message' => $message,
                'business_type'    => 'Cart Quote Request',
            ], $snapshotItems);

            // Note: Cart remains intact (NOT cleared) so user can still complete a normal paid order
            echo json_encode([
                'success'        => true,
                'message'        => "Your inquiry has been sent, we'll contact you shortly",
                'inquiry_number' => $result['inquiry_number'],
                'total_products' => $result['total_products'],
                'total_quantity' => $result['total_quantity'],
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to submit inquiry: ' . $e->getMessage()
            ]);
        }
    }
}
