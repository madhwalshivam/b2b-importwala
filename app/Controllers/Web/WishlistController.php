<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Auth;

class WishlistController extends BaseController
{
    private function getSessionId(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['cart_session_id'])) {
            $_SESSION['cart_session_id'] = 'cs_' . bin2hex(random_bytes(12));
        }
        return $_SESSION['cart_session_id'];
    }

    private function getUserId(): ?int
    {
        if (class_exists('\App\Core\Auth') && Auth::check()) {
            $u = Auth::user();
            return (int)($u['id'] ?? 0);
        }
        return null;
    }

    public function index(): void
    {
        $db = Database::getInstance();
        $userId    = $this->getUserId();
        $sessionId = $this->getSessionId();

        if ($userId) {
            $stmt = $db->prepare("SELECT w.*, p.name as product_name, p.slug as product_slug, p.main_image, p.price, p.base_price, p.sale_price, p.sku FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.id DESC");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("SELECT w.*, p.name as product_name, p.slug as product_slug, p.main_image, p.price, p.base_price, p.sale_price, p.sku FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.session_id = ? ORDER BY w.id DESC");
            $stmt->execute([$sessionId]);
        }
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $items = [];
        foreach ($rows as $r) {
            $img = !empty($r['main_image']) ? asset($r['main_image']) : asset('assets/images/placeholder.jpg');
            $items[] = [
                'id'         => (int)$r['id'],
                'product_id' => (int)$r['product_id'],
                'name'       => $r['product_name'],
                'slug'       => $r['product_slug'],
                'sku'        => $r['sku'],
                'image'      => $img,
                'price'      => (float)($r['base_price'] ?: ($r['sale_price'] ?: $r['price'])),
            ];
        }

        $this->renderView('web/wishlist', [
            'wishlistItems' => $items,
            'wishlistCount' => count($items)
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
