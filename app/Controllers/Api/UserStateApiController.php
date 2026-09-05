<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Core\Database;

class UserStateApiController extends BaseController
{
    public function getState(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        $db = Database::getInstance();
        $userId    = get_current_user_id();
        $sessionId = get_current_session_id();

        // 1. Wishlist Items & Count
        if ($userId) {
            $wStmt = $db->prepare("SELECT DISTINCT product_id FROM wishlist WHERE user_id = ?");
            $wStmt->execute([$userId]);
        } else {
            $wStmt = $db->prepare("SELECT DISTINCT product_id FROM wishlist WHERE session_id = ?");
            $wStmt->execute([$sessionId]);
        }
        $wishlistProductIds = array_map('intval', $wStmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);
        $wishlistCount = count($wishlistProductIds);

        // 2. Cart Items & Count
        if ($userId) {
            $cStmt = $db->prepare("SELECT DISTINCT product_id FROM cart_items WHERE user_id = ?");
            $cStmt->execute([$userId]);
            $cQtyStmt = $db->prepare("SELECT SUM(quantity) FROM cart_items WHERE user_id = ?");
            $cQtyStmt->execute([$userId]);
        } else {
            $cStmt = $db->prepare("SELECT DISTINCT product_id FROM cart_items WHERE session_id = ?");
            $cStmt->execute([$sessionId]);
            $cQtyStmt = $db->prepare("SELECT SUM(quantity) FROM cart_items WHERE session_id = ?");
            $cQtyStmt->execute([$sessionId]);
        }
        $cartProductIds = array_map('intval', $cStmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);
        $cartTotalQty = (int)($cQtyStmt->fetchColumn() ?: 0);

        echo json_encode([
            'success' => true,
            'logged_in' => $userId !== null,
            'wishlist' => [
                'count' => $wishlistCount,
                'product_ids' => $wishlistProductIds
            ],
            'cart' => [
                'count' => $cartTotalQty,
                'product_ids' => $cartProductIds
            ]
        ]);
    }
}
