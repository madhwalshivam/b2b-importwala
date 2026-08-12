<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HeartbeatController extends Controller {

    public function update(): void {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionId = session_id();
        if (empty($sessionId)) {
            echo json_encode(['status' => 'ignored']);
            exit;
        }

        $userId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO live_sessions (session_id, user_id, ip_address, user_agent, last_active)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), ip_address = VALUES(ip_address), last_active = NOW()
            ");
            $stmt->execute([$sessionId, $userId, $ip, substr($ua, 0, 255)]);

            // Sync cart_items table if items in session cart
            if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                $stmtCart = $db->prepare("
                    INSERT INTO cart_items (session_id, user_id, product_id, quantity, updated_at)
                    VALUES (?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), updated_at = NOW()
                ");
                foreach ($_SESSION['cart'] as $prodId => $item) {
                    $stmtCart->execute([$sessionId, $userId, (int)$prodId, (int)$item['quantity']]);
                }
            }

            echo json_encode(['status' => 'ok', 'online' => true]);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}
