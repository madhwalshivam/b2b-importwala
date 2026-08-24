<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Category;
use App\Models\Subcategory;

class SubcategoryController extends Controller {
    protected Subcategory $subcategoryModel;
    protected Category $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->subcategoryModel = new Subcategory();
        $this->categoryModel    = new Category();
    }

    /**
     * Display subcategories list
     */
    public function index(): string {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $categoryId = (int)($this->request->input('category_id', 0));
        $categories = $this->categoryModel->getActiveCategories();

        if ($categoryId > 0) {
            $subcategories = $this->subcategoryModel->getByCategoryId($categoryId);
        } else {
            $subcategories = $this->subcategoryModel->getAllWithCategory();
        }

        return $this->render('admin/categories/subcategories', [
            'subcategories' => $subcategories,
            'categories'    => $categories,
            'selectedCategoryId' => $categoryId
        ]);
    }

    /**
     * Get subcategories as JSON for dynamic select dropdown (used in Product form)
     */
    public function getByCategory(int $categoryId): void {
        header('Content-Type: application/json');
        
        if ($categoryId <= 0) {
            echo json_encode(['success' => true, 'subcategories' => []]);
            exit;
        }

        $subcategories = $this->subcategoryModel->getByCategoryId($categoryId);
        echo json_encode(['success' => true, 'subcategories' => $subcategories]);
        exit;
    }

    /**
     * Get single subcategory JSON details for modal editing
     */
    public function get(int $id): void {
        header('Content-Type: application/json');
        
        $sub = $this->subcategoryModel->findWithCategory($id);
        if (!$sub) {
            echo json_encode(['success' => false, 'message' => 'Subcategory not found.']);
            exit;
        }

        echo json_encode(['success' => true, 'subcategory' => $sub]);
        exit;
    }

    /**
     * Store new subcategory
     */
    public function store(): void {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $isAjax = $this->request->isAjax();

        try {
            $categoryId = (int)$this->request->input('category_id', 0);
            $name       = trim($this->request->input('name', ''));
            $sortOrder  = (int)$this->request->input('sort_order', 0);
            $status     = $this->request->input('status', 'active');
            $description = trim($this->request->input('description', ''));

            if ($categoryId <= 0) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Parent Category is required.']);
                    exit;
                }
                $this->setFlash('danger', 'Parent Category is required.');
                $this->redirect(url('admin/subcategories'));
                return;
            }

            if (empty($name)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Subcategory name is required.']);
                    exit;
                }
                $this->setFlash('danger', 'Subcategory name is required.');
                $this->redirect(url('admin/subcategories'));
                return;
            }

            // Slug Handling
            $slugInput = trim($this->request->input('slug', ''));
            $slug = $slugInput ? slugify($slugInput) : slugify($name);
            
            // Ensure Unique Slug
            $baseSlug = $slug;
            $counter = 1;
            while ($this->subcategoryModel->slugExists($slug)) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Image Upload
            $imagePath = null;
            if (!empty($_FILES['image_file']['name'])) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $file = $_FILES['image_file'];
                if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $filename = 'subcat_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $uploadDir = __DIR__ . '/../../../public/uploads/categories/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                        $imagePath = '/uploads/categories/' . $filename;
                    }
                }
            }

            $id = $this->subcategoryModel->createSubcategory([
                'category_id' => $categoryId,
                'name'        => $name,
                'slug'        => $slug,
                'image'       => $imagePath,
                'description' => $description,
                'sort_order'  => $sortOrder,
                'status'      => $status,
            ]);

            activity_log('Create Subcategory', 'Categories', $id, "Created subcategory: {$name}");

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Subcategory created successfully.', 'id' => $id]);
                exit;
            }

            $this->setFlash('success', 'Subcategory created successfully.');
            $this->redirect(url('admin/subcategories'));
        } catch (\Throwable $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error creating subcategory: ' . $e->getMessage()]);
                exit;
            }
            $this->setFlash('danger', 'Error creating subcategory: ' . $e->getMessage());
            $this->redirect(url('admin/subcategories'));
        }
    }

    /**
     * Update existing subcategory
     */
    public function update(int $id): void {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $isAjax = $this->request->isAjax();

        try {
            $existing = $this->subcategoryModel->find($id);
            if (!$existing) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Subcategory not found.']);
                    exit;
                }
                $this->setFlash('danger', 'Subcategory not found.');
                $this->redirect(url('admin/subcategories'));
                return;
            }

            $defaultParent = $existing['parent_id'] ?? $existing['category_id'] ?? 0;
            $categoryId = (int)$this->request->input('category_id', $defaultParent);
            $name       = trim($this->request->input('name', ''));
            $sortOrder  = (int)$this->request->input('sort_order', 0);
            $status     = $this->request->input('status', 'active');
            $description = trim($this->request->input('description', ''));

            if (empty($name)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Subcategory name is required.']);
                    exit;
                }
                $this->setFlash('danger', 'Subcategory name is required.');
                $this->redirect(url('admin/subcategories'));
                return;
            }

            // Slug Handling
            $slugInput = trim($this->request->input('slug', ''));
            $slug = $slugInput ? slugify($slugInput) : slugify($name);

            // Ensure Unique Slug (ignoring current subcategory ID)
            $baseSlug = $slug;
            $counter = 1;
            while ($this->subcategoryModel->slugExists($slug, $id)) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Image Upload
            $imagePath = $existing['image'];
            if (!empty($_FILES['image_file']['name'])) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $file = $_FILES['image_file'];
                if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $filename = 'subcat_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $uploadDir = __DIR__ . '/../../../public/uploads/categories/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                        $imagePath = '/uploads/categories/' . $filename;
                    }
                }
            }

            $this->subcategoryModel->updateSubcategory($id, [
                'category_id' => $categoryId,
                'name'        => $name,
                'slug'        => $slug,
                'image'       => $imagePath,
                'description' => $description,
                'sort_order'  => $sortOrder,
                'status'      => $status,
            ]);

            activity_log('Update Subcategory', 'Categories', $id, "Updated subcategory: {$name}");

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Subcategory updated successfully.']);
                exit;
            }

            $this->setFlash('success', 'Subcategory updated successfully.');
            $this->redirect(url('admin/subcategories'));
        } catch (\Throwable $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error updating subcategory: ' . $e->getMessage()]);
                exit;
            }
            $this->setFlash('danger', 'Error updating subcategory: ' . $e->getMessage());
            $this->redirect(url('admin/subcategories'));
        }
    }

    /**
     * Delete subcategory (with product dependency check)
     */
    public function delete(int $id): void {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $isAjax = $this->request->isAjax();

        $prodCount = $this->subcategoryModel->getProductCount($id);
        if ($prodCount > 0) {
            $msg = "Cannot delete subcategory because {$prodCount} product(s) are assigned to it. Reassign or delete those products first.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $this->setFlash('danger', $msg);
            $this->redirect(url('admin/subcategories'));
            return;
        }

        $this->subcategoryModel->deleteSubcategory($id);
        activity_log('Delete Subcategory', 'Categories', $id, "Deleted subcategory ID: {$id}");

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Subcategory deleted successfully.']);
            exit;
        }

        $this->setFlash('success', 'Subcategory deleted successfully.');
        $this->redirect(url('admin/subcategories'));
    }
}
