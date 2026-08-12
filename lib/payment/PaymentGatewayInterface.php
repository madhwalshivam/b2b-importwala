<?php
namespace Lib\Payment;

interface PaymentGatewayInterface {
    /**
     * Create an order on the payment gateway
     *
     * @param float $amount Amount in main currency unit (e.g. INR 499.00)
     * @param string $currency ISO currency code (e.g. INR)
     * @param string $receipt Internal order reference number
     * @param array $extra Customer metadata (name, email, phone)
     * @return array Contains 'success', 'gateway_order_id', 'amount', 'currency', 'key_id', etc.
     */
    public function createOrder(float $amount, string $currency = 'INR', string $receipt = '', array $extra = []): array;

    /**
     * Verify payment signature server-side
     *
     * @param array $paymentParams Gateway returned parameters (razorpay_order_id, razorpay_payment_id, razorpay_signature)
     * @return bool True if signature matches
     */
    public function verifyPayment(array $paymentParams): bool;

    /**
     * Issue a refund for a completed payment
     *
     * @param string $paymentId
     * @param float $amount
     * @param array $extra
     * @return array
     */
    public function refund(string $paymentId, float $amount, array $extra = []): array;

    /**
     * Test connection to payment gateway API with current credentials
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(): array;
}
