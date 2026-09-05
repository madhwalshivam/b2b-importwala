<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Auth;

class WishlistController extends BaseController
{
    private function getSessionId(): string
    {
        return get_current_session_id();
    }

    private function getUserId(): ?int
    {
        return get_current_user_id();
    }

    public function index(): void
    {
        $db = Database::getInstance();
        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        if ($userId) {
            $stmt = $db->prepare("SELECT p.*, w.id as wishlist_row_id, w.created_at as added_at FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.id DESC");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("SELECT p.*, w.id as wishlist_row_id, w.created_at as added_at FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.session_id = ? ORDER BY w.id DESC");
            $stmt->execute([$sessionId]);
        }
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->renderView('web/wishlist', [
            'wishlistItems' => $products,
            'products'      => $products,
            'wishlistCount' => count($products)
        ]);
    }

    public function toggle(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        $db = Database::getInstance();

        $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            return;
        }

        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        if ($userId) {
            $stmt = $db->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM wishlist WHERE session_id = ? AND product_id = ?");
            $stmt->execute([$sessionId, $productId]);
        }
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($existing) {
            $del = $db->prepare("DELETE FROM wishlist WHERE id = ?");
            $del->execute([$existing['id']]);
            $isSaved = false;
            $msg = 'Removed from wishlist';
        } else {
            $ins = $db->prepare("INSERT INTO wishlist (user_id, session_id, product_id, created_at) VALUES (?, ?, ?, NOW())");
            $ins->execute([$userId, $sessionId, $productId]);
            $isSaved = true;
            $msg = 'Added to wishlist';
        }

        $count = $this->getWishlistCount($userId, $sessionId);

        echo json_encode([
            'success' => true,
            'saved'   => $isSaved,
            'message' => $msg,
            'count'   => $count
        ]);
    }

    public function status(): void
    {
        header('Content-Type: application/json');
        $db = Database::getInstance();
        $productId = (int)($_GET['product_id'] ?? 0);

        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        $isSaved = false;
        if ($productId) {
            if ($userId) {
                $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $productId]);
            } else {
                $stmt = $db->prepare("SELECT id FROM wishlist WHERE session_id = ? AND product_id = ?");
                $stmt->execute([$sessionId, $productId]);
            }
            $isSaved = (bool)$stmt->fetchColumn();
        }

        $count = $this->getWishlistCount($userId, $sessionId);

        echo json_encode([
            'success' => true,
            'saved'   => $isSaved,
            'count'   => $count
        ]);
    }

    private function getWishlistCount(?int $userId, string $sessionId): int
    {
        $db = Database::getInstance();
        if ($userId) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE session_id = ?");
            $stmt->execute([$sessionId]);
        }
        return (int)$stmt->fetchColumn();
    }
}
