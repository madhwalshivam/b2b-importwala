<?php
namespace App\Services\Payment;

class RazorpayGateway extends \Lib\Payment\RazorpayGateway implements PaymentGatewayInterface {
    public function handleWebhook(array $payload, string $signature): bool {
        $factorySettings = \Lib\Payment\PaymentGatewayFactory::getSettings();
        $secret = \App\Helpers\Encryption::decrypt($factorySettings['webhook_secret'] ?? '');
        if (empty($secret) || empty($signature)) {
            return false;
        }
        $rawBody = json_encode($payload);
        $expected = hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $signature);
    }
}
