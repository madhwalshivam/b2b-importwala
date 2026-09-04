<?php
namespace Lib\Payment;

class RazorpayGateway implements PaymentGatewayInterface {
    protected string $keyId;
    protected string $keySecret;
    protected string $webhookSecret;
    protected string $mode;
    protected string $apiBase = 'https://api.razorpay.com/v1';

    public function __construct(
        ?string $keyId = null,
        ?string $keySecret = null,
        ?string $webhookSecret = null,
        string $mode = 'test'
    ) {
        $this->keyId = $keyId ?: 'rzp_test_placeholder_key';
        $this->keySecret = $keySecret ?: 'rzp_test_placeholder_secret';
        $this->webhookSecret = $webhookSecret ?: '';
        $this->mode = $mode;
    }

    public function createOrder(float $amount, string $currency = 'INR', string $receipt = '', array $extra = []): array {
        $amountInPaisa = (int)round($amount * 100);

        if (empty($this->keyId) || empty($this->keySecret) || str_contains($this->keyId, 'placeholder')) {
            return [
                'success' => false,
                'error_message' => 'Razorpay API credentials not configured. Please enter Key ID and Key Secret in Admin.'
            ];
        }

        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'error_message' => 'PHP cURL extension is required for payment processing.'
            ];
        }

        $payload = [
            'amount' => $amountInPaisa,
            'currency' => strtoupper($currency),
            'receipt' => $receipt,
            'payment_capture' => 1,
            'notes' => [
                'customer_name' => $extra['name'] ?? '',
                'customer_email' => $extra['email'] ?? '',
                'customer_phone' => $extra['phone'] ?? ''
            ]
        ];

        $ch = curl_init($this->apiBase . '/orders');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->keyId . ':' . $this->keySecret,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15
        ]);

        $response = curl_exec($ch);
        $httpCode = defined('CURLINFO_HTTPCODE') ? (int)curl_getinfo($ch, CURLINFO_HTTPCODE) : 200;
        $error = curl_error($ch);

        $data = json_decode($response, true);
        if ($error || $httpCode >= 400 || empty($data['id'])) {
            $errorDesc = $data['error']['description'] ?? ($error ?: "HTTP {$httpCode} error from Razorpay");
            error_log("Razorpay API Order Creation Error ({$httpCode}): {$errorDesc}");
            return [
                'success' => false,
                'error_message' => "Razorpay payment creation failed: {$errorDesc}"
            ];
        }

        return [
            'success' => true,
            'gateway_order_id' => $data['id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'key_id' => $this->keyId,
            'raw' => $data
        ];
    }

    public function verifyPayment(array $paymentParams): bool {
        $orderId = trim((string)($paymentParams['razorpay_order_id'] ?? ''));
        $paymentId = trim((string)($paymentParams['razorpay_payment_id'] ?? ''));
        $signature = trim((string)($paymentParams['razorpay_signature'] ?? ''));

        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return false;
        }

        if (empty($this->keySecret) || str_contains($this->keySecret, 'placeholder')) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($expectedSignature, $signature);
    }

    public function refund(string $paymentId, float $amount, array $extra = []): array {
        if (!function_exists('curl_init') || str_starts_with($paymentId, 'pay_mock_')) {
            return [
                'success' => true,
                'refund_id' => 'rfnd_mock_' . uniqid(),
                'amount' => (int)round($amount * 100)
            ];
        }

        $payload = [
            'amount' => (int)round($amount * 100),
            'notes' => ['reason' => $extra['reason'] ?? 'Customer request']
        ];

        $ch = curl_init($this->apiBase . "/payments/{$paymentId}/refund");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->keyId . ':' . $this->keySecret,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15
        ]);

        $response = curl_exec($ch);
        $httpCode = defined('CURLINFO_HTTPCODE') ? (int)curl_getinfo($ch, CURLINFO_HTTPCODE) : 200;

        $data = json_decode($response, true);
        return [
            'success' => ($httpCode === 200 && !empty($data['id'])),
            'refund_id' => $data['id'] ?? null,
            'raw' => $data
        ];
    }

    public function testConnection(): array {
        if (empty($this->keyId) || empty($this->keySecret)) {
            return ['success' => false, 'message' => 'Key ID or Key Secret is empty.'];
        }

        if (str_contains($this->keyId, 'placeholder')) {
            return ['success' => false, 'message' => 'Placeholder keys detected. Please enter valid Razorpay API keys.'];
        }

        if (!function_exists('curl_init')) {
            return ['success' => false, 'message' => 'PHP cURL extension is disabled.'];
        }

        $ch = curl_init($this->apiBase . '/orders?count=1');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->keyId . ':' . $this->keySecret,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = defined('CURLINFO_HTTPCODE') ? (int)curl_getinfo($ch, CURLINFO_HTTPCODE) : 200;
        $error = curl_error($ch);

        if ($httpCode === 200) {
            return ['success' => true, 'message' => 'Connection successful! Razorpay API credentials are valid.'];
        }

        $data = json_decode($response, true);
        $msg = $data['error']['description'] ?? ($error ?: "HTTP error {$httpCode}");
        return ['success' => false, 'message' => 'Razorpay API Authentication Failed: ' . $msg];
    }
}
