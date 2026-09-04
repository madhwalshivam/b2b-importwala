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

        $rawSections = $this->sectionModel->getAllSections();
        $sections = [];

        foreach ($rawSections as &$sec) {
            $secId = (int)$sec['id'];
            $sec['products'] = $this->sectionModel->getSectionProducts($secId, null, false);
            $key = !empty($sec['slug']) ? $sec['slug'] : $sec['section_key'];
            $sections[$key] = $sec;
        }
        unset($sec);

        // All active products for product selector
        $allProducts = $this->productModel->getAllActiveProducts();

        $promoSettings = [
            'badge'       => \App\Models\Setting::get('featured_promo_badge', 'SPECIAL OFFER'),
            'title'       => \App\Models\Setting::get('featured_promo_title', 'ImportWale Heavy-Duty Protection'),
            'description' => \App\Models\Setting::get('featured_promo_description', 'Heavy gauge stainless steel crash guards and all-weather body covers precision-fit for your electric scooter.'),
            'btn_text'    => \App\Models\Setting::get('featured_promo_btn_text', 'Shop Now'),
            'link'        => \App\Models\Setting::get('featured_promo_link', 'shop'),
            'image'       => \App\Models\Setting::get('featured_promo_image', '')
        ];

        return $this->render('admin/homepage_sections/index', [
            'sections'      => $sections,
            'rawSections'   => $rawSections,
            'allProducts'   => $allProducts,
            'promoSettings' => $promoSettings
        ]);
    }

    /**
     * Create a new custom section
     */
    public function store(): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
            return;
        }

        $title        = trim($this->request->input('title', ''));
        $slug         = trim($this->request->input('slug', ''));
        $subtitle     = trim($this->request->input('subtitle', ''));
        $maxProducts  = (int)$this->request->input('max_products', 0);
        $displayCount = (int)$this->request->input('homepage_display_count', 5);
        $sortOrder    = (int)$this->request->input('sort_order', 0);
        $status       = $this->request->input('status', 'active');

        if (empty($title)) {
            $this->setFlash('error', 'Section Title is required.');
            $this->redirect(url('admin/homepage-sections'));
            return;
        }

        $sectionId = $this->sectionModel->createSection([
            'title'                  => $title,
            'slug'                   => $slug,
            'subtitle'               => $subtitle,
            'max_products'           => $maxProducts,
            'homepage_display_count' => $displayCount,
            'sort_order'             => $sortOrder,
            'status'                 => $status
        ]);

        $this->setFlash('success', 'New homepage section "' . htmlspecialchars($title) . '" created successfully!');
        $this->redirect(url('admin/homepage-sections'));
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
        $title        = $this->request->input('title', $section['title']);
        $slug         = $this->request->input('slug', $section['slug']);
        $subtitle     = $this->request->input('subtitle', $section['subtitle']);
        $enabled      = $this->request->input('enabled', null);
        $status       = $this->request->input('status', null);
        $maxProducts  = (int)$this->request->input('max_products', $section['max_products']);
        $displayCount = (int)$this->request->input('homepage_display_count', $section['homepage_display_count'] ?? 5);
        $sortOrder    = (int)$this->request->input('sort_order', $section['sort_order']);

        $statusVal = 'inactive';
        if ($status !== null) {
            $statusVal = in_array(strtolower((string)$status), ['active', 'enabled', '1', 'true']) ? 'active' : 'inactive';
        } elseif ($enabled !== null) {
            $statusVal = (!empty($enabled) && $enabled !== '0' && $enabled !== 'false') ? 'active' : 'inactive';
        } elseif ($this->request->input('title') !== null) {
            $statusVal = 'inactive';
        } else {
            $statusVal = $section['status'];
        }

        // Update Section Config
        $this->sectionModel->updateSectionConfig($sectionId, [
            'title'                  => $title,
            'slug'                   => $slug,
            'subtitle'               => $subtitle,
            'status'                 => $statusVal,
            'max_products'           => $maxProducts,
            'homepage_display_count' => $displayCount,
            'sort_order'             => $sortOrder
        ]);

        // Selected product IDs in order
        $productIds = $this->request->input('product_ids', null);
        if ($productIds !== null) {
            if (is_string($productIds)) {
                $productIds = array_filter(explode(',', $productIds));
            }
            if (is_array($productIds)) {
                $this->sectionModel->saveSectionProducts($sectionId, $productIds);
            }
        }

        if ($this->request->isAjax() || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            $updatedSection = $this->sectionModel->findByKey((string)$sectionId);
            $updatedSection['products'] = $this->sectionModel->getSectionProducts($sectionId, null, false);
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
     * Delete custom section
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
            $this->setFlash('success', 'Homepage section "' . htmlspecialchars($title) . '" deleted successfully!');
        } else {
            $this->setFlash('error', 'Section not found.');
        }

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
            $words = array_filter(explode(' ', preg_replace('/\s+/', ' ', $q)));
            
            $whereWords = [];
            foreach ($words as $idx => $w) {
                $pName = 'w_name_' . $idx;
                $pSku = 'w_sku_' . $idx;
                $pCat = 'w_cat_' . $idx;
                $pBrand = 'w_brand_' . $idx;
                
                $whereWords[] = "(p.name LIKE :{$pName} OR p.sku LIKE :{$pSku} OR c.name LIKE :{$pCat} OR b.name LIKE :{$pBrand})";
                $searchTerm = '%' . $w . '%';
                $params[$pName] = $searchTerm;
                $params[$pSku] = $searchTerm;
                $params[$pCat] = $searchTerm;
                $params[$pBrand] = $searchTerm;
            }
            
            if (!empty($whereWords)) {
                $sql .= " AND " . implode(" AND ", $whereWords);
            }
            
            // Relevance sorting: Exact match > Title start match > Word boundary match > Title substring match > SKU match > Others
            $sql .= " ORDER BY (CASE 
                        WHEN p.name LIKE :rel_exact THEN 1
                        WHEN p.name LIKE :rel_start THEN 2
                        WHEN p.name REGEXP :rel_word THEN 3
                        WHEN p.name LIKE :rel_contain THEN 4
                        WHEN p.sku LIKE :rel_sku THEN 5
                        ELSE 6
                    END) ASC, p.id DESC LIMIT 30";
                    
            $params['rel_exact']   = $q;
            $params['rel_start']   = $q . '%';
            $params['rel_word']    = '(^|[[:space:]])' . preg_quote($q, '/') . '([[:space:]]|$)';
            $params['rel_contain'] = '%' . $q . '%';
            $params['rel_sku']     = '%' . $q . '%';
        } else {
            $sql .= " ORDER BY p.id DESC LIMIT 30";
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $resultData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->response->json([
            'success' => true,
            'items'   => array_map(function($p) {
                $priceVal = (($p['sale_price'] ?? 0) > 0) ? $p['sale_price'] : ($p['price'] ?? 0);
                $formattedPrice = function_exists('format_price') ? \format_price($priceVal) : '₹' . number_format((float)$priceVal, 2);
                $imgPath = $p['main_image'] ?? 'assets/images/placeholder.jpg';
                $imgUrl = function_exists('asset') ? \asset($imgPath) : $imgPath;
                return [
                    'id'         => (int)$p['id'],
                    'name'       => htmlspecialchars_decode($p['name'] ?? ''),
                    'slug'       => $p['slug'] ?? '',
                    'sku'        => $p['sku'] ?? '',
                    'price'      => $formattedPrice,
                    'main_image' => $imgUrl,
                    'stock'      => (int)($p['stock'] ?? 0)
                ];
            }, $resultData ?: [])
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

        $section['products'] = $this->sectionModel->getSectionProducts((int)$section['id'], (int)($section['homepage_display_count'] ?: 5));
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
