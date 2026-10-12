<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Category;
use App\Services\GstCalculator;
use App\Services\GstService;

class CategoryController extends Controller {
    protected Category $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->categoryModel = new Category();
    }

    /**
     * Display categories listing page
     */
    public function index(): string {
        if (!Auth::hasPermission('categories.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $categories = $this->categoryModel->getAllWithDetails();

        return $this->render('admin/categories/index', [
            'categories' => $categories
        ]);
    }

    /**
     * Get category details for AJAX viewing or editing modal
     */
    public function get(int $id): void {
        header('Content-Type: application/json');
        
        if (!Auth::hasPermission('categories.view')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            exit;
        }

        $category = $this->categoryModel->findWithDetails($id);
        if (!$category) {
            echo json_encode(['success' => false, 'message' => 'Category not found.']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'category' => $category
        ]);
        exit;
    }

    /**
     * Store new category
     */
    public function store(): void {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        try {
            if (!Auth::hasPermission('categories.add')) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Permission denied.']);
                    exit;
                }
                $this->setFlash('danger', 'Permission denied.');
                $this->redirect(url('admin/categories'));
                return;
            }

            $name = trim($this->request->input('name', ''));
            if (empty($name)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Category name is required.']);
                    exit;
                }
                $this->setFlash('danger', 'Category name is required.');
                $this->redirect(url('admin/categories'));
                return;
            }

            // Slug handling
            $slugInput = trim($this->request->input('slug', ''));
            $slug = $slugInput ? slugify($slugInput) : slugify($name);
            
            // Ensure unique slug
            $baseSlug = $slug;
            $counter = 1;
            while ($this->categoryModel->slugExists($slug)) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Image Handling (File upload or Image URL)
            $imageUrl = trim($this->request->input('image_url', ''));
            if (!empty($_FILES['image_file']['name'])) {
                $uploaded = $this->uploadCategoryImage($_FILES['image_file']);
                if ($uploaded) {
                    $imageUrl = $uploaded;
                }
            } elseif (!empty($_FILES['image']['name'])) {
                $uploaded = $this->uploadCategoryImage($_FILES['image']);
                if ($uploaded) {
                    $imageUrl = $uploaded;
                }
            }

            // Parent Category
            $parentId = $this->request->input('parent_id');
            $parentId = ($parentId !== '' && $parentId !== null && (int)$parentId > 0) ? (int)$parentId : null;

            $gst = $this->gstFieldsFromRequest();
            $id = $this->categoryModel->insert([
                'name'             => $name,
                'slug'             => $slug,
                'icon_type'        => 'custom',
                'icon'             => $imageUrl ?: null,
                'custom_icon'      => $imageUrl ?: null,
                'image'            => $imageUrl ?: null,
                'parent_id'        => $parentId,
                'description'      => trim($this->request->input('description', '')),
                'meta_title'       => trim($this->request->input('meta_title', '')),
                'meta_description' => trim($this->request->input('meta_description', '')),
                'is_featured'      => isset($_POST['is_featured']) && $_POST['is_featured'] == '1' ? 1 : 0,
                'sort_order'       => (int)$this->request->input('sort_order', 0),
                'status'           => $this->request->input('status', 'active'),
                'hsn_code'         => $gst['hsn_code'],
                'gst_rate'         => $gst['gst_rate'],
                'is_gst_active'    => $gst['is_gst_active'],
            ]);

            (new GstService())->audit('category', (int) $id, null, $gst['hsn_code'], null, $gst['gst_rate'], $this->adminId());
            activity_log('Create Category', 'Categories', $id, "Added category: {$name}");

            try {
                \App\Infrastructure\Cache\CacheManager::getInstance()->flush();
            } catch (\Throwable $e) {}

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Category created successfully.', 'id' => $id]);
                exit;
            }

            $this->setFlash('success', 'Category created successfully.');
            $this->redirect(url('admin/categories'));
        } catch (\Throwable $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error creating category: ' . $e->getMessage()]);
                exit;
            }
            $this->setFlash('danger', 'Error creating category: ' . $e->getMessage());
            $this->redirect(url('admin/categories'));
        }
    }

    /**
     * Update existing category
     */
    public function update(int $id): void {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        try {
            if (!Auth::hasPermission('categories.edit')) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Permission denied.']);
                    exit;
                }
                $this->setFlash('danger', 'Permission denied.');
                $this->redirect(url('admin/categories'));
                return;
            }

            $existing = $this->categoryModel->find($id);
            if (!$existing) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Category not found.']);
                    exit;
                }
                $this->setFlash('danger', 'Category not found.');
                $this->redirect(url('admin/categories'));
                return;
            }

            $name = trim($this->request->input('name', ''));
            if (empty($name)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Category name is required.']);
                    exit;
                }
                $this->setFlash('danger', 'Category name is required.');
                $this->redirect(url('admin/categories'));
                return;
            }

            // Slug handling
            $slugInput = trim($this->request->input('slug', ''));
            $slug = $slugInput ? slugify($slugInput) : slugify($name);

            // Ensure unique slug except current category
            $baseSlug = $slug;
            $counter = 1;
            while ($this->categoryModel->slugExists($slug, $id)) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Parent Category (Prevent self-parenting)
            $parentId = $this->request->input('parent_id');
            $parentId = ($parentId !== '' && $parentId !== null && (int)$parentId > 0) ? (int)$parentId : null;
            if ($parentId === $id) {
                $parentId = null;
            }

            // Image Handling
            $imageUrl = trim($this->request->input('image_url', $existing['image'] ?? $existing['custom_icon'] ?? ''));

            if (!empty($_FILES['image_file']['name'])) {
                $uploaded = $this->uploadCategoryImage($_FILES['image_file']);
                if ($uploaded) {
                    $imageUrl = $uploaded;
                }
            } elseif (!empty($_FILES['image']['name'])) {
                $uploaded = $this->uploadCategoryImage($_FILES['image']);
                if ($uploaded) {
                    $imageUrl = $uploaded;
                }
            }

            $gst = $this->gstFieldsFromRequest();
            $data = [
                'name'             => $name,
                'slug'             => $slug,
                'icon_type'        => 'custom',
                'icon'             => $imageUrl ?: null,
                'custom_icon'      => $imageUrl ?: null,
                'image'            => $imageUrl ?: null,
                'parent_id'        => $parentId,
                'description'      => trim($this->request->input('description', '')),
                'meta_title'       => trim($this->request->input('meta_title', '')),
                'meta_description' => trim($this->request->input('meta_description', '')),
                'is_featured'      => isset($_POST['is_featured']) && $_POST['is_featured'] == '1' ? 1 : 0,
                'sort_order'       => (int)$this->request->input('sort_order', 0),
                'status'           => $this->request->input('status', 'active'),
                'hsn_code'         => $gst['hsn_code'],
                'gst_rate'         => $gst['gst_rate'],
                'is_gst_active'    => $gst['is_gst_active'],
            ];

            $this->categoryModel->update($id, $data);
            (new GstService())->audit('category', $id, $existing['hsn_code'] ?? null, $gst['hsn_code'], $existing['gst_rate'] ?? null, $gst['gst_rate'], $this->adminId());

            activity_log('Update Category', 'Categories', $id, "Updated category: {$name}");

            try {
                \App\Infrastructure\Cache\CacheManager::getInstance()->flush();
            } catch (\Throwable $e) {}

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Category updated successfully.']);
                exit;
            }

            $this->setFlash('success', 'Category updated successfully.');
            $this->redirect(url('admin/categories'));
        } catch (\Throwable $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
                exit;
            }
            $this->setFlash('danger', 'Error updating category: ' . $e->getMessage());
            $this->redirect(url('admin/categories'));
        }
    }

    /**
     * Delete category
     */
    public function delete(int $id): void {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        if (!Auth::hasPermission('categories.delete')) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Permission denied.']);
                exit;
            }
            $this->setFlash('danger', 'Permission denied.');
            $this->redirect(url('admin/categories'));
            return;
        }

        $category = $this->categoryModel->find($id);
        if (!$category) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Category not found.']);
                exit;
            }
            $this->setFlash('danger', 'Category not found.');
            $this->redirect(url('admin/categories'));
            return;
        }

        // Check if category has linked products
        $productCount = $this->categoryModel->getProductCount($id);
        if ($productCount > 0) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'has_products' => true,
                    'product_count' => $productCount,
                    'message' => "Category has {$productCount} linked product(s). You must reassign or remove products before deleting."
                ]);
                exit;
            }
            $this->setFlash('danger', "Category has {$productCount} linked product(s). Please reassign them before deleting.");
            $this->redirect(url('admin/categories'));
            return;
        }

        $this->categoryModel->delete($id);
        activity_log('Delete Category', 'Categories', $id, "Deleted category: {$category['name']}");

        try {
            \App\Infrastructure\Cache\CacheManager::getInstance()->flush();
        } catch (\Throwable $e) {}

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully.']);
            exit;
        }

        $this->setFlash('success', 'Category deleted successfully.');
        $this->redirect(url('admin/categories'));
    }

    /**
     * Reassign products to target category and delete original category
     */
    public function reassignAndDelete(int $id): void {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        if (!Auth::hasPermission('categories.delete')) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Permission denied.']);
                exit;
            }
            $this->setFlash('danger', 'Permission denied.');
            $this->redirect(url('admin/categories'));
            return;
        }

        $category = $this->categoryModel->find($id);
        if (!$category) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Category not found.']);
                exit;
            }
            $this->setFlash('danger', 'Category not found.');
            $this->redirect(url('admin/categories'));
            return;
        }

        $targetCategoryId = (int)$this->request->input('target_category_id', 0);
        if ($targetCategoryId === $id) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cannot reassign products to the category being deleted.']);
                exit;
            }
            $this->setFlash('danger', 'Target category cannot be the category being deleted.');
            $this->redirect(url('admin/categories'));
            return;
        }

        // Verify target category exists if provided
        if ($targetCategoryId > 0) {
            $targetCat = $this->categoryModel->find($targetCategoryId);
            if (!$targetCat) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Target category not found.']);
                    exit;
                }
                $this->setFlash('danger', 'Target category not found.');
                $this->redirect(url('admin/categories'));
                return;
            }
        }

        // Reassign products
        $this->categoryModel->reassignProducts($id, $targetCategoryId > 0 ? $targetCategoryId : null);

        // Delete category
        $this->categoryModel->delete($id);
        activity_log('Delete Category with Reassign', 'Categories', $id, "Reassigned products and deleted category: {$category['name']}");

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Products reassigned and category deleted successfully.']);
            exit;
        }

        $this->setFlash('success', 'Products reassigned and category deleted successfully.');
        $this->redirect(url('admin/categories'));
    }

    public function updateGst(int $id): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if (!Auth::hasPermission('categories.edit')) {
            $this->gstFail($isAjax, 'Permission denied.');
            return;
        }
        $existing = $this->categoryModel->find($id);
        if (!$existing) {
            $this->gstFail($isAjax, 'Category not found.');
            return;
        }
        try {
            $gst = $this->gstFieldsFromRequest();
        } catch (\InvalidArgumentException $e) {
            $this->gstFail($isAjax, $e->getMessage());
            return;
        }
        $this->categoryModel->update($id, $gst);
        (new GstService())->audit('category', $id, $existing['hsn_code'] ?? null, $gst['hsn_code'], $existing['gst_rate'] ?? null, $gst['gst_rate'], $this->adminId());
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'GST details saved.']);
            exit;
        }
        $this->setFlash('success', 'GST details saved.');
        $this->redirect(url('admin/categories'));
    }

    private function gstFieldsFromRequest(): array
    {
        return [
            'hsn_code' => GstCalculator::parseHsn($this->request->input('hsn_code', '')),
            'gst_rate' => GstCalculator::parseRate($this->request->input('gst_rate', '18'), $this->request->input('gst_rate_custom', '')),
            'is_gst_active' => !array_key_exists('is_gst_active', $_POST) || (string) $_POST['is_gst_active'] === '1' ? 1 : 0,
        ];
    }

    private function gstFail(bool $isAjax, string $message): void
    {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        $this->setFlash('danger', $message);
        $this->redirect(url('admin/categories'));
    }

    private function adminId(): ?int
    {
        $id = $_SESSION['admin_user_id'] ?? null;
        return $id !== null && $id !== '' ? (int) $id : null;
    }

    /**
     * Helper to save uploaded category image file (SVG, PNG, WEBP, JPG)
     */
    private function uploadCategoryImage(array $file): ?string {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        $allowedExtensions = ['svg', 'png', 'webp', 'jpg', 'jpeg'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            return null;
        }

        $filename = upload_to_r2($file, 'categories', 'category');
        if ($filename) {
            return '/uploads/categories/' . $filename;
        }

        return null;
    }
}
