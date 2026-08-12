<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ReviewController extends Controller {

    /**
     * Storefront Customer Review Submission
     */
    public function submitStorefront() {
        $productId    = (int)($_POST['product_id'] ?? 0);
        $customerName = trim($_POST['customer_name'] ?? '');
        $rating       = (int)($_POST['rating'] ?? 5);
        $title        = trim($_POST['title'] ?? '');
        $comment      = trim($_POST['comment'] ?? '');

        if (!$productId || empty($customerName) || empty($comment)) {
            $_SESSION['flash_error'] = "Please fill in all required fields (Name, Rating, Comment).";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? url('/')));
            exit;
        }

        $rating = max(1, min(5, $rating));

        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO reviews (product_id, customer_id, customer_name, rating, title, comment, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'approved', NOW())");
        $stmt->execute([
            $productId,
            $_SESSION['user_id'] ?? null,
            $customerName,
            $rating,
            $title,
            $comment
        ]);

        // Recalculate product's average rating and review count
        self::recalculateProductRating($productId);

        $_SESSION['flash_success'] = "Thank you! Your review has been published successfully.";
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? url('/')));
        exit;
    }

    /**
     * Recalculate rating_avg and review_count in products table
     */
    public static function recalculateProductRating(int $productId) {
        if (!$productId) return;

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_cnt FROM reviews WHERE product_id = ? AND status = 'approved'");
        $stmt->execute([$productId]);
        $res = $stmt->fetch();

        $reviewCount = (int)($res['review_cnt'] ?? 0);
        $avgRating   = $reviewCount > 0 ? round((float)$res['avg_rating'], 1) : 0;

        $stmtUpd = $db->prepare("UPDATE products SET rating_avg = ?, review_count = ? WHERE id = ?");
        $stmtUpd->execute([$avgRating, $reviewCount, $productId]);
    }
}
