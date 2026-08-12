<?php
namespace App\Services;

use App\Core\Database;

class NotificationService {
    /**
     * Get site setting by key
     */
    public static function getSetting(string $key, string $default = ''): string {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return ($val !== false && $val !== null) ? (string)$val : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Update site setting by key
     */
    public static function setSetting(string $key, string $value): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }

    /**
     * Log notification attempt
     */
    public static function logNotification(string $eventType, string $channel, string $recipient, string $status, ?string $errorMessage = null): void {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO notification_log (event_type, channel, recipient, status, error_message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$eventType, $channel, $recipient, $status, $errorMessage]);
        } catch (\Exception $e) {
            error_log("Failed to log notification: " . $e->getMessage());
        }
    }

    /**
     * Send Email Notification to Admin
     */
    public static function sendEmail(string $eventType, string $toEmail, string $subject, string $messageBody): bool {
        if (empty($toEmail)) return false;

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Mudsor Notifications <no-reply@mudsor.com>" . "\r\n";

        try {
            $success = @mail($toEmail, $subject, $messageBody, $headers);
            if ($success) {
                self::logNotification($eventType, 'email', $toEmail, 'sent');
                return true;
            } else {
                self::logNotification($eventType, 'email', $toEmail, 'failed', 'PHP mail() function returned false');
                return false;
            }
        } catch (\Exception $e) {
            self::logNotification($eventType, 'email', $toEmail, 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Send WhatsApp Notification via Cloud API / Webhook
     */
    public static function sendWhatsApp(string $eventType, string $toPhone, string $messageText): bool {
        if (empty($toPhone)) return false;

        $apiToken = self::getSetting('whatsapp_api_token', '');
        $phoneId = self::getSetting('whatsapp_phone_number_id', '');

        try {
            // Clean phone number format
            $cleanPhone = preg_replace('/[^0-9]/', '', $toPhone);
            if (strlen($cleanPhone) === 10) {
                $cleanPhone = '91' . $cleanPhone; // Default to India prefix if 10 digits
            }

            if (!empty($apiToken) && !empty($phoneId)) {
                // Meta WhatsApp Cloud API endpoint
                $url = "https://graph.facebook.com/v18.0/{$phoneId}/messages";
                $data = [
                    'messaging_product' => 'whatsapp',
                    'to' => $cleanPhone,
                    'type' => 'text',
                    'text' => ['body' => $messageText]
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $apiToken,
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode >= 200 && $httpCode < 300) {
                    self::logNotification($eventType, 'whatsapp', $cleanPhone, 'sent');
                    return true;
                } else {
                    self::logNotification($eventType, 'whatsapp', $cleanPhone, 'failed', "API HTTP {$httpCode}: {$response}");
                    return false;
                }
            } else {
                // Simulating/Logging local delivery if API token not yet configured
                self::logNotification($eventType, 'whatsapp', $cleanPhone, 'simulated', 'Message prepared for WhatsApp API (Token pending setup in Admin)');
                return true;
            }
        } catch (\Exception $e) {
            self::logNotification($eventType, 'whatsapp', $toPhone, 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Main Trigger Method for Order / Wishlist / Cart events
     */
    public static function trigger(string $eventType, array $payload): void {
        $toEmail = self::getSetting('notification_email', 'mudsorinfo@gmail.com');
        $toWhatsApp = self::getSetting('notification_whatsapp', '9217714452');

        $userName = $payload['user_name'] ?? 'Guest User';
        $userEmail = $payload['user_email'] ?? 'N/A';
        $userPhone = $payload['user_phone'] ?? 'N/A';
        $productTitle = $payload['product_name'] ?? 'N/A';
        $qty = $payload['quantity'] ?? 1;
        $price = $payload['price'] ?? '0.00';
        $time = date('Y-m-d H:i:s');

        $subject = "Mudsor Alert: [{$eventType}] - {$userName}";

        $emailBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <h2 style='color: #dc2626;'>Mudsor E-Commerce Event Alert</h2>
                <p><strong>Event:</strong> {$eventType}</p>
                <p><strong>User:</strong> {$userName} ({$userEmail} / Phone: {$userPhone})</p>
                <p><strong>Product:</strong> {$productTitle}</p>
                <p><strong>Quantity:</strong> {$qty}</p>
                <p><strong>Amount:</strong> ₹{$price}</p>
                <p><strong>Timestamp:</strong> {$time}</p>
            </div>
        ";

        $waText = "🚀 Mudsor Alert: [{$eventType}]\nUser: {$userName} ({$userPhone})\nProduct: {$productTitle}\nQty: {$qty} | ₹{$price}\nTime: {$time}";

        // Wrap in try-catch so failure NEVER breaks caller execution
        try {
            self::sendEmail($eventType, $toEmail, $subject, $emailBody);
        } catch (\Exception $e) {}

        try {
            self::sendWhatsApp($eventType, $toWhatsApp, $waText);
        } catch (\Exception $e) {}
    }
}

/**
 * Global helper function per spec (send-whatsapp-notification.php style)
 */
function send_whatsapp_notification(string $eventType, string $phone, string $message): bool {
    return NotificationService::sendWhatsApp($eventType, $phone, $message);
}
