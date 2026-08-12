<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;
use App\Services\NotificationService;

class WishlistController extends Controller {

    // Toggle Wishlist Item via AJAX (Supports both Logged-in & Guest users)
    public function toggle(): void {
        header('Content-Type: application/json');

        $productId = (int)$this->request->input('product_id', 0);
        if ($productId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
            exit;
        }

        // 1. Logged-in User Wishlist (Database)
        if (!empty($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $db = Database::getInstance();

            $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $exists = $stmt->fetch();

            if ($exists) {
                $del = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
                $del->execute([$userId, $productId]);
                $status = 'removed';
            } else {
                $ins = $db->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
                $ins->execute([$userId, $productId]);
                $status = 'added';

                $productModel = new Product();
                $product = $productModel->find($productId);
            }

            $countStmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
            $countStmt->execute([$userId]);
            $count = (int)$countStmt->fetchColumn();

        } else {
            // 2. Guest User Wishlist (Session-based)
            if (!isset($_SESSION['guest_wishlist']) || !is_array($_SESSION['guest_wishlist'])) {
                $_SESSION['guest_wishlist'] = [];
            }

            $key = array_search($productId, $_SESSION['guest_wishlist']);
            if ($key !== false) {
                unset($_SESSION['guest_wishlist'][$key]);
                $_SESSION['guest_wishlist'] = array_values($_SESSION['guest_wishlist']);
                $status = 'removed';
            } else {
                $_SESSION['guest_wishlist'][] = $productId;
                $status = 'added';
            }

            $count = count($_SESSION['guest_wishlist']);
        }

        echo json_encode([
            'status' => $status,
            'count' => $count,
            'message' => $status === 'added' ? 'Added to wishlist' : 'Removed from wishlist'
        ]);
        exit;
    }

    // Wishlist Page View (Supports both Logged-in and Guest Users)
    public function index(): string {
        $db = Database::getInstance();
        $products = [];

        if (!empty($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $stmt = $db->prepare("
                SELECT p.* FROM wishlist w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
            ");
            $stmt->execute([$userId]);
            $products = $stmt->fetchAll();
        } else {
            $guestWishlist = $_SESSION['guest_wishlist'] ?? [];
            if (!empty($guestWishlist)) {
                $inClause = implode(',', array_map('intval', $guestWishlist));
                $stmt = $db->query("SELECT * FROM products WHERE id IN ($inClause) ORDER BY id DESC");
                $products = $stmt->fetchAll();
            }
        }

        return $this->render('storefront/wishlist', [
            'products' => $products
        ]);
    }
}
