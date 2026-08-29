<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;
use App\Models\Setting;

class WishlistController extends Controller {

    private function getSessionId(): string {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (empty($_SESSION['guest_wishlist_session_id'])) {
            $_SESSION['guest_wishlist_session_id'] = session_id() ?: ('guest_' . bin2hex(random_bytes(8)));
        }
        return $_SESSION['guest_wishlist_session_id'];
    }

    // Toggle Wishlist Item via AJAX
    public function toggle(): void {
        header('Content-Type: application/json');

        $productId = (int)$this->request->input('product_id', 0);
        if ($productId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
            exit;
        }

        $db = Database::getInstance();
        $settingModel = new Setting();
        $maxLimit = (int)($settingModel->get('wishlist_max_limit') ?? 100);
        if ($maxLimit <= 0) $maxLimit = 100;

        $userId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $sessionId = $this->getSessionId();

        // Check if item already in wishlist
        if ($userId) {
            $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $exists = $stmt->fetch();
        } else {
            $stmt = $db->prepare("SELECT id FROM wishlist WHERE session_id = ? AND product_id = ?");
            $stmt->execute([$sessionId, $productId]);
            $exists = $stmt->fetch();
        }

        if ($exists) {
            // Remove item
            if ($userId) {
                $del = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
                $del->execute([$userId, $productId]);
            } else {
                $del = $db->prepare("DELETE FROM wishlist WHERE session_id = ? AND product_id = ?");
                $del->execute([$sessionId, $productId]);
            }
            $status = 'removed';
            $message = 'Removed from wishlist';
        } else {
            // Check count against max limit before adding
            if ($userId) {
                $countStmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
                $countStmt->execute([$userId]);
            } else {
                $countStmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE session_id = ?");
                $countStmt->execute([$sessionId]);
            }
            $currentCount = (int)$countStmt->fetchColumn();

            if ($currentCount >= $maxLimit) {
                echo json_encode([
                    'status' => 'limit_reached',
                    'count' => $currentCount,
                    'max_limit' => $maxLimit,
                    'message' => "You can add a maximum of {$maxLimit} products to your wishlist."
                ]);
                exit;
            }

            // Add item
            if ($userId) {
                $ins = $db->prepare("INSERT INTO wishlist (user_id, session_id, product_id, created_at) VALUES (?, ?, ?, NOW())");
                $ins->execute([$userId, $sessionId, $productId]);
            } else {
                $ins = $db->prepare("INSERT INTO wishlist (user_id, session_id, product_id, created_at) VALUES (NULL, ?, ?, NOW())");
                $ins->execute([$sessionId, $productId]);
            }
            $status = 'added';
            $message = 'Added to wishlist';
        }

        // Fetch new count
        if ($userId) {
            $countStmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
            $countStmt->execute([$userId]);
        } else {
            $countStmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE session_id = ?");
            $countStmt->execute([$sessionId]);
        }
        $count = (int)$countStmt->fetchColumn();

        echo json_encode([
            'status' => $status,
            'count' => $count,
            'message' => $message
        ]);
        exit;
    }

    // Wishlist Page View (/wishlist)
    public function index(): string {
        $db = Database::getInstance();
        $settingModel = new Setting();
        
        $userId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $sessionId = $this->getSessionId();

        if ($userId) {
            $stmt = $db->prepare("
                SELECT p.*, w.id as wishlist_id, w.created_at as added_at 
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
            ");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("
                SELECT p.*, w.id as wishlist_id, w.created_at as added_at 
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                WHERE w.session_id = ?
                ORDER BY w.created_at DESC
            ");
            $stmt->execute([$sessionId]);
        }
        $products = $stmt->fetchAll() ?: [];

        // WhatsApp Configuration
        $whatsappNumber = preg_replace('/[^0-9]/', '', $settingModel->get('whatsapp_business_number') ?? '919217714452');
        $whatsappTemplate = $settingModel->get('whatsapp_wishlist_template') ?? "Hi, I am interested in wholesale pricing for the following wishlist items:\n\n{product_list}\n\nPlease provide a bulk quotation and delivery timeline.";

        // Build product list text
        $productListLines = [];
        foreach ($products as $idx => $prod) {
            $pName = $prod['name'] ?? $prod['title'] ?? 'Product #' . $prod['id'];
            $pUrl = url('product/' . ($prod['slug'] ?? $prod['id']));
            $productListLines[] = ($idx + 1) . ". " . $pName . " - " . $pUrl;
        }
        $productListStr = implode("\n", $productListLines);

        $finalMsg = str_replace('{product_list}', $productListStr, $whatsappTemplate);
        $whatsappShareUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode($finalMsg);

        $viewPath = dirname(__DIR__, 2) . '/views/web/wishlist.php';
        if (file_exists($viewPath)) {
            extract([
                'products' => $products,
                'whatsappShareUrl' => $whatsappShareUrl,
                'whatsappNumber' => $whatsappNumber,
                'maxLimit' => (int)($settingModel->get('wishlist_max_limit') ?? 100)
            ]);
            include $viewPath;
            return '';
        }

        return $this->render('web/wishlist', [
            'products' => $products,
            'whatsappShareUrl' => $whatsappShareUrl,
            'whatsappNumber' => $whatsappNumber,
            'maxLimit' => (int)($settingModel->get('wishlist_max_limit') ?? 100)
        ]);
    }
}
