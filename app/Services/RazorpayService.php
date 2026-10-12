<?php

namespace App\Services;

use App\Core\Database;
use App\Helpers\Encryption;

class RazorpayService
{
    private string $keyId = '';
    private string $keySecret = '';
    private string $webhookSecret = '';
    private string $mode = 'test';
    private bool $active = false;
    private string $blockReason = '';

    public function __construct()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM payment_gateway_settings WHERE provider = 'razorpay' ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $setting = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('Razorpay settings load failed: ' . $e->getMessage());
            $this->blockReason = 'Online payment is temporarily unavailable. Please request a quote and our team will help you place the order.';
            return;
        }

        if (!$setting) {
            $this->blockReason = 'Online payment is not set up yet. Please request a quote instead.';
            return;
        }

        $this->active = (int)($setting['is_active'] ?? 0) === 1;
        $this->mode = (($setting['mode'] ?? 'test') === 'live') ? 'live' : 'test';
        $this->keyId = trim((string)($setting['key_id'] ?? ''));
        $this->keySecret = Encryption::decrypt((string)($setting['key_secret'] ?? ''));
        $this->webhookSecret = Encryption::decrypt((string)($setting['webhook_secret'] ?? ''));

        if (!$this->active) {
            $this->blockReason = 'Online payment is turned off right now. Please request a quote, or try again later.';
            return;
        }

        if ($this->keyId === '' || $this->keySecret === '' || $this->isPlaceholder($this->keyId) || $this->isPlaceholder($this->keySecret)) {
            $storedSecret = (string)($setting['key_secret'] ?? '');
            if (str_starts_with($storedSecret, 'enc::') && $this->keySecret === '') {
                $this->blockReason = 'Payment keys could not be read on this server. The encryption key must match the one used when the secrets were saved.';
            } else {
                $this->blockReason = 'Online payment is not configured yet. Please request a quote and we will confirm your order.';
            }
        }
    }

    public function isReady(): bool
    {
        return $this->blockReason === '' && $this->keyId !== '' && $this->keySecret !== '';
    }

    public function getBlockReason(): string
    {
        return $this->blockReason !== ''
            ? $this->blockReason
            : 'Online payment is not available right now.';
    }

    public function getKeyId(): string
    {
        return $this->isReady() ? $this->keyId : '';
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Public checkout config. Never includes the key secret or webhook secret.
     */
    public function publicConfig(): array
    {
        return [
            'ready' => $this->isReady(),
            'message' => $this->isReady() ? '' : $this->getBlockReason(),
            'key_id' => $this->getKeyId(),
            'mode' => $this->mode,
            'currency' => 'INR',
        ];
    }

    public function createOrder(float $amountInINR, string $receiptId, array $notes = []): array
    {
        if (!$this->isReady()) {
            return ['success' => false, 'message' => $this->getBlockReason()];
        }

        $amountInPaisa = (int)round($amountInINR * 100);
        if ($amountInPaisa < 100) {
            return ['success' => false, 'message' => 'Order amount is too small to pay online.'];
        }

        if (!function_exists('curl_init')) {
            error_log('Razorpay create order: cURL extension is missing');
            return ['success' => false, 'message' => 'Online payment is temporarily unavailable. Please request a quote.'];
        }

        $safeNotes = [];
        foreach ($notes as $k => $v) {
            $safeNotes[(string)$k] = mb_substr((string)$v, 0, 180);
        }

        $payload = [
            'amount' => $amountInPaisa,
            'currency' => 'INR',
            'receipt' => mb_substr($receiptId, 0, 40),
            'payment_capture' => 1,
            'notes' => $safeNotes,
        ];

        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt_array($ch, [
            CURLOPT_USERPWD => $this->keyId . ':' . $this->keySecret,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 20,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        $data = json_decode((string)$response, true);
        if ($error || $httpCode >= 400 || empty($data['id'])) {
            $desc = $data['error']['description'] ?? ($error ?: 'HTTP ' . $httpCode);
            error_log('Razorpay order create failed (' . $httpCode . '): ' . $desc);
            return ['success' => false, 'message' => 'We could not start the payment. Please try again, or request a quote.'];
        }

        return [
            'success' => true,
            'razorpay_order_id' => $data['id'],
            'amount' => $amountInPaisa,
        ];
    }

    public function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $signature): bool
    {
        if (!$this->isReady() || $signature === '' || $razorpayOrderId === '' || $razorpayPaymentId === '') {
            return false;
        }
        if (!preg_match('/^order_[A-Za-z0-9]+$/', $razorpayOrderId) || !preg_match('/^pay_[A-Za-z0-9]+$/', $razorpayPaymentId)) {
            return false;
        }

        $generated = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $this->keySecret);
        return hash_equals($generated, $signature);
    }

    public function verifyWebhook(string $rawBody, string $signature): bool
    {
        if ($rawBody === '' || $signature === '' || $this->webhookSecret === '' || $this->isPlaceholder($this->webhookSecret)) {
            return false;
        }
        $expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);
        return hash_equals($expected, $signature);
    }

    private function isPlaceholder(string $value): bool
    {
        $v = strtolower($value);
        return $v === '' || str_contains($v, 'placeholder') || str_contains($v, 'rzp_test_secret_123');
    }
}
