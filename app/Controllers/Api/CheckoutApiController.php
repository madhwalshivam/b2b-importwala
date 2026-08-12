<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\CheckoutService;

class CheckoutApiController extends BaseController
{
    private CheckoutService $checkoutService;

    public function __construct()
    {
        $this->checkoutService = new CheckoutService();
    }

    public function process(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $cartId = $_SESSION['cart_id'] ?? '';
        $userId = $_SESSION['user_id'] ?? ('usr_' . bin2hex(random_bytes(6)));
        $idempotencyKey = $input['idempotency_key'] ?? ('idemp_' . bin2hex(random_bytes(8)));

        $shippingAddress = $input['shipping_address'] ?? [
            'first_name' => $input['first_name'] ?? 'Guest',
            'last_name' => $input['last_name'] ?? 'User',
            'email' => $input['email'] ?? 'guest@importwala.com',
            'phone' => $input['phone'] ?? '+1 555-0192',
            'address_line' => $input['address_line'] ?? '123 Wholesale Blvd',
            'city' => $input['city'] ?? 'New York',
            'state' => $input['state'] ?? 'NY',
            'postal_code' => $input['postal_code'] ?? '10001',
            'country' => $input['country'] ?? 'US',
        ];

        $billingAddress = $input['billing_address'] ?? $shippingAddress;
        $paymentMethod = $input['payment_method'] ?? 'razorpay';

        try {
            $result = $this->checkoutService->processCheckout(
                $userId,
                $cartId,
                $idempotencyKey,
                $shippingAddress,
                $billingAddress,
                $paymentMethod
            );
            echo json_encode(['success' => true, 'order' => $result]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
