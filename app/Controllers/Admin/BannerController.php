<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Banner;

class BannerController extends Controller {

    private Banner $bannerModel;

    public function __construct() {
        parent::__construct();
        $this->bannerModel = new Banner();
    }

    public function index(): string {
        if (!Auth::check()) $this->redirect(url('admin/login'));
        $banners = $this->bannerModel->getAllBanners();
        return $this->render('admin/banners/index', ['banners' => $banners]);
    }

    // -----------------------------------------------------------------------
    // CREATE
    // -----------------------------------------------------------------------
    public function store(): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $title           = trim($this->request->input('title', ''));
        $subtitle        = trim($this->request->input('subtitle', ''));
        $imageUrl        = trim($this->request->input('image_url', ''));
        $tabletImageUrl  = trim($this->request->input('tablet_image_url', ''));
        $mobileImageUrl  = trim($this->request->input('mobile_image_url', ''));
        $linkUrl         = trim($this->request->input('link_url', ''));
        $ctaText         = trim($this->request->input('cta_text', ''));
        $sortOrder       = (int)$this->request->input('sort_order', 0);
        $isActive        = $this->request->input('is_active') ? 1 : 0;

        $imagePath        = null;
        $tabletImagePath  = null;
        $mobileImagePath  = null;

        $allowed   = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $uploadDir = __DIR__ . '/../../../public/uploads/banners/';

        // Desktop Upload
        if (!empty($_FILES['image_file']['name'])) {
            $file = $_FILES['image_file'];
            if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'banner_desktop_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $imagePath = $filename;
                    $imageUrl  = '';  // clear URL if file uploaded
                }
            }
        }

        // Tablet Upload
        if (!empty($_FILES['tablet_image_file']['name'])) {
            $file = $_FILES['tablet_image_file'];
            if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'banner_tablet_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $tabletImagePath = $filename;
                    $tabletImageUrl  = '';
                }
            }
        }

        // Mobile Upload
        if (!empty($_FILES['mobile_image_file']['name'])) {
            $file = $_FILES['mobile_image_file'];
            if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'banner_mobile_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $mobileImagePath = $filename;
                    $mobileImageUrl  = '';
                }
            }
        }

        // Desktop image required
        if (empty($imagePath) && empty($imageUrl)) {
            $this->redirect(url('admin/banners') . '?error=Please+upload+a+desktop+image+or+enter+an+image+URL');
        }

        $this->bannerModel->create([
            'title'             => $title,
            'subtitle'          => $subtitle,
            'image_path'        => $imagePath,
            'image_url'         => $imageUrl ?: null,
            'tablet_image_path' => $tabletImagePath,
            'tablet_image_url'  => $tabletImageUrl ?: null,
            'mobile_image_path' => $mobileImagePath,
            'mobile_image_url'  => $mobileImageUrl ?: null,
            'link_url'          => $linkUrl,
            'cta_text'          => $ctaText,
            'sort_order'        => $sortOrder,
            'is_active'         => $isActive,
        ]);

        $this->redirect(url('admin/banners') . '?success=Banner+added+successfully');
    }

    // -----------------------------------------------------------------------
    // EDIT
    // -----------------------------------------------------------------------
    public function edit(int $id): string {
        if (!Auth::check()) $this->redirect(url('admin/login'));
        $banner = $this->bannerModel->findById($id);
        if (!$banner) $this->redirect(url('admin/banners'));
        return $this->render('admin/banners/edit', ['banner' => $banner]);
    }

    // -----------------------------------------------------------------------
    // UPDATE
    // -----------------------------------------------------------------------
    public function update(int $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $banner = $this->bannerModel->findById($id);
        if (!$banner) $this->redirect(url('admin/banners'));

        $title           = trim($this->request->input('title', ''));
        $subtitle        = trim($this->request->input('subtitle', ''));
        $imageUrl        = trim($this->request->input('image_url', ''));
        $tabletImageUrl  = trim($this->request->input('tablet_image_url', ''));
        $mobileImageUrl  = trim($this->request->input('mobile_image_url', ''));
        $linkUrl         = trim($this->request->input('link_url', ''));
        $ctaText         = trim($this->request->input('cta_text', ''));
        $sortOrder       = (int)$this->request->input('sort_order', 0);
        $isActive        = $this->request->input('is_active') ? 1 : 0;

        // Preserve existing images unless replaced
        $imagePath        = $banner['image_path']        ?? null;
        $tabletImagePath  = $banner['tablet_image_path'] ?? null;
        $mobileImagePath  = $banner['mobile_image_path'] ?? null;

        // Preserve existing URL values unless new URL typed
        $existingImageUrl       = $banner['image_url']        ?? '';
        $existingTabletImageUrl = $banner['tablet_image_url'] ?? '';
        $existingMobileImageUrl = $banner['mobile_image_url'] ?? '';

        $allowed   = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $uploadDir = __DIR__ . '/../../../public/uploads/banners/';

        // Desktop Upload
        if (!empty($_FILES['image_file']['name'])) {
            $file = $_FILES['image_file'];
            if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'banner_desktop_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    if ($imagePath && file_exists($uploadDir . $imagePath)) @unlink($uploadDir . $imagePath);
                    $imagePath = $filename;
                    $imageUrl  = '';  // clear URL when file uploaded
                }
            }
        }

        // Tablet Upload
        if (!empty($_FILES['tablet_image_file']['name'])) {
            $file = $_FILES['tablet_image_file'];
            if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'banner_tablet_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    if ($tabletImagePath && file_exists($uploadDir . $tabletImagePath)) @unlink($uploadDir . $tabletImagePath);
                    $tabletImagePath = $filename;
                    $tabletImageUrl  = '';
                }
            }
        }

        // Mobile Upload
        if (!empty($_FILES['mobile_image_file']['name'])) {
            $file = $_FILES['mobile_image_file'];
            if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'banner_mobile_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    if ($mobileImagePath && file_exists($uploadDir . $mobileImagePath)) @unlink($uploadDir . $mobileImagePath);
                    $mobileImagePath = $filename;
                    $mobileImageUrl  = '';
                }
            }
        }

        // Remove Desktop image if requested
        if ($this->request->input('remove_desktop_image')) {
            if ($imagePath && file_exists($uploadDir . $imagePath)) @unlink($uploadDir . $imagePath);
            $imagePath = null;
            $imageUrl  = '';
        }

        // Remove Tablet image if requested
        if ($this->request->input('remove_tablet_image')) {
            if ($tabletImagePath && file_exists($uploadDir . $tabletImagePath)) @unlink($uploadDir . $tabletImagePath);
            $tabletImagePath = null;
            $tabletImageUrl  = '';
        }

        // Remove Mobile image if requested
        if ($this->request->input('remove_mobile_image')) {
            if ($mobileImagePath && file_exists($uploadDir . $mobileImagePath)) @unlink($uploadDir . $mobileImagePath);
            $mobileImagePath = null;
            $mobileImageUrl  = '';
        }

        // Desktop: if no file uploaded and no URL typed, keep existing URL
        if (empty($imagePath) && empty($imageUrl)) {
            $imageUrl = $existingImageUrl;
        }

        // Tablet: if no file uploaded and URL field left empty, keep existing URL
        if (empty($tabletImagePath) && empty($tabletImageUrl)) {
            $tabletImageUrl = $existingTabletImageUrl;
        }

        // Mobile: same
        if (empty($mobileImagePath) && empty($mobileImageUrl)) {
            $mobileImageUrl = $existingMobileImageUrl;
        }

        $this->bannerModel->update($id, [
            'title'             => $title,
            'subtitle'          => $subtitle,
            'image_path'        => $imagePath,
            'image_url'         => ($imagePath ? null : ($imageUrl ?: null)),
            'tablet_image_path' => $tabletImagePath,
            'tablet_image_url'  => ($tabletImagePath ? null : ($tabletImageUrl ?: null)),
            'mobile_image_path' => $mobileImagePath,
            'mobile_image_url'  => ($mobileImagePath ? null : ($mobileImageUrl ?: null)),
            'link_url'          => $linkUrl,
            'cta_text'          => $ctaText,
            'sort_order'        => $sortOrder,
            'is_active'         => $isActive,
        ]);

        $this->redirect(url('admin/banners') . '?success=Banner+updated+successfully');
    }

    // -----------------------------------------------------------------------
    // TOGGLE STATUS
    // -----------------------------------------------------------------------
    public function toggleStatus(int $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));
        $banner = $this->bannerModel->findById($id);
        if ($banner) {
            $this->bannerModel->update($id, array_merge($banner, [
                'is_active' => $banner['is_active'] ? 0 : 1
            ]));
        }
        $this->redirect(url('admin/banners') . '?success=Status+updated');
    }

    // -----------------------------------------------------------------------
    // DELETE
    // -----------------------------------------------------------------------
    public function delete(int $id): void {
        if (!Auth::check()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $this->redirect(url('admin/login'));
        }

        $banner = $this->bannerModel->findById($id);
        if ($banner) {
            $uploadDir = __DIR__ . '/../../../public/uploads/banners/';

            // Delete all 3 device images from disk
            foreach (['image_path', 'tablet_image_path', 'mobile_image_path'] as $col) {
                if (!empty($banner[$col])) {
                    $file = $uploadDir . basename($banner[$col]);
                    if (file_exists($file)) @unlink($file);
                }
            }

            $this->bannerModel->delete($id);
        }

        // AJAX delete returns JSON; normal form post redirects
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        $this->redirect(url('admin/banners') . '?success=Banner+deleted');
    }

    // -----------------------------------------------------------------------
    // HELPERS
    // -----------------------------------------------------------------------
    private function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
