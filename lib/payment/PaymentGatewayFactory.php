<?php
namespace Lib\Payment;

use App\Core\Database;
use App\Helpers\Encryption;

class PaymentGatewayFactory {
    private static ?array $cachedSettings = null;
    private static ?int $cacheTime = null;
    private static int $ttl = 300; // 5-minute cache

    /**
     * Create and return an instance of active PaymentGatewayInterface
     *
     * @param bool $forceFresh Skip memory cache
     * @return PaymentGatewayInterface
     */
    public static function make(bool $forceFresh = false): PaymentGatewayInterface {
        $settings = self::getSettings($forceFresh);
        $provider = strtolower($settings['provider'] ?? 'razorpay');

        $keyId = $settings['key_id'] ?? '';
        $keySecret = Encryption::decrypt($settings['key_secret'] ?? '');
        $webhookSecret = Encryption::decrypt($settings['webhook_secret'] ?? '');
        $mode = $settings['mode'] ?? 'test';

        switch ($provider) {
            case 'razorpay':
            default:
                return new RazorpayGateway($keyId, $keySecret, $webhookSecret, $mode);
        }
    }

    public static function clearCache(): void {
        self::$cachedSettings = null;
        self::$cacheTime = null;
    }

    /**
     * Fetch active payment gateway settings from database
     */
    public static function getSettings(bool $forceFresh = false): array {
        $now = time();
        if (!$forceFresh && self::$cachedSettings !== null && self::$cacheTime !== null && ($now - self::$cacheTime) < self::$ttl) {
            return self::$cachedSettings;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM payment_gateway_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
            $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : null;
            if ($row) {
                self::$cachedSettings = $row;
                self::$cacheTime = $now;
                return $row;
            }
        } catch (\Throwable $e) {
            error_log("Failed to load payment gateway settings: " . $e->getMessage());
        }

        return [
            'provider' => 'razorpay',
            'key_id' => 'rzp_test_placeholder_key',
            'key_secret' => 'rzp_test_placeholder_secret',
            'webhook_secret' => '',
            'mode' => 'test',
            'is_active' => 1
        ];
    }
}
