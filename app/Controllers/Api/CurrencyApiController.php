<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class CurrencyApiController extends BaseController
{
    private array $countryCurrencyMap = [
        'US' => ['code' => 'USD', 'symbol' => '$', 'rate' => 1.00],
        'IN' => ['code' => 'INR', 'symbol' => '₹', 'rate' => 83.50],
        'EU' => ['code' => 'EUR', 'symbol' => '€', 'rate' => 0.92],
        'GB' => ['code' => 'GBP', 'symbol' => '£', 'rate' => 0.78],
        'CA' => ['code' => 'CAD', 'symbol' => '$', 'rate' => 1.35],
        'AU' => ['code' => 'AUD', 'symbol' => '$', 'rate' => 1.52],
    ];

    public function setPreference(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $country = strtoupper(trim($input['country'] ?? 'US'));
        $currency = strtoupper(trim($input['currency'] ?? 'USD'));
        $language = trim($input['language'] ?? 'EN');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_country'] = $country;
        $_SESSION['user_currency'] = $currency;
        $_SESSION['user_language'] = $language;

        setcookie('user_currency', $currency, time() + 2592000, '/');
        setcookie('user_country', $country, time() + 2592000, '/');

        echo json_encode([
            'success' => true,
            'country' => $country,
            'currency' => $currency,
            'language' => $language,
        ]);
    }

    public function getMap(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'map' => $this->countryCurrencyMap]);
    }
}
