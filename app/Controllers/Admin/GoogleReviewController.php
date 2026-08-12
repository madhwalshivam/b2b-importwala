<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\GoogleReview;

class GoogleReviewController extends Controller {

    protected GoogleReview $reviewModel;

    public function __construct() {
        parent::__construct();
        $this->reviewModel = new GoogleReview();
    }

    public function index(): string {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
        }

        $reviews = $this->reviewModel->getActiveReviews();

        return $this->render('admin/google_reviews/index', [
            'reviews' => $reviews
        ]);
    }

    public function store(): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
        }

        $customerName = trim($this->request->input('customer_name', ''));
        $rating       = (int)$this->request->input('rating', 5);
        $reviewText   = trim($this->request->input('review_text', ''));
        $reviewDate   = $this->request->input('review_date', date('Y-m-d'));
        $isVerified   = (int)$this->request->input('is_verified', 1);
        $displayOrder = (int)$this->request->input('display_order', 0);

        $photoPath = '';

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/uploads/reviews/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = 'rev_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['photo']['name']);
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $fileName)) {
                $photoPath = '/uploads/reviews/' . $fileName;
            }
        }

        if (!empty($customerName) && !empty($reviewText)) {
            $this->reviewModel->insert([
                'customer_name' => $customerName,
                'photo_path'    => $photoPath,
                'rating'        => min(5, max(1, $rating)),
                'review_text'   => $reviewText,
                'review_date'   => $reviewDate ?: date('Y-m-d'),
                'is_verified'   => $isVerified,
                'display_order' => $displayOrder
            ]);
            $this->setFlash('success', 'Google Review added successfully!');
        } else {
            $this->setFlash('error', 'Customer Name and Review Text are required.');
        }

        $this->redirect(url('admin/google-reviews'));
    }

    public function update(int $id): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
        }

        $review = $this->reviewModel->find($id);
        if (!$review) {
            $this->setFlash('error', 'Review not found.');
            $this->redirect(url('admin/google-reviews'));
        }

        $customerName = trim($this->request->input('customer_name', $review['customer_name']));
        $rating       = (int)$this->request->input('rating', $review['rating']);
        $reviewText   = trim($this->request->input('review_text', $review['review_text']));
        $reviewDate   = $this->request->input('review_date', $review['review_date']);
        $isVerified   = (int)$this->request->input('is_verified', $review['is_verified']);
        $displayOrder = (int)$this->request->input('display_order', $review['display_order']);

        $photoPath = $review['photo_path'];

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/uploads/reviews/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = 'rev_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['photo']['name']);
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $fileName)) {
                $photoPath = '/uploads/reviews/' . $fileName;
            }
        }

        $this->reviewModel->update($id, [
            'customer_name' => $customerName,
            'photo_path'    => $photoPath,
            'rating'        => min(5, max(1, $rating)),
            'review_text'   => $reviewText,
            'review_date'   => $reviewDate ?: date('Y-m-d'),
            'is_verified'   => $isVerified,
            'display_order' => $displayOrder
        ]);

        $this->setFlash('success', 'Google Review updated successfully!');
        $this->redirect(url('admin/google-reviews'));
    }

    public function delete(int $id): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
        }

        $this->reviewModel->delete($id);
        $this->setFlash('success', 'Google Review removed successfully!');
        $this->redirect(url('admin/google-reviews'));
    }
}
