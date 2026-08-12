<?php

namespace App\Services;

use App\Repositories\Eloquent\ProductRepository;

/**
 * Ephemeral Redis-Backed Cart Service (Zero DB Writes on Add-to-Cart)
 */
class CartService extends BaseService
{
    private ProductRepository $productRepo;
    private TieredPricingService $pricingService;

    public function __construct()
    {
        parent::__construct();
        $this->productRepo = new ProductRepository();
        $this->pricingService = new TieredPricingService();
    }

    public function getCartKey(string $cartId): string
    {
        return "cart:{$cartId}";
    }

    /**
     * Get cart contents
     */
    public function getCart(string $cartId): array
    {
        $key = $this->getCartKey($cartId);
        $cart = $this->cache->get($key, ['items' => [], 'coupon' => null]);
        
        return $this->recalculateCart($cart);
    }

    /**
     * Add item to ephemeral Redis cart
     */
    public function addItem(string $cartId, int $productId, int $variationId, int $quantity): array
    {
        $key = $this->getCartKey($cartId);
        $cart = $this->cache->get($key, ['items' => [], 'coupon' => null]);

        $product = $this->productRepo->getProductWithDetails($productId);
        if (!$product) {
            throw new \InvalidArgumentException("Product not found.");
        }

        $variation = null;
        if (!empty($product['variations'])) {
            foreach ($product['variations'] as $v) {
                if ($v['id'] == $variationId) {
                    $variation = $v;
                    break;
                }
            }
        }

        $moq = (int)($product['moq'] ?? 1);
        $itemKey = "{$productId}_{$variationId}";

        $existingQty = isset($cart['items'][$itemKey]) ? (int)$cart['items'][$itemKey]['quantity'] : 0;
        $newQty = $existingQty + $quantity;

        if ($newQty < $moq) {
            $newQty = $moq;
        }

        $cart['items'][$itemKey] = [
            'product_id' => $productId,
            'variation_id' => $variationId,
            'quantity' => $newQty,
            'added_at' => time(),
        ];

        $updatedCart = $this->recalculateCart($cart);
        $this->cache->set($key, $updatedCart, 2592000); // 30 Days TTL

        return $updatedCart;
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(string $cartId, int $productId, int $variationId, int $quantity): array
    {
        $key = $this->getCartKey($cartId);
        $cart = $this->cache->get($key, ['items' => [], 'coupon' => null]);

        $itemKey = "{$productId}_{$variationId}";
        if ($quantity <= 0) {
            unset($cart['items'][$itemKey]);
        } else {
            $product = $this->productRepo->getProductWithDetails($productId);
            $moq = (int)($product['moq'] ?? 1);
            if ($quantity < $moq) {
                $quantity = $moq;
            }
            if (isset($cart['items'][$itemKey])) {
                $cart['items'][$itemKey]['quantity'] = $quantity;
            }
        }

        $updatedCart = $this->recalculateCart($cart);
        $this->cache->set($key, $updatedCart, 2592000);

        return $updatedCart;
    }

    /**
     * Clear Cart
     */
    public function clearCart(string $cartId): bool
    {
        $key = $this->getCartKey($cartId);
        return $this->cache->forget($key);
    }

    /**
     * Recalculate tier prices and subtotals across all cart items
     */
    private function recalculateCart(array $cart): array
    {
        $items = [];
        $subtotal = 0.0;
        $totalWeight = 0.0;
        $totalItemsCount = 0;

        foreach ($cart['items'] as $itemKey => $itemData) {
            $product = $this->productRepo->getProductWithDetails($itemData['product_id']);
            if (!$product) {
                continue;
            }

            $variation = null;
            if (!empty($product['variations'])) {
                foreach ($product['variations'] as $v) {
                    if ($v['id'] == $itemData['variation_id']) {
                        $variation = $v;
                        break;
                    }
                }
            }

            $pricing = $this->pricingService->calculateUnitPrice($product, $variation, $itemData['quantity']);

            $subtotal += $pricing['line_total'];
            $totalWeight += ((float)($product['weight_kg'] ?? 0.004)) * $pricing['quantity'];
            $totalItemsCount += $pricing['quantity'];

            $items[$itemKey] = array_merge($itemData, [
                'product_title' => $product['title'],
                'product_slug' => $product['slug'],
                'main_image' => $product['main_image'],
                'sku' => $variation['sku'] ?? $product['sku'],
                'color_name' => $variation['color_name'] ?? null,
                'weight_kg' => $product['weight_kg'] ?? 0.004,
                'unit_price' => $pricing['effective_unit_price'],
                'line_total' => $pricing['line_total'],
                'savings' => $pricing['savings'],
                'moq' => $pricing['moq'],
            ]);
        }

        return [
            'items' => array_values($items),
            'items_count' => $totalItemsCount,
            'subtotal' => round($subtotal, 4),
            'total_weight_kg' => round($totalWeight, 4),
            'coupon' => $cart['coupon'] ?? null,
        ];
    }
}
