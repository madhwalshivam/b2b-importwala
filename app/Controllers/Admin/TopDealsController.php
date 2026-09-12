<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\HomeSection;
use App\Models\Product;
use App\Models\TopDealSection;

class TopDealsController extends Controller {
    protected HomeSection $sectionModel;
    protected Product $productModel;

    public function __construct() {
        parent::__construct();
        $this->sectionModel = new HomeSection();
        $this->productModel = new Product();
    }

    /**
     * Ensure legacy top_deals_settings are migrated/synced to homepage_sections with section_style = 'deals_row'
     */
    private function syncLegacyTopDeals(): void {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->query("SELECT COUNT(*) FROM homepage_sections WHERE slug = 'top-deals' OR section_key = 'top_deals'");
            $count = (int)($stmt ? $stmt->fetchColumn() : 0);

            if ($count === 0) {
                $topDealModel = new TopDealSection();
                $settings = $topDealModel->getSettings();
                $products = $topDealModel->getProducts();

                $secId = $this->sectionModel->createSection([
                    'title'                  => $settings['title'] ?? 'Top Deals',
                    'slug'                   => 'top-deals',
                    'subtitle'               => $settings['subtitle'] ?? 'Limited time offers — grab them before they sell out!',
                    'custom_url'             => $settings['custom_url'] ?? '',
                    'section_style'          => 'deals_row',
                    'max_products'           => 20,
                    'homepage_display_count' => 10,
                    'sort_order'             => 1,
                    'status'                 => $settings['status'] ?? 'active'
                ]);

                if (!empty($products) && $secId > 0) {
                    $pIds = array_column($products, 'id');
                    $this->sectionModel->saveSectionProducts($secId, $pIds);
                }
            }
        } catch (\Throwable $e) {
            // Silence sync error if already exists
        }
    }

    /**
     * Dedicated Admin View for Top Deals & Row Sections Manager
     */
    public function index(): string {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
        }

        $this->syncLegacyTopDeals();

        $rawSections = $this->sectionModel->getAllSections();
        $displaySections = [];

        foreach ($rawSections as &$sec) {
            $secId = (int)$sec['id'];
            $sec['products'] = $this->sectionModel->getSectionProducts($secId, null, false);
            // Include section if section_style is deals_row or section_key is featured_deals or slug is top-deals
            if (($sec['section_style'] ?? '') === 'deals_row' || ($sec['section_key'] ?? '') === 'featured_deals' || ($sec['slug'] ?? '') === 'top-deals') {
                $sec['section_style'] = 'deals_row';
                $displaySections[] = $sec;
            }
        }
        unset($sec);

        // Sort sections by sort_order ASC, id ASC
        usort($displaySections, function($a, $b) {
            $orderA = (int)($a['sort_order'] ?? 0);
            $orderB = (int)($b['sort_order'] ?? 0);
            if ($orderA === $orderB) {
                return ((int)$a['id']) <=> ((int)$b['id']);
            }
            return $orderA <=> $orderB;
        });

        $allProducts = $this->productModel->getAllActiveProducts();

        return $this->render('admin/top_deals/index', [
            'displaySections' => $displaySections,
            'rawSections'     => $rawSections,
            'allProducts'     => $allProducts
        ]);
    }

    /**
     * Create a new Deals section from Top Deals Manager
     */
    public function store(): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
            return;
        }

        $title        = trim($this->request->input('title', ''));
        $slug         = trim($this->request->input('slug', ''));
        $subtitle     = trim($this->request->input('subtitle', ''));
        $customUrl    = trim($this->request->input('custom_url', ''));
        $maxProducts  = (int)$this->request->input('max_products', 20);
        $displayCount = (int)$this->request->input('homepage_display_count', 10);
        $sortOrder    = (int)$this->request->input('sort_order', 1);
        $status       = $this->request->input('status', 'active');

        if (empty($title)) {
            $this->setFlash('error', 'Section Title is required.');
            $this->redirect(url('admin/top-deals'));
            return;
        }

        $sectionId = $this->sectionModel->createSection([
            'title'                  => $title,
            'slug'                   => $slug,
            'subtitle'               => $subtitle,
            'custom_url'             => $customUrl,
            'section_style'          => 'deals_row',
            'max_products'           => $maxProducts,
            'homepage_display_count' => $displayCount,
            'sort_order'             => $sortOrder,
            'status'                 => $status
        ]);

        $this->setFlash('success', 'New Deals Section "' . htmlspecialchars($title) . '" created successfully!');
        $this->redirect(url('admin/top-deals'));
    }

    /**
     * Update section config & selected products
     */
    public function update(?string $key = null): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            if ($this->request->isAjax() || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
                $this->response->json(['success' => false, 'message' => 'Unauthorized'], 403);
                return;
            }
            $this->redirect(url('admin/dashboard'));
            return;
        }

        // Determine section ID directly from section_id input or route key parameter
        $targetId = (int)$this->request->input('section_id', 0);
        if ($targetId <= 0 && is_numeric($key)) {
            $targetId = (int)$key;
        }

        $section = null;
        if ($targetId > 0) {
            $section = $this->sectionModel->find($targetId);
        }
        if (!$section && !empty($key)) {
            $section = $this->sectionModel->findByKey($key);
        }
        if (!$section) {
            $slugInput = trim($this->request->input('slug', ''));
            if (!empty($slugInput)) {
                $section = $this->sectionModel->findByKey($slugInput);
            }
        }

        if (!$section) {
            if ($this->request->isAjax()) {
                $this->response->json(['success' => false, 'message' => 'Section not found'], 404);
                return;
            }
            $this->setFlash('error', 'Deals section not found.');
            $this->redirect(url('admin/top-deals'));
            return;
        }

        $sectionId = (int)$section['id'];

        $title        = trim($this->request->input('title', $section['title']));
        $slug         = trim($this->request->input('slug', $section['slug']));
        $subtitle     = trim($this->request->input('subtitle', $section['subtitle'] ?? ''));
        $customUrl    = trim($this->request->input('custom_url', $section['custom_url'] ?? ''));
        $statusInput  = $this->request->input('status', null);
        $enabledInput = $this->request->input('enabled', null);
        $maxProducts  = (int)$this->request->input('max_products', $section['max_products'] ?? 20);
        $displayCount = (int)$this->request->input('homepage_display_count', $section['homepage_display_count'] ?? 10);
        $sortOrder    = (int)$this->request->input('sort_order', $section['sort_order'] ?? 1);

        if (empty($title)) {
            $title = $section['title'];
        }

        $statusVal = $section['status'];
        if ($statusInput !== null && $statusInput !== '') {
            $statusVal = in_array(strtolower((string)$statusInput), ['active', 'enabled', '1', 'true']) ? 'active' : 'inactive';
        } elseif ($enabledInput !== null) {
            $statusVal = (!empty($enabledInput) && $enabledInput !== '0' && $enabledInput !== 'false') ? 'active' : 'inactive';
        }

        $this->sectionModel->updateSectionConfig($sectionId, [
            'title'                  => $title,
            'slug'                   => $slug,
            'subtitle'               => $subtitle,
            'custom_url'             => $customUrl,
            'section_style'          => 'deals_row',
            'status'                 => $statusVal,
            'max_products'           => $maxProducts,
            'homepage_display_count' => $displayCount,
            'sort_order'             => $sortOrder
        ]);

        $productIds = $this->request->input('product_ids', null);
        if ($productIds !== null) {
            if (is_string($productIds)) {
                $productIds = array_filter(array_map('trim', explode(',', $productIds)), fn($v) => $v !== '' && is_numeric($v));
            }
            if (is_array($productIds)) {
                $this->sectionModel->saveSectionProducts($sectionId, array_values($productIds));
            }
        }

        if ($this->request->isAjax() || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            $updatedSection = $this->sectionModel->find($sectionId);
            $updatedSection['products'] = $this->sectionModel->getSectionProducts($sectionId, null, false);
            $this->response->json([
                'success' => true,
                'message' => 'Deals section updated successfully',
                'section' => $updatedSection
            ]);
            return;
        }

        $this->setFlash('success', 'Deals section "' . htmlspecialchars($title) . '" updated successfully!');
        $this->redirect(url('admin/top-deals'));
    }

    /**
     * Delete custom deals section
     */
    public function delete(int $id): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
            return;
        }

        $section = $this->sectionModel->find($id);
        if ($section) {
            $title = $section['title'];
            $this->sectionModel->deleteSection($id);
            $this->setFlash('success', 'Deals section "' . htmlspecialchars($title) . '" deleted successfully!');
        } else {
            $this->setFlash('error', 'Section not found.');
        }

        $this->redirect(url('admin/top-deals'));
    }
}
