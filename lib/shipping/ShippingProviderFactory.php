<?php
namespace Lib\Shipping;

use App\Core\Database;
use App\Helpers\Encryption;

class ShippingProviderFactory {
    private static ?array $cachedSettings = null;
    private static ?int $cacheTime = null;
    private static int $ttl = 300; // 5-minute cache

    /**
     * Create and return an instance of active ShippingProviderInterface
     *
     * @param bool $forceFresh Skip memory cache
     * @return ShippingProviderInterface
     */
    public static function make(bool $forceFresh = false): ShippingProviderInterface {
        $settings = self::getSettings($forceFresh);
        $provider = strtolower($settings['provider'] ?? 'shiprocket');

        $email = $settings['email'] ?? '';
        $password = Encryption::decrypt($settings['password'] ?? '');
        $pickupLocation = $settings['pickup_location'] ?? 'Primary';
        $autoAssign = (bool)($settings['auto_assign_courier'] ?? true);

        switch ($provider) {
            case 'shiprocket':
            default:
                return new ShiprocketProvider($email, $password, $pickupLocation, $autoAssign);
        }
    }

    public static function clearCache(): void {
        self::$cachedSettings = null;
        self::$cacheTime = null;
    }

    /**
     * Fetch active shipping settings from database
     */
    public static function getSettings(bool $forceFresh = false): array {
        $now = time();
        if (!$forceFresh && self::$cachedSettings !== null && self::$cacheTime !== null && ($now - self::$cacheTime) < self::$ttl) {
            return self::$cachedSettings;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM shipping_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
            $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : null;
            if ($row) {
                self::$cachedSettings = $row;
                self::$cacheTime = $now;
                return $row;
            }
        } catch (\Throwable $e) {
            error_log("Failed to load shipping settings: " . $e->getMessage());
        }

        return [
            'provider' => 'shiprocket',
            'email' => 'admin@mudsor.com',
            'password' => 'demo_password',
            'pickup_location' => 'Primary',
            'auto_assign_courier' => 1,
            'is_active' => 1
        ];
    }
}
