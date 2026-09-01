<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\NavLink;
use App\Core\Database;

class NavigationController extends Controller {

    private NavLink $navModel;

    public function __construct() {
        parent::__construct();
        $this->navModel = new NavLink();
    }

    /**
     * Display Manage Navigation section.
     */
    public function index(): string {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $navTree = $this->navModel->getTree(false);
        $flatLinks = $this->navModel->getAllFlat();

        // Fetch active categories for helper URL picker
        $db = Database::getInstance();
        $categories = $db->query("SELECT id, name, slug FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll() ?: [];

        return $this->render('admin/navigation/index', [
            'navTree' => $navTree,
            'flatLinks' => $flatLinks,
            'categories' => $categories
        ]);
    }

    /**
     * Store new navigation link.
     */
    public function store(): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $label = trim($this->request->input('label', ''));
        $urlInput = trim($this->request->input('url', ''));
        $type = trim($this->request->input('type', 'internal'));
        $parentId = $this->request->input('parent_id') ? (int)$this->request->input('parent_id') : null;
        $sortOrder = $this->request->input('sort_order') !== null && $this->request->input('sort_order') !== '' ? (int)$this->request->input('sort_order') : null;
        $isActive = $this->request->input('is_active') ? 1 : 0;
        $openInNewTab = $this->request->input('open_in_new_tab') ? 1 : 0;

        // Validation
        if (empty($label)) {
            $this->setFlash('error', 'Navigation label text is required.');
            $this->redirect(url('admin/navigation'));
            return;
        }

        if (!$this->isValidUrlFormat($urlInput)) {
            $this->setFlash('error', 'Invalid URL format. Please enter a valid path (e.g. /catalog) or full URL (e.g. https://example.com).');
            $this->redirect(url('admin/navigation'));
            return;
        }

        $this->navModel->createLink([
            'label' => $label,
            'url' => $urlInput,
            'type' => $type,
            'parent_id' => $parentId,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
            'open_in_new_tab' => $openInNewTab
        ]);

        $this->setFlash('success', "Navigation link '{$label}' added successfully.");
        $this->redirect(url('admin/navigation'));
    }

    /**
     * Update existing navigation link.
     */
    public function update(string $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $linkId = (int)$id;
        $label = trim($this->request->input('label', ''));
        $urlInput = trim($this->request->input('url', ''));
        $type = trim($this->request->input('type', 'internal'));
        $parentId = $this->request->input('parent_id') ? (int)$this->request->input('parent_id') : null;
        $sortOrder = (int)$this->request->input('sort_order', 0);
        $isActive = $this->request->input('is_active') ? 1 : 0;
        $openInNewTab = $this->request->input('open_in_new_tab') ? 1 : 0;

        // Validation
        if (empty($label)) {
            $this->setFlash('error', 'Navigation label text cannot be empty.');
            $this->redirect(url('admin/navigation'));
            return;
        }

        if (!$this->isValidUrlFormat($urlInput)) {
            $this->setFlash('error', 'Invalid URL format. Please enter a valid path (e.g. /catalog) or full URL (e.g. https://example.com).');
            $this->redirect(url('admin/navigation'));
            return;
        }

        $this->navModel->updateLink($linkId, [
            'label' => $label,
            'url' => $urlInput,
            'type' => $type,
            'parent_id' => $parentId,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
            'open_in_new_tab' => $openInNewTab
        ]);

        $this->setFlash('success', "Navigation link '{$label}' updated successfully.");
        $this->redirect(url('admin/navigation'));
    }

    /**
     * Delete a navigation link.
     */
    public function delete(string $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $linkId = (int)$id;
        $this->navModel->deleteLink($linkId);

        $this->setFlash('success', 'Navigation link deleted successfully.');
        $this->redirect(url('admin/navigation'));
    }

    /**
     * Toggle Active/Inactive status.
     */
    public function toggleStatus(string $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $linkId = (int)$id;
        $this->navModel->toggleStatus($linkId);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $this->json(['success' => true]);
            return;
        }

        $this->setFlash('success', 'Navigation link status toggled.');
        $this->redirect(url('admin/navigation'));
    }

    /**
     * Move position up or down.
     */
    public function move(string $id, string $direction): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $linkId = (int)$id;
        $this->navModel->movePosition($linkId, $direction);

        $this->setFlash('success', 'Navigation order updated.');
        $this->redirect(url('admin/navigation'));
    }

    /**
     * Reorder via AJAX drag-and-drop or array submit.
     */
    public function reorder(): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $orderedIds = $this->request->input('ordered_ids', []);
        if (is_string($orderedIds)) {
            $orderedIds = json_decode($orderedIds, true) ?: [];
        }

        if (!empty($orderedIds) && is_array($orderedIds)) {
            $this->navModel->reorder($orderedIds);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                $this->json(['success' => true]);
                return;
            }
        }

        $this->setFlash('success', 'Navigation menu reordered successfully.');
        $this->redirect(url('admin/navigation'));
    }

    /**
     * Validate URL / Path format.
     */
    private function isValidUrlFormat(string $url): bool {
        if (empty($url)) return false;
        // Allow relative URLs starting with / or #, or absolute http/https/javascript:void(0)
        if (preg_match('/^(\/|#|http:\/\/|https:\/\/|javascript:)/i', $url)) {
            return true;
        }
        // Allow clean relative paths like 'catalog', 'blog', etc.
        if (preg_match('/^[a-zA-Z0-9_\-\/\?\=\&\.\#]+$/', $url)) {
            return true;
        }
        return false;
    }
}
