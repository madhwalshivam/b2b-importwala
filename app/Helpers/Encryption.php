<?php
namespace App\Helpers;

class Encryption {
    /**
     * Encryption method algorithm
     */
    private static string $cipher = 'aes-256-cbc';

    /**
     * Get or derive secret encryption key
     */
    private static function getSecretKey(): string {
        $envKey = getenv('APP_SECRET_KEY');
        if (!empty($envKey)) {
            return hash('sha256', $envKey, true);
        }
        $appConfigKey = $GLOBALS['app_config']['secret_key'] ?? 'mudsor_ecommerce_secure_secret_key_2026';
        return hash('sha256', $appConfigKey, true);
    }

    /**
     * Encrypt sensitive string data using AES-256-CBC
     */
    public static function encrypt(?string $plainText): string {
        if ($plainText === null || $plainText === '') {
            return '';
        }

        // If string is already encrypted format (enc::...), return as is
        if (str_starts_with($plainText, 'enc::')) {
            return $plainText;
        }

        $key = self::getSecretKey();
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $encryptedRaw = openssl_encrypt($plainText, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($encryptedRaw === false) {
            return $plainText; // Fallback
        }

        $hmac = hash_hmac('sha256', $iv . $encryptedRaw, $key, true);
        return 'enc::' . base64_encode($iv . $hmac . $encryptedRaw);
    }

    /**
     * Decrypt AES-256-CBC encrypted string
     */
    public static function decrypt(?string $encryptedData): string {
        if ($encryptedData === null || $encryptedData === '') {
            return '';
        }

        if (!str_starts_with($encryptedData, 'enc::')) {
            return $encryptedData; // Plaintext legacy or placeholder
        }

        $payload = base64_decode(substr($encryptedData, 5));
        $key = self::getSecretKey();
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $hmacLength = 32;

        if (strlen($payload) < ($ivLength + $hmacLength)) {
            return $encryptedData;
        }

        $iv = substr($payload, 0, $ivLength);
        $hmac = substr($payload, $ivLength, $hmacLength);
        $encryptedRaw = substr($payload, $ivLength + $hmacLength);

        $calculatedHmac = hash_hmac('sha256', $iv . $encryptedRaw, $key, true);
        if (!hash_equals($hmac, $calculatedHmac)) {
            return ''; // Tampered or corrupted secret
        }

        $decrypted = openssl_decrypt($encryptedRaw, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted !== false ? $decrypted : '';
    }

    /**
     * Mask sensitive strings for UI display (e.g. show only last 4 characters)
     */
    public static function maskSecret(?string $plainText): string {
        if (empty($plainText)) {
            return '';
        }
        $len = strlen($plainText);
        if ($len <= 4) {
            return str_repeat('•', $len);
        }
        return str_repeat('•', max(8, $len - 4)) . substr($plainText, -4);
    }
}
