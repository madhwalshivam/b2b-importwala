<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\FeaturedCategory;
use App\Models\FeaturedSubcategory;

class FeaturedCategoryController extends Controller {

    private FeaturedCategory $categoryModel;
    private FeaturedSubcategory $subcategoryModel;

    public function __construct() {
        parent::__construct();
        $this->categoryModel = new FeaturedCategory();
        $this->subcategoryModel = new FeaturedSubcategory();
    }

    public function index(): string {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $categories = $this->categoryModel->getAll();
        $subcategoriesGrouped = [];

        foreach ($categories as $cat) {
            $subcategoriesGrouped[$cat['id']] = $this->subcategoryModel->getByCategory($cat['id']);
        }

        // Fetch regular categories for dropdown link selector
        $db = \App\Core\Database::getInstance();
        $regCategories = $db->query("SELECT id, name, slug FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll() ?: [];

        return $this->render('admin/featured_categories/index', [
            'categories' => $categories,
            'subcategoriesGrouped' => $subcategoriesGrouped,
            'regCategories' => $regCategories
        ]);
    }

    // Public API endpoint for storefront
    public function apiIndex(): void {
        $data = $this->categoryModel->getActiveWithSubcategories();
        $this->json(['success' => true, 'data' => $data]);
    }

    // Category Tab Store
    public function storeCategory(): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $name = trim($this->request->input('name', ''));
        $sortOrder = (int)$this->request->input('sort_order', 0);
        $isActive = $this->request->input('is_active') ? 1 : 0;

        if (empty($name)) {
            $this->setFlash('error', 'Category name is required.');
            $this->redirect(url('admin/featured-categories'));
            return;
        }

        $this->categoryModel->createCategory([
            'name' => $name,
            'sort_order' => $sortOrder,
            'is_active' => $isActive
        ]);

        $this->setFlash('success', 'Featured category tab created successfully.');
        $this->redirect(url('admin/featured-categories'));
    }

    // Category Tab Update
    public function updateCategory(string $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $catId = (int)$id;
        $name = trim($this->request->input('name', ''));
        $sortOrder = (int)$this->request->input('sort_order', 0);
        $isActive = $this->request->input('is_active') ? 1 : 0;

        if (empty($name)) {
            $this->setFlash('error', 'Category name is required.');
            $this->redirect(url('admin/featured-categories'));
            return;
        }

        $this->categoryModel->updateCategory($catId, [
            'name' => $name,
            'sort_order' => $sortOrder,
            'is_active' => $isActive
        ]);

        $this->setFlash('success', 'Featured category tab updated successfully.');
        $this->redirect(url('admin/featured-categories'));
    }

    // Category Tab Delete
    public function deleteCategory(string $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $this->categoryModel->deleteCategory((int)$id);
        $this->setFlash('success', 'Featured category tab deleted.');
        $this->redirect(url('admin/featured-categories'));
    }

    // Subcategory Card Store
    public function storeSubcategory(): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $featuredCategoryId = (int)$this->request->input('featured_category_id');
        $name = trim($this->request->input('name', ''));
        $linkUrl = trim($this->request->input('link_url', ''));
        $sortOrder = (int)$this->request->input('sort_order', 0);
        $isActive = $this->request->input('is_active') ? 1 : 0;

        if (empty($name) || !$featuredCategoryId) {
            $this->setFlash('error', 'Category tab and subcategory name are required.');
            $this->redirect(url('admin/featured-categories'));
            return;
        }

        $imagePath = $this->handleImageUpload('image_file', 'image_url');

        $this->subcategoryModel->createSubcategory([
            'featured_category_id' => $featuredCategoryId,
            'name' => $name,
            'image' => $imagePath,
            'link_url' => $linkUrl ?: '/catalog',
            'sort_order' => $sortOrder,
            'is_active' => $isActive
        ]);

        $this->setFlash('success', 'Featured subcategory card created successfully.');
        $this->redirect(url('admin/featured-categories'));
    }

    // Subcategory Card Update
    public function updateSubcategory(string $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $subId = (int)$id;
        $subcat = $this->subcategoryModel->findById($subId);
        if (!$subcat) {
            $this->setFlash('error', 'Subcategory card not found.');
            $this->redirect(url('admin/featured-categories'));
            return;
        }

        $featuredCategoryId = (int)$this->request->input('featured_category_id');
        $name = trim($this->request->input('name', ''));
        $linkUrl = trim($this->request->input('link_url', ''));
        $sortOrder = (int)$this->request->input('sort_order', 0);
        $isActive = $this->request->input('is_active') ? 1 : 0;

        $imagePath = $this->handleImageUpload('image_file', 'image_url') ?: $subcat['image'];

        $this->subcategoryModel->updateSubcategory($subId, [
            'featured_category_id' => $featuredCategoryId ?: $subcat['featured_category_id'],
            'name' => $name,
            'image' => $imagePath,
            'link_url' => $linkUrl ?: '/catalog',
            'sort_order' => $sortOrder,
            'is_active' => $isActive
        ]);

        $this->setFlash('success', 'Featured subcategory card updated.');
        $this->redirect(url('admin/featured-categories'));
    }

    // Subcategory Card Delete
    public function deleteSubcategory(string $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $this->subcategoryModel->deleteSubcategory((int)$id);
        $this->setFlash('success', 'Subcategory card deleted.');
        $this->redirect(url('admin/featured-categories'));
    }

    // AJAX Reorder Categories
    public function reorderCategories(): void {
        if (!Auth::check()) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }
        $order = $this->request->input('order', []);
        if (is_array($order)) {
            $this->categoryModel->updateSortOrder($order);
        }
        $this->json(['success' => true]);
    }

    // AJAX Reorder Subcategories
    public function reorderSubcategories(): void {
        if (!Auth::check()) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }
        $order = $this->request->input('order', []);
        if (is_array($order)) {
            $this->subcategoryModel->updateSortOrder($order);
        }
        $this->json(['success' => true]);
    }

    private function handleImageUpload(string $fileInputName, string $urlInputName): string {
        $urlInput = trim($this->request->input($urlInputName, ''));
        
        if (!empty($_FILES[$fileInputName]['name'])) {
            $file = $_FILES[$fileInputName];
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
            $uploadDir = __DIR__ . '/../../../public/uploads/featured_categories/';

            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'feat_sub_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    return '/uploads/featured_categories/' . $filename;
                }
            }
        }

        return $urlInput;
    }
}
