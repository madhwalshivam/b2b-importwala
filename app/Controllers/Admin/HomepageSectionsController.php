<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\HomeSection;
use App\Models\Product;

class HomepageSectionsController extends Controller {

    protected HomeSection $sectionModel;
    protected Product $productModel;

    public function __construct() {
        parent::__construct();
        $this->sectionModel = new HomeSection();
        $this->productModel = new Product();
    }

    /**
     * Main Admin View for Homepage Sections
     */
    public function index(): string {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
        }

        $rawSections = $this->sectionModel->getProductSections();
        $sections = [];

        foreach ($rawSections as $sec) {
            $secId = (int)$sec['id'];
            $sec['products'] = $this->sectionModel->getSectionProducts($secId);
            $sections[$sec['section_key']] = $sec;
        }

        // All active products for initial product selection dropdown
        $allProducts = $this->productModel->getAllActiveProducts();

        $promoSettings = [
            'badge'       => \App\Models\Setting::get('featured_promo_badge', 'SPECIAL OFFER'),
            'title'       => \App\Models\Setting::get('featured_promo_title', 'Mudsor Heavy-Duty EV Protection'),
            'description' => \App\Models\Setting::get('featured_promo_description', 'Heavy gauge stainless steel crash guards and all-weather body covers precision-fit for your electric scooter.'),
            'btn_text'    => \App\Models\Setting::get('featured_promo_btn_text', 'Shop Now'),
            'link'        => \App\Models\Setting::get('featured_promo_link', 'shop'),
            'image'       => \App\Models\Setting::get('featured_promo_image', '')
        ];

        return $this->render('admin/homepage_sections/index', [
            'sections'      => $sections,
            'allProducts'   => $allProducts,
            'promoSettings' => $promoSettings
        ]);
    }

    /**
     * Update section config & selected products (Single Form Submission or AJAX)
     */
    public function update(string $key): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            if ($this->request->isAjax() || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
                $this->response->json(['success' => false, 'message' => 'Unauthorized'], 403);
                return;
            }
            $this->redirect(url('admin/dashboard'));
        }

        $section = $this->sectionModel->findByKey($key);
        if (!$section) {
            if ($this->request->isAjax()) {
                $this->response->json(['success' => false, 'message' => 'Section not found'], 404);
                return;
            }
            $this->setFlash('error', 'Section not found');
            $this->redirect(url('admin/homepage-sections'));
        }

        $sectionId = (int)$section['id'];

        // Extract configuration parameters
        $title       = $this->request->input('title', $section['title']);
        $subtitle    = $this->request->input('subtitle', $section['subtitle']);
        $enabled     = $this->request->input('enabled', '0');
        $maxProducts = (int)$this->request->input('max_products', 8);

        // Update Section Config
        $this->sectionModel->updateSectionConfig($sectionId, [
            'title'        => $title,
            'subtitle'     => $subtitle,
            'status'       => !empty($enabled) && $enabled !== '0' && $enabled !== 'false' ? 'active' : 'inactive',
            'max_products' => $maxProducts
        ]);

        // Selected product IDs in order
        $productIds = $this->request->input('product_ids', []);
        if (is_string($productIds)) {
            $productIds = array_filter(explode(',', $productIds));
        }

        if (is_array($productIds)) {
            $this->sectionModel->saveSectionProducts($sectionId, $productIds);
        }

        if ($this->request->isAjax() || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            $updatedSection = $this->sectionModel->findByKey($key);
            $updatedSection['products'] = $this->sectionModel->getSectionProducts($sectionId);
            $this->response->json([
                'success' => true,
                'message' => 'Section updated successfully',
                'section' => $updatedSection
            ]);
            return;
        }

        $this->setFlash('success', 'Homepage section "' . htmlspecialchars($title) . '" updated successfully!');
        $this->redirect(url('admin/homepage-sections'));
    }

    /**
     * AJAX Search Products for Selector
     */
    public function searchProducts(): void {
        $q = trim($this->request->input('q', ''));
        
        $db = \App\Core\Database::getInstance();
        $params = [];
        
        $sql = "SELECT p.id, p.name, p.slug, p.sku, p.price, p.sale_price, p.main_image, p.stock
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.status = 'active'";
                
        if (!empty($q)) {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR c.name LIKE ? OR b.name LIKE ?)";
            $searchTerm = '%' . $q . '%';
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }
        
        $sql .= " GROUP BY p.id ORDER BY p.name ASC LIMIT 30";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $resultData = $stmt->fetchAll();

        $this->response->json([
            'success' => true,
            'items'   => array_map(function($p) {
                return [
                    'id'         => (int)$p['id'],
                    'name'       => $p['name'],
                    'slug'       => $p['slug'] ?? '',
                    'sku'        => $p['sku'],
                    'price'      => format_price($p['sale_price'] ?: $p['price']),
                    'main_image' => asset($p['main_image']),
                    'stock'      => (int)$p['stock']
                ];
            }, $resultData)
        ]);
    }

    /**
     * API: GET /api/homepage-sections (Returns all enabled sections with products)
     */
    public function apiIndex(): void {
        $sections = $this->sectionModel->getEnabledSectionsWithProducts();
        $this->response->json([
            'success' => true,
            'data'    => $sections
        ]);
    }

    /**
     * API: GET /api/homepage-sections/:key
     */
    public function apiShow(string $key): void {
        $section = $this->sectionModel->findByKey($key);
        if (!$section) {
            $this->response->json(['success' => false, 'message' => 'Section not found'], 404);
            return;
        }

        $section['products'] = $this->sectionModel->getSectionProducts((int)$section['id'], (int)$section['max_products']);
        $this->response->json([
            'success' => true,
            'data'    => $section
        ]);
    }

    /**
     * Update Featured Section Left Promo Card Settings
     */
    public function updatePromo(): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
            return;
        }

        $badge       = trim($_POST['featured_promo_badge'] ?? $this->request->input('featured_promo_badge', 'SPECIAL OFFER'));
        $title       = trim($_POST['featured_promo_title'] ?? $this->request->input('featured_promo_title', ''));
        $description = trim($_POST['featured_promo_description'] ?? $this->request->input('featured_promo_description', ''));
        $btnText     = trim($_POST['featured_promo_btn_text'] ?? $this->request->input('featured_promo_btn_text', 'Shop Now'));
        $link        = trim($_POST['featured_promo_link'] ?? $this->request->input('featured_promo_link', 'shop'));
        $imageUrl    = htmlspecialchars_decode(trim($_POST['image_url'] ?? $this->request->input('image_url', '')));

        if (!empty($_FILES['image_file']['name']) && ($_FILES['image_file']['error'] ?? 1) === UPLOAD_ERR_OK) {
            $uploaded = $this->uploadPromoImage($_FILES['image_file']);
            if ($uploaded) {
                $imageUrl = $uploaded;
            }
        }

        \App\Models\Setting::set('featured_promo_badge', $badge);
        \App\Models\Setting::set('featured_promo_title', $title);
        \App\Models\Setting::set('featured_promo_description', $description);
        \App\Models\Setting::set('featured_promo_btn_text', $btnText);
        \App\Models\Setting::set('featured_promo_link', $link);
        \App\Models\Setting::set('featured_promo_image', $imageUrl);

        $this->setFlash('success', 'Featured promo banner card updated successfully!');
        $this->redirect(url('admin/homepage-sections'));
    }

    private function uploadPromoImage(array $file): ?string {
        $allowedExtensions = ['svg', 'png', 'webp', 'jpg', 'jpeg'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            return null;
        }

        $uploadDir = dirname(__DIR__, 3) . '/public/uploads/banners';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $filename = 'promo_' . time() . '_' . substr(md5(uniqid()), 0, 8) . '.' . $extension;
        $targetPath = $uploadDir . '/' . $filename;

        if (@move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/banners/' . $filename;
        }

        return null;
    }
}
