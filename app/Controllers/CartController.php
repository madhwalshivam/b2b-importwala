<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Services\CouponService;
use App\Services\NotificationService;

class CartController extends Controller {
    public function index(): string {
        $cart = $this->getCart();
        return $this->render('storefront/cart', ['cart' => $cart]);
    }

    public function add(): void {
        $productId = (int)$this->request->input('product_id');
        $qty = max(1, (int)$this->request->input('quantity', 1));

        $productModel = new Product();
        $product = $productModel->find($productId);

        if (!$product) {
            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'message' => 'Product not found.'], 404);
            }
            $this->redirect(url('shop'));
        }

        $stock = (int)($product['stock'] ?? 0);

        // Server-Side Stock Enforcement: Out of Stock Check
        if ($stock <= 0) {
            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'message' => 'This product is currently out of stock.'], 400);
            }
            $this->setFlash('error', 'This product is currently out of stock.');
            $this->redirect(url('cart'));
            return;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $currentInCart = $_SESSION['cart'][$productId]['quantity'] ?? 0;
        $newTotal = $currentInCart + $qty;

        // Server-Side Stock Enforcement: Stock Limit Check
        if ($newTotal > $stock) {
            $availableToTake = max(0, $stock - $currentInCart);
            if ($availableToTake <= 0) {
                $msg = "You already have the maximum available stock ({$stock} units) in your cart.";
            } else {
                $msg = "Only {$stock} unit(s) available in stock. You can only add {$availableToTake} more.";
            }
            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 400);
            }
            $this->setFlash('error', $msg);
            $this->redirect(url('cart'));
            return;
        }

        $effectivePrice = $product['sale_price'] ?: $product['price'];
        $imgSrc = asset($product['main_image'] ?? '');

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $qty;
            $_SESSION['cart'][$productId]['image'] = $imgSrc;
        } else {
            $_SESSION['cart'][$productId] = [
                'id' => $product['id'],
                'category_id' => $product['category_id'] ?? null,
                'name' => $product['name'],
                'sku' => $product['sku'],
                'hsn_code' => $product['hsn_code'] ?? '8714.99.90',
                'price' => (float)$effectivePrice,
                'tax_percent' => (float)($product['tax_percent'] ?? 18),
                'image' => $imgSrc,
                'quantity' => $qty,
                'slug' => $product['slug']
            ];
        }

        if ($this->request->input('buy_now')) {
            $this->redirect(url('checkout'));
            return;
        }

        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'cart' => $this->getCart()]);
        }

        $this->redirect(url('cart'));
    }

    public function update(): void {
        $productId = (int)$this->request->input('product_id');
        $qty = (int)$this->request->input('quantity', 1);

        $productModel = new Product();
        $product = $productModel->find($productId);
        $stock = $product ? (int)($product['stock'] ?? 0) : 0;

        if (isset($_SESSION['cart'][$productId])) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                if ($product && $qty > $stock) {
                    $qty = max(1, $stock);
                    $_SESSION['cart'][$productId]['quantity'] = $qty;
                    $msg = "Only {$stock} unit(s) available in stock. Quantity updated to {$qty}.";
                    if ($this->request->isAjax()) {
                        $this->json(['success' => false, 'message' => $msg, 'cart' => $this->getCart()], 400);
                    }
                    $this->setFlash('error', $msg);
                    $this->redirect(url('cart'));
                    return;
                }
                $_SESSION['cart'][$productId]['quantity'] = $qty;
            }
        }

        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'cart' => $this->getCart()]);
        }

        $this->redirect(url('cart'));
    }

    public function remove(): void {
        $productId = (int)$this->request->input('product_id');

        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }

        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'cart' => $this->getCart()]);
        }

        $this->redirect(url('cart'));
    }

    public function getCartData(): void {
        $this->json(['success' => true, 'cart' => $this->getCart()]);
    }

    public function applyCoupon(): void {
        $code = trim((string)$this->request->input('code', ''));
        $cart = $this->getCart();

        if (empty($code)) {
            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'message' => 'Please enter a coupon code.'], 400);
            }
            $this->setFlash('error', 'Please enter a coupon code.');
            $this->redirect(url('cart'));
        }

        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();

        $result = CouponService::validateAndCalculate($code, $cart['items'], $cart['subtotal'], $userId, $sessionId);

        if ($result['status'] === 'applied') {
            $_SESSION['applied_coupon'] = $result;

            if ($this->request->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => $result['message'],
                    'discount_amount' => $result['discount_amount'],
                    'applicable_product_ids' => $result['applicable_product_ids'],
                    'cart' => $this->getCart()
                ]);
            }

            $this->setFlash('success', $result['message']);
        } else {
            unset($_SESSION['applied_coupon']);

            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'message' => $result['message']], 400);
            }

            $this->setFlash('error', $result['message']);
        }

        $this->redirect(url('cart'));
    }

    public function removeCoupon(): void {
        unset($_SESSION['applied_coupon']);

        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'message' => 'Coupon removed.', 'cart' => $this->getCart()]);
        }

        $this->setFlash('success', 'Coupon removed.');
        $this->redirect(url('cart'));
    }

    protected function getCart(): array {
        $items = $_SESSION['cart'] ?? [];
        $grossSubtotal = 0;
        $subtotal = 0;
        $taxTotal = 0;
        $itemCount = 0;
        $hasOutOfStock = false;

        $db = \App\Core\Database::getInstance();
        $stmtStock = $db->prepare("SELECT stock, hsn_code, tax_percent FROM products WHERE id = ? LIMIT 1");

        foreach ($items as &$item) {
            $item['image'] = asset($item['image'] ?? '');

            // Live Stock & Product Metadata Check
            $stmtStock->execute([$item['id']]);
            $prodInfo = $stmtStock->fetch(\PDO::FETCH_ASSOC);
            $liveStock = ($prodInfo !== false) ? (int)($prodInfo['stock'] ?? 0) : 0;

            if ($prodInfo) {
                if (!empty($prodInfo['hsn_code'])) {
                    $item['hsn_code'] = $prodInfo['hsn_code'];
                }
                if (isset($prodInfo['tax_percent'])) {
                    $item['tax_percent'] = (float)$prodInfo['tax_percent'];
                }
            }

            $item['stock'] = $liveStock;
            $item['in_stock'] = ($liveStock > 0);
            $item['is_out_of_stock'] = ($liveStock <= 0);
            $item['exceeds_stock'] = ($item['quantity'] > $liveStock);

            if ($item['is_out_of_stock'] || $item['exceeds_stock']) {
                $hasOutOfStock = true;
            }

            $itemCount += (int) ($item['quantity'] ?? 1);
        }
        unset($item);

        $gstLines = [];
        foreach ($items as $item) {
            $gstLines[] = [
                'product_id' => (int) ($item['id'] ?? 0),
                'unit_price' => (float) ($item['price'] ?? 0),
                'quantity' => (int) ($item['quantity'] ?? 1),
                'name' => (string) ($item['name'] ?? ''),
                'sku' => (string) ($item['sku'] ?? ''),
            ];
        }
        $quote = (new \App\Services\GstService())->quote($gstLines, null);
        $grossSubtotal = 0.0;
        $subtotal = (float) $quote['total_taxable_amount'];
        $taxTotal = (float) $quote['total_gst'];
        foreach ($items as $idx => &$item) {
            $line = $quote['lines'][$idx] ?? null;
            if (!$line) {
                continue;
            }
            $item['tax_percent'] = $line['gst_rate'];
            $item['hsn_code'] = $line['hsn_code'];
            $item['unit_base_price'] = $line['quantity'] > 0 ? $line['taxable_amount'] / $line['quantity'] : 0;
            $item['unit_gst_amount'] = $line['quantity'] > 0 ? $line['gst_amount'] / $line['quantity'] : 0;
            $item['line_gross_total'] = $line['gross_amount'];
            $item['line_base_total'] = $line['taxable_amount'];
            $item['line_gst_total'] = $line['gst_amount'];
            $item['gst_line'] = $line;
            $grossSubtotal += ($quote['price_mode'] === 'inclusive') ? $line['gross_amount'] : $line['taxable_amount'];
        }
        unset($item);
        $grossSubtotal = \App\Services\GstCalculator::roundMoney($grossSubtotal);

        $discount = 0;
        $applicableProductIds = [];
        $couponCode = null;

        if (!empty($_SESSION['applied_coupon']['code'])) {
            $userId = $_SESSION['user_id'] ?? null;
            $sessionId = session_id();

            $valResult = CouponService::validateAndCalculate(
                $_SESSION['applied_coupon']['code'],
                array_values($items),
                $grossSubtotal,
                $userId,
                $sessionId
            );

            if ($valResult['status'] === 'applied') {
                $discount = $valResult['discount_amount'];
                $applicableProductIds = $valResult['applicable_product_ids'];
                $couponCode = $valResult['code'];
                $_SESSION['applied_coupon'] = $valResult;
            } else {
                unset($_SESSION['applied_coupon']);
            }
        }

        $shippingThreshold = (float)($GLOBALS['app_config']['company']['free_shipping_threshold'] ?? 999);
        $shippingCharge = ($grossSubtotal >= $shippingThreshold || $grossSubtotal == 0) ? 0 : 79;

        $goodsTotal = ($quote['price_mode'] ?? 'exclusive') === 'inclusive'
            ? $grossSubtotal
            : \App\Services\GstCalculator::roundMoney($grossSubtotal + $taxTotal);
        $grandTotal = max(0, \App\Services\GstCalculator::roundMoney($goodsTotal + $shippingCharge - $discount));

        return [
            'items' => array_values($items),
            'item_count' => $itemCount,
            'subtotal' => $grossSubtotal, // Full item price sum (GST Inclusive)
            'gross_subtotal' => $grossSubtotal,
            'base_subtotal' => $subtotal, // Base price sum (exclusive of GST)
            'tax_total' => $taxTotal, // GST tax included sum
            'shipping_charge' => $shippingCharge,
            'discount' => $discount,
            'grand_total' => $grandTotal,
            'coupon_code' => $couponCode,
            'applicable_product_ids' => $applicableProductIds,
            'has_out_of_stock' => $hasOutOfStock
        ];
    }
}
