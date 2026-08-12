<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use App\Services\NotificationService;

class WholesaleController {

    public function submit() {
        $response = new Response();

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $quantity = trim($_POST['quantity'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($phone) || empty($email)) {
            return $response->json([
                'status' => 'error',
                'message' => 'Please fill in all required fields (Name, Phone, Email).'
            ], 400);
        }

        // Save to database table if missing or create on the fly
        try {
            $db = Database::getInstance();
            $db->exec("CREATE TABLE IF NOT EXISTS wholesale_inquiries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(191) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                email VARCHAR(191) NOT NULL,
                company VARCHAR(191) NULL,
                quantity VARCHAR(100) NULL,
                message TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $stmt = $db->prepare("INSERT INTO wholesale_inquiries (name, phone, email, company, quantity, message) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $phone, $email, $company, $quantity, $message]);
        } catch (\Throwable $e) {
            error_log("Wholesale inquiry DB notice: " . $e->getMessage());
        }

        // Email Notification to Admin
        $adminEmail = NotificationService::getSetting('admin_email', 'sales@mudsor.com');
        if (empty($adminEmail)) {
            $adminEmail = 'sales@mudsor.com';
        }

        $subject = "New Wholesale Inquiry from " . $name . " - Mudsor";
        $emailBody = "
        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
            <h2 style='color: #A8111C;'>New Wholesale Inquiry — Mudsor EV Accessories</h2>
            <p><strong>Client Name:</strong> " . htmlspecialchars($name) . "</p>
            <p><strong>Phone / WhatsApp:</strong> " . htmlspecialchars($phone) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Business / Shop Name:</strong> " . htmlspecialchars($company ?: 'N/A') . "</p>
            <p><strong>Estimated Order Quantity:</strong> " . htmlspecialchars($quantity ?: 'N/A') . "</p>
            <p><strong>Message / Product Details:</strong></p>
            <blockquote style='background: #f9f9f9; padding: 12px; border-left: 4px solid #A8111C;'>
                " . nl2br(htmlspecialchars($message ?: 'No additional message provided.')) . "
            </blockquote>
            <hr style='border: none; border-top: 1px solid #eee; margin-top: 20px;'>
            <p style='font-size: 11px; color: #777;'>Submitted from Mudsor Wholesale Inquiry Form on " . date('Y-m-d H:i:s') . "</p>
        </div>";

        NotificationService::sendEmail('wholesale_inquiry', $adminEmail, $subject, $emailBody);

        return $response->json([
            'status' => 'success',
            'message' => 'Thank you! Your wholesale inquiry has been submitted successfully. Our sales team will get in touch with you shortly.'
        ]);
    }
}
