<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Helpers\Encryption;

require_once ROOT_PATH . '/lib/payment/PaymentGatewayInterface.php';
require_once ROOT_PATH . '/lib/payment/RazorpayGateway.php';
require_once ROOT_PATH . '/lib/payment/PaymentGatewayFactory.php';
require_once ROOT_PATH . '/lib/shipping/ShippingProviderInterface.php';
require_once ROOT_PATH . '/lib/shipping/ShiprocketProvider.php';
require_once ROOT_PATH . '/lib/shipping/ShippingProviderFactory.php';

use Lib\Payment\RazorpayGateway;
use Lib\Payment\PaymentGatewayFactory;
use Lib\Shipping\ShiprocketProvider;
use Lib\Shipping\ShippingProviderFactory;

class PaymentShippingSettingsController extends Controller {

    public function index(): string {
        if (!Auth::hasPermission('settings.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $razorpay = [
            'key_id' => '',
            'key_secret' => '',
            'webhook_secret' => '',
            'mode' => 'test',
            'is_active' => 1
        ];

        $shiprocket = [
            'email' => '',
            'password' => '',
            'pickup_location' => 'Primary',
            'auto_assign_courier' => 1,
            'is_active' => 1
        ];

        $auditLogs = [];
        $pickupLocations = [
            ['pickup_location' => 'Primary'],
            ['pickup_location' => 'Delhi Warehouse'],
            ['pickup_location' => 'Bawana Hub']
        ];

        try {
            $db = Database::getInstance();

            // Fetch Razorpay Settings
            $stmtPg = $db->query("SELECT * FROM payment_gateway_settings WHERE provider = 'razorpay' ORDER BY id DESC LIMIT 1");
            $rowPg = $stmtPg ? $stmtPg->fetch(\PDO::FETCH_ASSOC) : null;
            if ($rowPg) {
                $razorpay = array_merge($razorpay, $rowPg);
            }

            // Fetch Shiprocket Settings
            $stmtSp = $db->query("SELECT * FROM shipping_settings WHERE provider = 'shiprocket' ORDER BY id DESC LIMIT 1");
            $rowSp = $stmtSp ? $stmtSp->fetch(\PDO::FETCH_ASSOC) : null;
            if ($rowSp) {
                $shiprocket = array_merge($shiprocket, $rowSp);
            }

            // Fetch recent audit logs
            try {
                $stmtLogs = $db->query("SELECT * FROM admin_audit_log WHERE action LIKE '%Payment%' OR action LIKE '%Shipping%' ORDER BY id DESC LIMIT 10");
                if ($stmtLogs) {
                    $auditLogs = $stmtLogs->fetchAll(\PDO::FETCH_ASSOC);
                }
            } catch (\Throwable $e) {
                // Ignore audit log error gracefully
            }
        } catch (\Throwable $e) {
            error_log("PaymentShippingSettingsController DB error: " . $e->getMessage());
        }

        // Decrypt for processing masking
        $plainKeySecret = Encryption::decrypt($razorpay['key_secret'] ?? '');
        $plainWebhookSecret = Encryption::decrypt($razorpay['webhook_secret'] ?? '');
        $plainShiprocketPass = Encryption::decrypt($shiprocket['password'] ?? '');

        // Masked representations
        $maskedKeySecret = Encryption::maskSecret($plainKeySecret);
        $maskedWebhookSecret = Encryption::maskSecret($plainWebhookSecret);
        $maskedShiprocketPass = Encryption::maskSecret($plainShiprocketPass);

        // Fetch Pickup Locations from Shiprocket Provider safely
        try {
            if (!empty($shiprocket['email']) && !empty($plainShiprocketPass)) {
                $shiprocketProvider = new ShiprocketProvider($shiprocket['email'], $plainShiprocketPass, $shiprocket['pickup_location'] ?? 'Primary');
                $fetchedLocations = $shiprocketProvider->getPickupLocations();
                if (!empty($fetchedLocations)) {
                    $pickupLocations = $fetchedLocations;
                }
            }
        } catch (\Throwable $e) {
            error_log("PaymentShippingSettingsController Shiprocket API error: " . $e->getMessage());
        }

        return $this->render('admin/settings/payment_shipping', [
            'razorpay' => $razorpay,
            'shiprocket' => $shiprocket,
            'maskedKeySecret' => $maskedKeySecret,
            'maskedWebhookSecret' => $maskedWebhookSecret,
            'maskedShiprocketPass' => $maskedShiprocketPass,
            'pickupLocations' => $pickupLocations,
            'auditLogs' => $auditLogs
        ]);
    }

    public function updateRazorpay(): void {
        if (!Auth::hasPermission('settings.edit')) {
            $this->redirect(url('admin/settings/payment-shipping'));
        }

        try {
            $keyId = trim((string)$this->request->input('key_id', ''));
            $rawKeySecret = trim((string)$this->request->input('key_secret', ''));
            $rawWebhookSecret = trim((string)$this->request->input('webhook_secret', ''));
            $mode = in_array($this->request->input('mode'), ['test', 'live']) ? $this->request->input('mode') : 'test';
            $isActive = (int)$this->request->input('is_active', 1);

            $db = Database::getInstance();
            $stmtCurrent = $db->query("SELECT id, key_secret, webhook_secret FROM payment_gateway_settings WHERE provider = 'razorpay' ORDER BY id DESC LIMIT 1");
            $current = $stmtCurrent ? $stmtCurrent->fetch(\PDO::FETCH_ASSOC) : null;

            // Preserve existing encrypted secret if submitted input is masked (contains bullet points)
            if (str_contains($rawKeySecret, '•') && !empty($current['key_secret'])) {
                $encryptedKeySecret = $current['key_secret'];
            } else {
                $encryptedKeySecret = Encryption::encrypt($rawKeySecret);
            }

            if (str_contains($rawWebhookSecret, '•') && !empty($current['webhook_secret'])) {
                $encryptedWebhookSecret = $current['webhook_secret'];
            } else {
                $encryptedWebhookSecret = Encryption::encrypt($rawWebhookSecret);
            }

            if ($current && !empty($current['id'])) {
                $stmt = $db->prepare("
                    UPDATE payment_gateway_settings SET
                        key_id = ?,
                        key_secret = ?,
                        webhook_secret = ?,
                        mode = ?,
                        is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$keyId, $encryptedKeySecret, $encryptedWebhookSecret, $mode, $isActive, $current['id']]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO payment_gateway_settings (provider, key_id, key_secret, webhook_secret, mode, is_active)
                    VALUES ('razorpay', ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$keyId, $encryptedKeySecret, $encryptedWebhookSecret, $mode, $isActive]);
            }

            PaymentGatewayFactory::clearCache();

            admin_audit_log('Update Payment Settings', "Updated Razorpay configuration (Mode: {$mode}, Active: {$isActive})");

            $this->setFlash('success', 'Razorpay payment gateway settings updated successfully.');
        } catch (\Throwable $e) {
            error_log("Update Razorpay Error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update Razorpay settings: ' . $e->getMessage());
        }

        $this->redirect(url('admin/settings/payment-shipping'));
    }

    public function updateShiprocket(): void {
        if (!Auth::hasPermission('settings.edit')) {
            $this->redirect(url('admin/settings/payment-shipping'));
        }

        try {
            $email = trim((string)$this->request->input('email', ''));
            $rawPassword = trim((string)$this->request->input('password', ''));
            $pickupLocation = trim((string)$this->request->input('pickup_location', 'Primary'));
            $autoAssign = (int)$this->request->input('auto_assign_courier', 1);
            $isActive = (int)$this->request->input('is_active', 1);

            $db = Database::getInstance();
            $stmtCurrent = $db->query("SELECT id, password FROM shipping_settings WHERE provider = 'shiprocket' ORDER BY id DESC LIMIT 1");
            $current = $stmtCurrent ? $stmtCurrent->fetch(\PDO::FETCH_ASSOC) : null;

            if (str_contains($rawPassword, '•') && !empty($current['password'])) {
                $encryptedPassword = $current['password'];
            } else {
                $encryptedPassword = Encryption::encrypt($rawPassword);
            }

            if ($current && !empty($current['id'])) {
                $stmt = $db->prepare("
                    UPDATE shipping_settings SET
                        email = ?,
                        password = ?,
                        pickup_location = ?,
                        auto_assign_courier = ?,
                        is_active = ?,
                        token = NULL,
                        token_expires_at = NULL
                    WHERE id = ?
                ");
                $stmt->execute([$email, $encryptedPassword, $pickupLocation, $autoAssign, $isActive, $current['id']]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO shipping_settings (provider, email, password, pickup_location, auto_assign_courier, is_active)
                    VALUES ('shiprocket', ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$email, $encryptedPassword, $pickupLocation, $autoAssign, $isActive]);
            }

            ShippingProviderFactory::clearCache();

            admin_audit_log('Update Shipping Settings', "Updated Shiprocket configuration (Email: {$email}, Pickup: {$pickupLocation})");

            $this->setFlash('success', 'Shiprocket shipping provider settings updated successfully.');
        } catch (\Throwable $e) {
            error_log("Update Shiprocket Error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update Shiprocket settings: ' . $e->getMessage());
        }

        $this->redirect(url('admin/settings/payment-shipping'));
    }

    public function testRazorpay(): void {
        header('Content-Type: application/json');
        if (!Auth::hasPermission('settings.view')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $keyId = trim((string)$this->request->input('key_id', ''));
        $rawKeySecret = trim((string)$this->request->input('key_secret', ''));

        // If masked secret, fetch stored secret
        if (str_contains($rawKeySecret, '•')) {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT key_secret FROM payment_gateway_settings WHERE provider = 'razorpay' ORDER BY id DESC LIMIT 1");
            $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : null;
            $rawKeySecret = Encryption::decrypt($row['key_secret'] ?? '');
        }

        $gateway = new RazorpayGateway($keyId, $rawKeySecret);
        $result = $gateway->testConnection();

        echo json_encode($result);
        exit;
    }

    public function testShiprocket(): void {
        header('Content-Type: application/json');
        if (!Auth::hasPermission('settings.view')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $email = trim((string)$this->request->input('email', ''));
        $rawPassword = trim((string)$this->request->input('password', ''));

        if (str_contains($rawPassword, '•')) {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT password FROM shipping_settings WHERE provider = 'shiprocket' ORDER BY id DESC LIMIT 1");
            $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : null;
            $rawPassword = Encryption::decrypt($row['password'] ?? '');
        }

        $provider = new ShiprocketProvider($email, $rawPassword);
        $result = $provider->testConnection();

        echo json_encode($result);
        exit;
    }

    public function revealSecrets(): void {
        header('Content-Type: application/json');
        if (!Auth::hasPermission('settings.view')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $adminPassword = (string)$this->request->input('admin_password', '');
        $currentAdmin = Auth::user();

        if (empty($adminPassword) || empty($currentAdmin['password']) || !password_verify($adminPassword, $currentAdmin['password'])) {
            echo json_encode(['success' => false, 'message' => 'Incorrect Admin Password. Permission denied.']);
            exit;
        }

        $db = Database::getInstance();

        $stmtPg = $db->query("SELECT key_secret, webhook_secret FROM payment_gateway_settings WHERE provider = 'razorpay' ORDER BY id DESC LIMIT 1");
        $pg = $stmtPg ? $stmtPg->fetch(\PDO::FETCH_ASSOC) : null;

        $stmtSp = $db->query("SELECT password FROM shipping_settings WHERE provider = 'shiprocket' ORDER BY id DESC LIMIT 1");
        $sp = $stmtSp ? $stmtSp->fetch(\PDO::FETCH_ASSOC) : null;

        admin_audit_log('Reveal Secrets', "Admin unmasked API secret keys in settings panel");

        echo json_encode([
            'success' => true,
            'key_secret' => Encryption::decrypt($pg['key_secret'] ?? ''),
            'webhook_secret' => Encryption::decrypt($pg['webhook_secret'] ?? ''),
            'shiprocket_password' => Encryption::decrypt($sp['password'] ?? '')
        ]);
        exit;
    }
}
