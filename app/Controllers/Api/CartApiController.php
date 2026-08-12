<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\CartService;

class CartApiController extends BaseController
{
    private CartService $cartService;

    public function __construct()
    {
        $this->cartService = new CartService();
    }

    private function getCartId(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['cart_id'])) {
            $_SESSION['cart_id'] = 'cart_' . bin2hex(random_bytes(8));
        }
        return $_SESSION['cart_id'];
    }

    public function getCart(): void
    {
        header('Content-Type: application/json');
        $cart = $this->cartService->getCart($this->getCartId());
        echo json_encode(['success' => true, 'cart' => $cart]);
    }

    public function addItem(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $productId = (int)($input['product_id'] ?? 0);
        $variationId = (int)($input['variation_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);

        if (!$productId || !$variationId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid product or variation parameters.']);
            return;
        }

        try {
            $updatedCart = $this->cartService->addItem($this->getCartId(), $productId, $variationId, $quantity);
            echo json_encode(['success' => true, 'cart' => $updatedCart]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateItem(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $productId = (int)($input['product_id'] ?? 0);
        $variationId = (int)($input['variation_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 0);

        try {
            $updatedCart = $this->cartService->updateQuantity($this->getCartId(), $productId, $variationId, $quantity);
            echo json_encode(['success' => true, 'cart' => $updatedCart]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
