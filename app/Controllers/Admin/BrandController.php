<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Brand;
use App\Helpers\Paginator;

class BrandController extends Controller {
    protected Brand $brandModel;

    public function __construct() {
        parent::__construct();
        $this->brandModel = new Brand();
    }

    public function index(): string {
        if (!Auth::hasPermission('brands.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $brands = $this->brandModel->getAllOrdered();

        return $this->render('admin/brands/index', [
            'brands' => $brands
        ]);
    }

    public function store(): void {
        if (!Auth::hasPermission('brands.add')) {
            $this->redirect(url('admin/brands'));
        }

        $name = trim($this->request->input('name', ''));
        if (empty($name)) {
            $this->setFlash('error', 'Brand name is required.');
            $this->redirect(url('admin/brands'));
        }

        $websiteLink = trim($this->request->input('website_link', ''));
        $isActive = isset($_POST['is_active']) || (isset($_POST['status']) && $_POST['status'] === 'active') ? 1 : 0;
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $description = trim($this->request->input('description', ''));

        // Calculate next sort_order
        $nextOrder = $this->brandModel->getMaxSortOrder() + 1;

        $logoPath = null;
        $logoUrlInput = trim($this->request->input('logo_url', ''));

        if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleLogoUpload($_FILES['logo'], $name);
            if (!$uploadResult['success']) {
                $this->setFlash('error', $uploadResult['message']);
                $this->redirect(url('admin/brands'));
            }
            $logoPath = $uploadResult['path'];
        } elseif (!empty($logoUrlInput)) {
            $logoPath = $logoUrlInput;
        }

        $data = [
            'name'         => $name,
            'slug'         => slugify($name),
            'logo'         => $logoPath,
            'logo_path'    => $logoPath,
            'website_link' => $websiteLink ?: null,
            'description'  => $description,
            'is_featured'  => $isFeatured,
            'is_active'    => $isActive,
            'status'       => $isActive ? 'active' : 'inactive',
            'sort_order'   => (int)$this->request->input('sort_order', $nextOrder)
        ];

        $id = $this->brandModel->insert($data);

        activity_log('Create Brand', 'Brands', $id, "Added featured brand: {$name}");
        $this->setFlash('success', 'Brand added successfully.');
        $this->redirect(url('admin/brands'));
    }

    public function update(int $id): void {
        if (!Auth::hasPermission('brands.edit')) {
            $this->redirect(url('admin/brands'));
        }

        $brand = $this->brandModel->find($id);
        if (!$brand) {
            $this->setFlash('error', 'Brand not found.');
            $this->redirect(url('admin/brands'));
        }

        $name = trim($this->request->input('name', $brand['name']));
        if (empty($name)) {
            $this->setFlash('error', 'Brand name cannot be empty.');
            $this->redirect(url('admin/brands'));
        }

        $websiteLink = trim($this->request->input('website_link', ''));
        $isActive = isset($_POST['is_active']) ? ((int)$_POST['is_active'] === 1 ? 1 : 0) : ($this->request->input('status') === 'active' ? 1 : 0);
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $description = trim($this->request->input('description', ''));

        $logoPath = $brand['logo_path'] ?? $brand['logo'] ?? null;
        $logoUrlInput = trim($this->request->input('logo_url', ''));

        if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleLogoUpload($_FILES['logo'], $name);
            if (!$uploadResult['success']) {
                $this->setFlash('error', $uploadResult['message']);
                $this->redirect(url('admin/brands'));
            }
            
            // Unlink old logo file if local
            $this->deletePhysicalFile($brand['logo_path'] ?? $brand['logo'] ?? '');

            $logoPath = $uploadResult['path'];
        } elseif (!empty($logoUrlInput)) {
            $logoPath = $logoUrlInput;
        }

        $data = [
            'name'         => $name,
            'slug'         => slugify($name),
            'logo'         => $logoPath,
            'logo_path'    => $logoPath,
            'website_link' => $websiteLink ?: null,
            'description'  => $description,
            'is_featured'  => $isFeatured,
            'is_active'    => $isActive,
            'status'       => $isActive ? 'active' : 'inactive',
            'sort_order'   => (int)$this->request->input('sort_order', $brand['sort_order'])
        ];

        $this->brandModel->update($id, $data);
        activity_log('Update Brand', 'Brands', $id, "Updated brand: {$name}");
        $this->setFlash('success', 'Brand updated successfully.');
        $this->redirect(url('admin/brands'));
    }

    public function delete(int $id): void {
        if (!Auth::hasPermission('brands.delete')) {
            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
                return;
            }
            $this->redirect(url('admin/brands'));
        }

        $brand = $this->brandModel->find($id);
        if ($brand) {
            // Delete physical logo file from disk
            $this->deletePhysicalFile($brand['logo_path'] ?? $brand['logo'] ?? '');
            
            $this->brandModel->delete($id);
            activity_log('Delete Brand', 'Brands', $id, "Deleted brand: {$brand['name']}");
        }

        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'message' => 'Brand deleted successfully.']);
            return;
        }

        $this->setFlash('success', 'Brand deleted successfully.');
        $this->redirect(url('admin/brands'));
    }

    public function toggleStatus(int $id): void {
        if (!Auth::hasPermission('brands.edit')) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $brand = $this->brandModel->find($id);
        if (!$brand) {
            $this->json(['success' => false, 'message' => 'Brand not found'], 404);
            return;
        }

        $currentActive = (int)($brand['is_active'] ?? ($brand['status'] === 'active' ? 1 : 0));
        $newActive = $currentActive === 1 ? 0 : 1;
        $newStatus = $newActive === 1 ? 'active' : 'inactive';

        $this->brandModel->update($id, [
            'is_active' => $newActive,
            'status'    => $newStatus
        ]);

        activity_log('Toggle Brand Status', 'Brands', $id, "Toggled status to " . ($newActive ? 'Active' : 'Inactive'));

        if ($this->request->isAjax()) {
            $this->json([
                'success' => true,
                'is_active' => $newActive,
                'status' => $newStatus,
                'message' => 'Status updated successfully.'
            ]);
            return;
        }

        $this->setFlash('success', 'Status updated successfully.');
        $this->redirect(url('admin/brands'));
    }

    public function reorder(): void {
        if (!Auth::hasPermission('brands.edit')) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $order = $_POST['order'] ?? [];
        if (is_string($order)) {
            $order = json_decode($order, true);
        }

        if (!is_array($order) || empty($order)) {
            $this->json(['success' => false, 'message' => 'Invalid order data'], 400);
            return;
        }

        $this->brandModel->updateSortOrders($order);
        activity_log('Reorder Brands', 'Brands', null, "Updated brand sorting order");

        $this->json(['success' => true, 'message' => 'Brand order updated successfully.']);
    }

    public function bulkAction(): void {
        if (!Auth::hasPermission('brands.edit')) {
            $this->redirect(url('admin/brands'));
        }

        $action = $this->request->input('action');
        $ids = $_POST['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            $this->setFlash('error', 'No brands selected.');
            $this->redirect(url('admin/brands'));
        }

        $cleanIds = array_map('intval', $ids);

        if ($action === 'activate') {
            foreach ($cleanIds as $id) {
                $this->brandModel->update($id, ['is_active' => 1, 'status' => 'active']);
            }
            $this->setFlash('success', 'Selected brands activated.');
        } elseif ($action === 'deactivate') {
            foreach ($cleanIds as $id) {
                $this->brandModel->update($id, ['is_active' => 0, 'status' => 'inactive']);
            }
            $this->setFlash('success', 'Selected brands deactivated.');
        } elseif ($action === 'delete') {
            if (!Auth::hasPermission('brands.delete')) {
                $this->setFlash('error', 'Permission denied for deletion.');
                $this->redirect(url('admin/brands'));
            }
            foreach ($cleanIds as $id) {
                $brand = $this->brandModel->find($id);
                if ($brand) {
                    $this->deletePhysicalFile($brand['logo_path'] ?? $brand['logo'] ?? '');
                    $this->brandModel->delete($id);
                }
            }
            $this->setFlash('success', 'Selected brands deleted.');
        }

        $this->redirect(url('admin/brands'));
    }

    /**
     * File Upload Helper with MIME validation and filename sanitization
     */
    protected function handleLogoUpload(array $file, string $brandName): array {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'gif'];
        $allowedMimetypes  = [
            'image/jpeg',
            'image/png',
            'image/svg+xml',
            'image/webp',
            'image/gif',
            'text/xml',
            'image/svg'
        ];
        $maxSizeBytes = 2 * 1024 * 1024; // 2MB

        if ($file['size'] > $maxSizeBytes) {
            return ['success' => false, 'message' => 'Logo file size exceeds 2MB limit.'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            return ['success' => false, 'message' => 'Invalid file extension. Only JPG, PNG, SVG, WEBP, and GIF are allowed.'];
        }

        // Validate MIME type via finfo
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mimeType, $allowedMimetypes)) {
                return ['success' => false, 'message' => "Invalid file MIME type ({$mimeType}). Only images are allowed."];
            }
        }

        // Generate sanitized unique filename
        $cleanBrandName = slugify($brandName);
        if (empty($cleanBrandName)) $cleanBrandName = 'brand';
        $fileName = $cleanBrandName . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $uploadDir = __DIR__ . '/../../../public/uploads/brands/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'message' => 'Failed to save uploaded file on server.'];
        }

        return [
            'success' => true,
            'path'    => '/uploads/brands/' . $fileName
        ];
    }

    /**
     * Delete physical logo file from disk if stored locally in uploads directory
     */
    protected function deletePhysicalFile(string $filePath): void {
        if (empty($filePath)) return;

        // Only delete local uploads to prevent security issues
        if (str_starts_with($filePath, '/uploads/brands/') || str_starts_with($filePath, 'uploads/brands/')) {
            $fullPath = __DIR__ . '/../../../public/' . ltrim($filePath, '/');
            if (file_exists($fullPath) && is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }
}
