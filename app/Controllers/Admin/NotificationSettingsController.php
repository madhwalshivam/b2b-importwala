<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Services\NotificationService;

class NotificationSettingsController extends Controller {

    public function index(): string {
        if (!Auth::hasPermission('settings.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $email = NotificationService::getSetting('notification_email', 'info@importwale.com');
        $whatsapp = NotificationService::getSetting('notification_whatsapp', '9217714452');
        $apiToken = NotificationService::getSetting('whatsapp_api_token', '');
        $phoneId = NotificationService::getSetting('whatsapp_phone_number_id', '');

        // Fetch recent notification logs
        try {
            $db = Database::getInstance();
            $logsStmt = $db->query("SELECT * FROM notification_log ORDER BY id DESC LIMIT 20");
            $logs = $logsStmt ? $logsStmt->fetchAll() : [];
        } catch (\Throwable $e) {
            $logs = [];
        }

        return $this->render('admin/notification_settings', [
            'notification_email' => $email,
            'notification_whatsapp' => $whatsapp,
            'whatsapp_api_token' => $apiToken,
            'whatsapp_phone_number_id' => $phoneId,
            'logs' => $logs
        ]);
    }

    public function update(): void {
        if (!Auth::hasPermission('settings.manage')) {
            $this->redirect(url('admin/notification-settings'));
        }

        $email = trim($this->request->input('notification_email', 'info@importwale.com'));
        $whatsapp = trim($this->request->input('notification_whatsapp', '9217714452'));
        $apiToken = trim($this->request->input('whatsapp_api_token', ''));
        $phoneId = trim($this->request->input('whatsapp_phone_number_id', ''));

        NotificationService::setSetting('notification_email', $email);
        NotificationService::setSetting('notification_whatsapp', $whatsapp);
        NotificationService::setSetting('whatsapp_api_token', $apiToken);
        NotificationService::setSetting('whatsapp_phone_number_id', $phoneId);

        activity_log('Update Settings', 'NotificationSettings', 0, "Updated email ({$email}) & WhatsApp ({$whatsapp}) settings");

        $this->setFlash('success', 'Notification settings updated successfully!');
        $this->redirect(url('admin/notification-settings'));
    }

    public function testSend(): void {
        if (!Auth::hasPermission('settings.manage')) {
            $this->redirect(url('admin/notification-settings'));
        }

        $channel = $this->request->input('channel', 'email');

        if ($channel === 'email') {
            $email = NotificationService::getSetting('notification_email', 'info@importwale.com');
            $sent = NotificationService::sendEmail('Test Ping', $email, 'ImportWale Test Email', 'This is a test notification email from ImportWale Admin Panel.');
            $this->setFlash($sent ? 'success' : 'error', $sent ? "Test email sent to {$email}" : "Email failed to send. Check server logs.");
        } else {
            $phone = NotificationService::getSetting('notification_whatsapp', '9217714452');
            $sent = NotificationService::sendWhatsApp('Test Ping', $phone, "🚀 ImportWale Admin Test WhatsApp alert to {$phone}!");
            $this->setFlash('success', "Test WhatsApp trigger executed for {$phone}. Check log below.");
        }

        $this->redirect(url('admin/notification-settings'));
    }
}
