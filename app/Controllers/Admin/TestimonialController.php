<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Testimonial;

class TestimonialController extends Controller {

    protected Testimonial $testimonialModel;

    public function __construct() {
        parent::__construct();
        $this->testimonialModel = new Testimonial();
    }

    /**
     * Admin Testimonials Management View
     */
    public function index(): string {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $testimonials = $this->testimonialModel->getAllForAdmin();

        // Fetch active products for optional product linking in modal
        $db = Database::getInstance();
        $stmtProducts = $db->query("SELECT id, name, sku FROM products WHERE status = 'active' ORDER BY name ASC");
        $products = $stmtProducts->fetchAll();

        return $this->render('admin/testimonials/index', [
            'testimonials' => $testimonials,
            'products'     => $products
        ]);
    }

    /**
     * Store new Testimonial
     */
    public function store(): void {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $reviewerName = trim($this->request->input('reviewer_name', ''));
        $location     = trim($this->request->input('location', ''));
        $rating       = (int)$this->request->input('rating', 5);
        $reviewText   = trim($this->request->input('review_text', ''));
        $productId    = (int)$this->request->input('product_id', 0) ?: null;
        $displayOrder = (int)$this->request->input('display_order', 0);
        $isFeatured   = isset($_POST['is_featured']) ? 1 : 0;
        $status       = trim($this->request->input('status', 'active'));

        if (empty($reviewerName) || empty($reviewText)) {
            $this->setFlash('error', 'Reviewer Name and Review Text are required.');
            $this->redirect(url('admin/testimonials'));
            return;
        }

        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoPath = $this->handlePhotoUpload($_FILES['photo']);
        }

        $avatarColor = Testimonial::pickAvatarColor($reviewerName);

        $this->testimonialModel->insert([
            'reviewer_name' => $reviewerName,
            'location'      => $location ?: 'Verified Buyer',
            'rating'        => min(5, max(1, $rating)),
            'review_text'   => $reviewText,
            'photo_path'    => $photoPath,
            'avatar_color'  => $avatarColor,
            'product_id'    => $productId,
            'display_order' => $displayOrder,
            'is_featured'   => $isFeatured,
            'status'        => in_array($status, ['active', 'inactive']) ? $status : 'active'
        ]);

        $this->setFlash('success', 'Customer review added successfully!');
        $this->redirect(url('admin/testimonials'));
    }

    /**
     * Update Testimonial
     */
    public function update(int $id): void {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $testimonial = $this->testimonialModel->find($id);
        if (!$testimonial) {
            $this->setFlash('error', 'Review not found.');
            $this->redirect(url('admin/testimonials'));
            return;
        }

        $reviewerName = trim($this->request->input('reviewer_name', $testimonial['reviewer_name']));
        $location     = trim($this->request->input('location', $testimonial['location']));
        $rating       = (int)$this->request->input('rating', $testimonial['rating']);
        $reviewText   = trim($this->request->input('review_text', $testimonial['review_text']));
        $productId    = (int)$this->request->input('product_id', 0) ?: null;
        $displayOrder = (int)$this->request->input('display_order', $testimonial['display_order']);
        $isFeatured   = isset($_POST['is_featured']) ? 1 : 0;
        $status       = trim($this->request->input('status', $testimonial['status']));

        $photoPath = $testimonial['photo_path'];

        // If user checked remove photo
        if (isset($_POST['remove_photo']) && $_POST['remove_photo'] == '1') {
            if ($photoPath && file_exists(__DIR__ . '/../../../public' . $photoPath)) {
                @unlink(__DIR__ . '/../../../public' . $photoPath);
            }
            $photoPath = null;
        }

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            // Delete old photo if existed
            if ($photoPath && file_exists(__DIR__ . '/../../../public' . $photoPath)) {
                @unlink(__DIR__ . '/../../../public' . $photoPath);
            }
            $photoPath = $this->handlePhotoUpload($_FILES['photo']);
        }

        $avatarColor = Testimonial::pickAvatarColor($reviewerName);

        $this->testimonialModel->update($id, [
            'reviewer_name' => $reviewerName,
            'location'      => $location ?: 'Verified Buyer',
            'rating'        => min(5, max(1, $rating)),
            'review_text'   => $reviewText,
            'photo_path'    => $photoPath,
            'avatar_color'  => $avatarColor,
            'product_id'    => $productId,
            'display_order' => $displayOrder,
            'is_featured'   => $isFeatured,
            'status'        => in_array($status, ['active', 'inactive']) ? $status : 'active'
        ]);

        $this->setFlash('success', 'Customer review updated successfully!');
        $this->redirect(url('admin/testimonials'));
    }

    /**
     * Delete Testimonial
     */
    public function delete(int $id): void {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $testimonial = $this->testimonialModel->find($id);
        if ($testimonial) {
            if (!empty($testimonial['photo_path']) && file_exists(__DIR__ . '/../../../public' . $testimonial['photo_path'])) {
                @unlink(__DIR__ . '/../../../public' . $testimonial['photo_path']);
            }
            $this->testimonialModel->delete($id);
            $this->setFlash('success', 'Customer review deleted successfully!');
        }

        $this->redirect(url('admin/testimonials'));
    }

    /**
     * Toggle Active Status
     */
    public function toggleStatus(int $id): void {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $testimonial = $this->testimonialModel->find($id);
        if ($testimonial) {
            $newStatus = ($testimonial['status'] === 'active') ? 'inactive' : 'active';
            $this->testimonialModel->update($id, ['status' => $newStatus]);
            $this->setFlash('success', "Review status set to {$newStatus}.");
        }

        $this->redirect(url('admin/testimonials'));
    }

    /**
     * Toggle Featured Status
     */
    public function toggleFeatured(int $id): void {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $testimonial = $this->testimonialModel->find($id);
        if ($testimonial) {
            $newFeatured = $testimonial['is_featured'] ? 0 : 1;
            $this->testimonialModel->update($id, ['is_featured' => $newFeatured]);
            $msg = $newFeatured ? "Review is now featured on Homepage." : "Review removed from Homepage featured section.";
            $this->setFlash('success', $msg);
        }

        $this->redirect(url('admin/testimonials'));
    }

    /**
     * Helper for uploading reviewer image
     */
    private function handlePhotoUpload(array $file): ?string {
        $uploadDir = __DIR__ . '/../../../public/uploads/testimonials/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
        if (!in_array($ext, $allowed)) {
            return null;
        }

        $fileName = 'testi_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '/uploads/testimonials/' . $fileName;
        }

        return null;
    }
}
