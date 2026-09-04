<?php
namespace Lib\Shipping;

use App\Core\Database;

class ShiprocketProvider implements ShippingProviderInterface {
    protected string $email;
    protected string $password;
    protected string $pickupLocation;
    protected bool $autoAssignCourier;
    protected string $apiBase = 'https://apiv2.shiprocket.in/v1/external';
    protected ?string $token = null;

    public function __construct(
        ?string $email = null,
        ?string $password = null,
        ?string $pickupLocation = null,
        bool $autoAssignCourier = true
    ) {
        $this->email = $email ?: 'admin@mudsor.com';
        $this->password = $password ?: '';
        $this->pickupLocation = $pickupLocation ?: 'Primary';
        $this->autoAssignCourier = $autoAssignCourier;
    }

    /**
     * Authenticate with Shiprocket API with token caching (~10 days expiry)
     */
    protected ?string $lastAuthError = null;

    public function getLastAuthError(): ?string {
        return $this->lastAuthError;
    }

    protected function authenticate(bool $forceFresh = false): ?string {
        if (!$forceFresh && $this->token) {
            return $this->token;
        }

        // Check cached token in database (if not forceFresh)
        if (!$forceFresh) {
            try {
                $db = Database::getInstance();
                $stmt = $db->query("SELECT token, token_expires_at FROM shipping_settings WHERE provider = 'shiprocket' ORDER BY id DESC LIMIT 1");
                $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : null;
                if ($row && !empty($row['token']) && !empty($row['token_expires_at'])) {
                    if (strtotime($row['token_expires_at']) > (time() + 3600)) { // 1 hour buffer
                        $this->token = $row['token'];
                        return $this->token;
                    }
                }
            } catch (\Throwable $e) {
                error_log("Shiprocket token DB check failed: " . $e->getMessage());
            }
        }

        if (!function_exists('curl_init') || empty($this->email) || empty($this->password)) {
            $this->lastAuthError = 'Email or Password is empty';
            return null;
        }

        $ch = curl_init($this->apiBase . '/auth/login');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['email' => $this->email, 'password' => $this->password]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 12
        ]);

        $response = curl_exec($ch);
        $httpCode = defined('CURLINFO_HTTPCODE') ? (int)curl_getinfo($ch, CURLINFO_HTTPCODE) : 200;

        $data = json_decode($response, true);
        if ($httpCode === 200 && isset($data['token'])) {
            $this->token = $data['token'];
            $this->lastAuthError = null;
            // Cache token in DB (Shiprocket token is valid for 10 days)
            try {
                $expiresAt = date('Y-m-d H:i:s', time() + (9 * 86400));
                $db = Database::getInstance();
                $stmt = $db->prepare("UPDATE shipping_settings SET token = ?, token_expires_at = ? WHERE provider = 'shiprocket'");
                $stmt->execute([$this->token, $expiresAt]);
            } catch (\Throwable $e) {
                // Ignore DB update warning
            }
            return $this->token;
        }

        $this->lastAuthError = $data['message'] ?? 'Invalid email or password.';
        return null;
    }

    public function createShipment(array $orderDetails): array {
        $token = $this->authenticate();
        if (!$token) {
            // Development/Mock Fallback
            $mockAwb = 'AWB' . rand(10000000, 99999999);
            return [
                'success' => true,
                'is_mock' => true,
                'shiprocket_order_id' => 'SR_ORD_' . rand(10000, 99999),
                'shipment_id' => 'SR_SHP_' . rand(10000, 99999),
                'awb_code' => $mockAwb,
                'courier_name' => 'Bluedart Express',
                'tracking_url' => 'https://shiprocket.co/tracking/' . $mockAwb
            ];
        }

        $items = [];
        $totalWeight = 0.0;
        foreach ($orderDetails['items'] ?? [] as $item) {
            $qty = (int)($item['quantity'] ?? 1);
            $weight = (float)($item['weight_kg'] ?? 0.5);
            $totalWeight += ($weight * $qty);

            $items[] = [
                'name' => substr($item['name'] ?? $item['product_name'] ?? 'Spare Part', 0, 50),
                'sku' => $item['sku'] ?? 'GENERIC',
                'units' => $qty,
                'selling_price' => (float)($item['price'] ?? 0)
            ];
        }

        $address = is_array($orderDetails['address'] ?? null) 
            ? $orderDetails['address'] 
            : json_decode($orderDetails['address'] ?? '{}', true);

        $customerName = trim(($address['full_name'] ?? '') ?: ($orderDetails['customer_name'] ?? 'Customer'));
        $nameParts = explode(' ', $customerName, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $channelId = $this->getChannelId($token);

        $billingAddr1 = !empty($address['address_line1']) ? $address['address_line1'] : 'Main Road';
        $billingCity  = !empty($address['city']) ? $address['city'] : 'Delhi';
        $billingState = !empty($address['state']) ? $address['state'] : 'Delhi';
        $billingPin   = !empty($address['pincode']) ? $address['pincode'] : '110001';
        $billingEmail = !empty($address['email']) ? $address['email'] : ($orderDetails['customer_email'] ?? 'customer@mudsor.com');
        $billingPhone = !empty($address['phone']) ? $address['phone'] : ($orderDetails['customer_phone'] ?? '9999999999');

        $payload = [
            'order_id' => $orderDetails['order_number'],
            'order_date' => date('Y-m-d H:i:s'),
            'pickup_location' => $this->pickupLocation ?: 'Primary',
            'billing_customer_name' => $firstName,
            'billing_last_name' => $lastName,
            'billing_address' => $billingAddr1,
            'billing_address_2' => $address['address_line2'] ?? '',
            'billing_city' => $billingCity,
            'billing_pincode' => $billingPin,
            'billing_state' => $billingState,
            'billing_country' => $address['country'] ?? 'India',
            'billing_email' => $billingEmail,
            'billing_phone' => $billingPhone,
            'shipping_is_billing' => true,
            'shipping_customer_name' => $firstName,
            'shipping_last_name' => $lastName,
            'shipping_address' => $billingAddr1,
            'shipping_address_2' => $address['address_line2'] ?? '',
            'shipping_city' => $billingCity,
            'shipping_pincode' => $billingPin,
            'shipping_country' => $address['country'] ?? 'India',
            'shipping_state' => $billingState,
            'shipping_email' => $billingEmail,
            'shipping_phone' => $billingPhone,
            'order_items' => $items,
            'payment_method' => strtolower($orderDetails['payment_method'] ?? 'cod') === 'cod' ? 'COD' : 'Prepaid',
            'sub_total' => (float)($orderDetails['total_amount'] ?? 0),
            'length' => 15,
            'breadth' => 15,
            'height' => 10,
            'weight' => max(0.5, $totalWeight)
        ];

        if ($channelId) {
            $payload['channel_id'] = $channelId;
        }

        $ch = curl_init($this->apiBase . '/orders/create/adhoc');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 15
        ]);

        $response = curl_exec($ch);
        $httpCode = defined('CURLINFO_HTTPCODE') ? (int)curl_getinfo($ch, CURLINFO_HTTPCODE) : 200;

        $data = json_decode($response, true);
        if ($httpCode >= 400 || empty($data['order_id'])) {
            $msg = $data['message'] ?? '';
            if (str_contains(strtolower($msg), 'billing/shipping address') || str_contains(strtolower($msg), 'pickup location')) {
                if ($this->ensurePickupLocationCreated($token, $payload)) {
                    $chRetry = curl_init($this->apiBase . '/orders/create/adhoc');
                    curl_setopt_array($chRetry, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => json_encode($payload),
                        CURLOPT_HTTPHEADER => [
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $token
                        ],
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_TIMEOUT => 15
                    ]);
                    $resRetry = curl_exec($chRetry);
                    $httpCodeRetry = defined('CURLINFO_HTTPCODE') ? (int)curl_getinfo($chRetry, CURLINFO_HTTPCODE) : 200;
                    $dataRetry = json_decode($resRetry, true);
                    if ($httpCodeRetry < 400 && !empty($dataRetry['order_id'])) {
                        $data = $dataRetry;
                        $httpCode = 200;
                    }
                }
            }
        }

        if ($httpCode >= 400 || empty($data['order_id'])) {
            error_log("Shiprocket Order Push Error ({$httpCode}): " . json_encode($data));
            return [
                'success' => false,
                'message' => $data['message'] ?? 'Failed to push order to Shiprocket',
                'raw' => $data
            ];
        }

        $srOrderId = $data['order_id'];
        $shipmentId = $data['shipment_id'] ?? null;
        $awbCode = $data['awb_code'] ?? null;
        $courierName = $data['courier_name'] ?? 'Shiprocket Logistics';

        // Auto-assign courier & generate AWB if enabled and shipment_id exists
        if ($this->autoAssignCourier && $shipmentId && empty($awbCode)) {
            $assignResult = $this->autoAssignCourierAndAWB($shipmentId, $token);
            if (!empty($assignResult['awb_code'])) {
                $awbCode = $assignResult['awb_code'];
                $courierName = $assignResult['courier_name'] ?? $courierName;
            }
        }

        $trackingUrl = $awbCode ? 'https://shiprocket.co/tracking/' . $awbCode : null;

        return [
            'success' => true,
            'is_mock' => false,
            'shiprocket_order_id' => (string)$srOrderId,
            'shipment_id' => (string)$shipmentId,
            'awb_code' => $awbCode,
            'courier_name' => $courierName,
            'tracking_url' => $trackingUrl,
            'raw' => $data
        ];
    }

    /**
     * Auto assign cheapest/fastest courier and get AWB
     */
    protected function autoAssignCourierAndAWB(int|string $shipmentId, string $token): array {
        $ch = curl_init($this->apiBase . '/courier/assign/awb');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['shipment_id' => $shipmentId]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_TIMEOUT => 12
        ]);
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        if (!empty($data['response']['data']['awb_code'])) {
            return [
                'awb_code' => $data['response']['data']['awb_code'],
                'courier_name' => $data['response']['data']['courier_name'] ?? 'Courier Partner'
            ];
        }
        return [];
    }

    public function trackShipment(string $awbCode): array {
        $token = $this->authenticate();
        if (!$token || str_starts_with($awbCode, 'AWB')) {
            return [
                'status' => 'IN_TRANSIT',
                'courier_name' => 'Bluedart Express',
                'current_location' => 'Sorting Facility, New Delhi',
                'etd' => date('d M Y', strtotime('+2 days')),
                'history' => [
                    ['time' => date('Y-m-d H:i:s', strtotime('-1 day')), 'activity' => 'Order dispatched from warehouse'],
                    ['time' => date('Y-m-d H:i:s'), 'activity' => 'In transit to destination city hub']
                ]
            ];
        }

        $ch = curl_init($this->apiBase . '/courier/track/awb/' . urlencode($awbCode));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);

        $data = json_decode($response, true);
        $trackData = $data['tracking_data'] ?? [];
        return [
            'status' => $trackData['track_status'] ?? 'PROCESSING',
            'courier_name' => $trackData['courier_name'] ?? 'Courier',
            'current_location' => $trackData['shipment_track'][0]['location'] ?? 'Hub',
            'etd' => $trackData['etd'] ?? null,
            'history' => $trackData['shipment_track'] ?? [],
            'raw' => $data
        ];
    }

    public function cancelShipment(string $shipmentId): array {
        $token = $this->authenticate();
        if (!$token) {
            return ['success' => true, 'message' => 'Shipment cancelled (mock mode).'];
        }

        $ch = curl_init($this->apiBase . '/orders/cancel');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['ids' => [(int)$shipmentId]]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = defined('CURLINFO_HTTPCODE') ? (int)curl_getinfo($ch, CURLINFO_HTTPCODE) : 200;

        $data = json_decode($response, true);
        return [
            'success' => ($httpCode === 200),
            'message' => $data['message'] ?? 'Cancellation processed',
            'raw' => $data
        ];
    }

    protected function ensurePickupLocationCreated(string $token, array $payload): bool {
        $pickupLocationName = $payload['pickup_location'] ?? 'Primary';
        $locationPayload = [
            'pickup_location' => $pickupLocationName,
            'name' => trim(($payload['billing_customer_name'] ?? 'Mudsor') . ' ' . ($payload['billing_last_name'] ?? 'Enterprise')),
            'email' => $payload['billing_email'] ?? $this->email,
            'phone' => $payload['billing_phone'] ?? '9999999999',
            'address' => !empty($payload['billing_address']) ? $payload['billing_address'] : 'Main Warehouse',
            'address_2' => $payload['billing_address_2'] ?? '',
            'city' => !empty($payload['billing_city']) ? $payload['billing_city'] : 'Delhi',
            'state' => !empty($payload['billing_state']) ? $payload['billing_state'] : 'Delhi',
            'country' => $payload['billing_country'] ?? 'India',
            'pin_code' => !empty($payload['billing_pincode']) ? $payload['billing_pincode'] : '110001'
        ];

        $ch = curl_init($this->apiBase . '/settings/company/addpickup');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($locationPayload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 15
        ]);
        $res = curl_exec($ch);
        $data = json_decode($res, true);
        return !empty($data['success']) || !empty($data['pickup_id']);
    }

    public function getPickupLocations(): array {
        $token = $this->authenticate();
        if (!$token) {
            return [
                ['pickup_location' => 'Primary'],
                ['pickup_location' => 'Delhi Warehouse'],
                ['pickup_location' => 'Bawana Hub']
            ];
        }

        $ch = curl_init($this->apiBase . '/settings/company/pickup');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);

        $data = json_decode($response, true);
        $locations = $data['data']['shipping_address'] ?? [];
        $result = [];
        foreach ($locations as $loc) {
            if (!empty($loc['pickup_location'])) {
                $result[] = ['pickup_location' => $loc['pickup_location']];
            }
        }
        return !empty($result) ? $result : [['pickup_location' => 'Primary']];
    }

    public function getChannelId(?string $token = null): ?int {
        $token = $token ?: $this->authenticate();
        if (!$token) return null;

        $ch = curl_init($this->apiBase . '/channels');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);

        $data = json_decode($response, true);
        $channels = $data['data'] ?? [];
        foreach ($channels as $c) {
            if (!empty($c['id'])) {
                if (strcasecmp($c['name'] ?? '', 'Mudsor') === 0) {
                    return (int)$c['id'];
                }
            }
        }
        return !empty($channels[0]['id']) ? (int)$channels[0]['id'] : null;
    }

    public function testConnection(): array {
        if (empty($this->email) || empty($this->password)) {
            return ['success' => false, 'message' => 'Email or Password is empty.'];
        }

        $token = $this->authenticate(true);
        if (!$token) {
            $errMsg = $this->getLastAuthError() ?: 'Invalid email or password.';
            return ['success' => false, 'message' => 'Shiprocket Authentication Failed: ' . $errMsg];
        }

        $locations = $this->getPickupLocations();
        return [
            'success' => true,
            'message' => 'Connection successful! Shiprocket API credentials are valid.',
            'locations' => array_column($locations, 'pickup_location')
        ];
    }
}
