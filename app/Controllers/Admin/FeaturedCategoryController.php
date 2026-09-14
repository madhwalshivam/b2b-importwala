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
        $subcategories = $this->subcategoryModel->getAll();
        $subcategoriesGrouped = [];

        foreach ($categories as $cat) {
            $subcategoriesGrouped[$cat['id']] = $this->subcategoryModel->getByCategory($cat['id']);
        }

        // Fetch regular categories & subcategories for admin link/source selector
        $db = \App\Core\Database::getInstance();
        $regCategories = $db->query("SELECT id, name, slug, COALESCE(NULLIF(image, ''), NULLIF(custom_icon, '')) AS image FROM categories WHERE (status = 'active' OR status = 'enabled') AND (parent_id IS NULL OR parent_id = 0 OR parent_id = '') ORDER BY name ASC")->fetchAll() ?: [];
        $regSubcategories = $db->query("
            SELECT s.id, s.name, s.slug, COALESCE(NULLIF(s.image, ''), NULLIF(c.image, '')) AS image, COALESCE(NULLIF(c.slug, ''), 'jewellery') AS parent_slug
            FROM subcategories s
            LEFT JOIN categories c ON (s.category_id = c.id OR s.category_id = c.slug)
            WHERE (s.status = 'active' OR s.status = 'enabled')
            ORDER BY s.name ASC
        ")->fetchAll() ?: [];

        return $this->render('admin/featured_categories/index', [
            'categories' => $categories,
            'subcategories' => $subcategories,
            'subcategoriesGrouped' => $subcategoriesGrouped,
            'regCategories' => $regCategories,
            'regSubcategories' => $regSubcategories
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
        $linkUrl = trim($this->request->input('link_url', ''));
        $sortOrder = (int)$this->request->input('sort_order', 0);
        $isActive = $this->request->input('is_active') ? 1 : 0;

        if (empty($name)) {
            $this->setFlash('error', 'Category label name is required.');
            $this->redirect(url('admin/featured-categories'));
            return;
        }

        $imagePath = $this->handleImageUpload('image_file', 'image_url');

        $this->categoryModel->createCategory([
            'name' => $name,
            'image' => $imagePath,
            'link_url' => $linkUrl ?: '/catalog',
            'sort_order' => $sortOrder,
            'is_active' => $isActive
        ]);

        $this->setFlash('success', 'Main category tile created successfully.');
        $this->redirect(url('admin/featured-categories'));
    }

    // Category Tab Update
    public function updateCategory(string $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $catId = (int)$id;
        $category = $this->categoryModel->findById($catId);
        if (!$category) {
            $this->setFlash('error', 'Main category tile not found.');
            $this->redirect(url('admin/featured-categories'));
            return;
        }

        $name = trim($this->request->input('name', ''));
        $linkUrl = trim($this->request->input('link_url', ''));
        $sortOrder = (int)$this->request->input('sort_order', 0);
        $isActive = $this->request->input('is_active') ? 1 : 0;

        if (empty($name)) {
            $this->setFlash('error', 'Category label name is required.');
            $this->redirect(url('admin/featured-categories'));
            return;
        }

        $imagePath = $this->handleImageUpload('image_file', 'image_url') ?: ($category['image'] ?? '');

        $this->categoryModel->updateCategory($catId, [
            'name' => $name,
            'image' => $imagePath,
            'link_url' => $linkUrl ?: ($category['link_url'] ?? '/catalog'),
            'sort_order' => $sortOrder,
            'is_active' => $isActive
        ]);

        $this->setFlash('success', 'Main category tile updated successfully.');
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

        if (empty($name)) {
            $this->setFlash('error', 'Subcategory name is required.');
            $this->redirect(url('admin/featured-categories'));
            return;
        }

        if (!$featuredCategoryId) {
            $allCats = $this->categoryModel->getAll();
            $featuredCategoryId = !empty($allCats) ? (int)$allCats[0]['id'] : 1;
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

        $this->setFlash('success', 'Subcategory icon item created successfully.');
        $this->redirect(url('admin/featured-categories'));
    }

    // Subcategory Card Update
    public function updateSubcategory(string $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $subId = (int)$id;
        $subcat = $this->subcategoryModel->findById($subId);
        if (!$subcat) {
            $this->setFlash('error', 'Subcategory item not found.');
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
            'name' => $name ?: $subcat['name'],
            'image' => $imagePath,
            'link_url' => $linkUrl ?: '/catalog',
            'sort_order' => $sortOrder,
            'is_active' => $isActive
        ]);

        $this->setFlash('success', 'Subcategory icon item updated.');
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
