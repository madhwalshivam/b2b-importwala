<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Controllers\ReviewController as BaseReviewController;

class ReviewController extends Controller {

    /**
     * Admin Review List & Manage View
     */
    public function index() {
        $db = Database::getInstance();

        // Fetch all reviews with product info
        $stmt = $db->query("
            SELECT r.*, p.name as product_name, p.slug as product_slug, p.main_image
            FROM reviews r
            LEFT JOIN products p ON r.product_id = p.id
            ORDER BY r.id DESC
        ");
        $reviews = $stmt->fetchAll();

        // Fetch all active products for the Add Review Modal
        $stmtProducts = $db->query("SELECT id, name, sku FROM products WHERE status = 'active' ORDER BY name ASC");
        $products = $stmtProducts->fetchAll();

        return $this->render('admin/reviews/index', [
            'reviews'  => $reviews,
            'products' => $products
        ]);
    }

    /**
     * Admin Manually Add Review
     */
    public function store() {
        $productId    = (int)($_POST['product_id'] ?? 0);
        $customerName = trim($_POST['customer_name'] ?? '');
        $rating       = (int)($_POST['rating'] ?? 5);
        $title        = trim($_POST['title'] ?? '');
        $comment      = trim($_POST['comment'] ?? '');
        $status       = trim($_POST['status'] ?? 'approved');

        if (!$productId || empty($customerName) || empty($comment)) {
            $_SESSION['flash_error'] = "Please select a product and fill in customer name and comment.";
            header("Location: " . url('admin/reviews'));
            exit;
        }

        $rating = max(1, min(5, $rating));

        $isVerified = isset($_POST['is_verified']) ? 1 : 0;

        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO reviews (product_id, customer_name, rating, title, comment, status, is_verified, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$productId, $customerName, $rating, $title, $comment, $status, $isVerified]);

        // Recalculate product rating_avg and review_count
        BaseReviewController::recalculateProductRating($productId);

        $_SESSION['flash_success'] = "Review added successfully!";
        header("Location: " . url('admin/reviews'));
        exit;
    }

    /**
     * Admin Delete Review
     */
    public function delete($id) {
        $reviewId = (int)$id;
        if (!$reviewId) {
            header("Location: " . url('admin/reviews'));
            exit;
        }

        $db = Database::getInstance();
        
        // Find product_id first
        $stmtFind = $db->prepare("SELECT product_id FROM reviews WHERE id = ?");
        $stmtFind->execute([$reviewId]);
        $rev = $stmtFind->fetch();

        if ($rev) {
            $productId = (int)$rev['product_id'];

            // Delete review
            $stmtDel = $db->prepare("DELETE FROM reviews WHERE id = ?");
            $stmtDel->execute([$reviewId]);

            // Recalculate product rating
            BaseReviewController::recalculateProductRating($productId);

            $_SESSION['flash_success'] = "Review deleted successfully and product rating recalculated!";
        }

        header("Location: " . url('admin/reviews'));
        exit;
    }

    /**
     * Admin Update Status
     */
    public function updateStatus($id) {
        $reviewId = (int)$id;
        $status   = trim($_POST['status'] ?? 'approved');

        $db = Database::getInstance();
        $stmtFind = $db->prepare("SELECT product_id FROM reviews WHERE id = ?");
        $stmtFind->execute([$reviewId]);
        $rev = $stmtFind->fetch();

        if ($rev) {
            $productId = (int)$rev['product_id'];

            $stmtUpd = $db->prepare("UPDATE reviews SET status = ? WHERE id = ?");
            $stmtUpd->execute([$status, $reviewId]);

            BaseReviewController::recalculateProductRating($productId);

            $_SESSION['flash_success'] = "Review status updated to {$status}!";
        }

        header("Location: " . url('admin/reviews'));
        exit;
    }

    /**
     * Admin Update Verified Status
     */
    public function updateVerified($id) {
        $reviewId = (int)$id;
        $isVerified = (int)$_POST['is_verified'];

        $db = Database::getInstance();
        $stmtUpd = $db->prepare("UPDATE reviews SET is_verified = ? WHERE id = ?");
        $stmtUpd->execute([$isVerified, $reviewId]);

        $_SESSION['flash_success'] = "Review verified status updated!";
        header("Location: " . url('admin/reviews'));
        exit;
    }
}
