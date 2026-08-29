<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ScooterModel;
use App\Helpers\Paginator;
use App\Services\CloudflareR2;

class ProductController extends Controller {
    protected Product $productModel;
    protected ProductImage $imageModel;

    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
        $this->imageModel   = new ProductImage();
    }

    public function index(): string {
        if (!Auth::hasPermission('products.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $page = (int)($this->request->input('page', 1));
        $perPage = (int)($this->request->input('per_page', 20));
        $search = $this->request->input('search', '');
        $status = $this->request->input('status', '');

        $whereConditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if (!empty($status)) {
            $whereConditions[] = "p.status = ?";
            $params[] = $status;
        }

        $whereSql = implode(' AND ', $whereConditions);

        // Fetch products with category name join
        $db = \App\Core\Database::getInstance();
        $countStmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE {$whereSql} 
                ORDER BY p.id DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        $paginator = new Paginator($total, $perPage, $page, url('admin/products'), $_GET);

        return $this->render('admin/products/index', [
            'products' => $items,
            'paginator' => $paginator,
            'search' => $search,
            'status' => $status
        ]);
    }

    public function create(): string {
        if (!Auth::hasPermission('products.add')) {
            $this->redirect(url('admin/products'));
        }

        $categoryModel = new Category();
        $brandModel = new Brand();
        $scooterModel = new ScooterModel();

        return $this->render('admin/products/create', [
            'categories' => $categoryModel->all('name ASC'),
            'brands' => $brandModel->all('name ASC'),
            'scooterModels' => $scooterModel->getAllWithBrand(),
            'selectedCategoryIds' => [],
            'selectedBrandIds' => []
        ]);
    }

    public function store(): void {
        if (!Auth::hasPermission('products.add')) {
            $this->redirect(url('admin/products'));
        }

        $name = htmlspecialchars_decode($this->request->input('name'));
        $sku = trim($this->request->input('sku'));
        $price = (float)$this->request->input('price', 0);
        $salePrice = $this->request->input('sale_price') ? (float)$this->request->input('sale_price') : null;
        
        $categoryIds = $_POST['categories'] ?? [];
        $brandIds = $_POST['brands'] ?? [];

        $inputCatId = $this->request->input('category_id');
        $primaryCategoryId = !empty($inputCatId) ? (int)$inputCatId : (!empty($categoryIds[0]) ? (int)$categoryIds[0] : 0);
        if ($primaryCategoryId <= 0) {
            $catModel = new Category();
            $allCats = $catModel->all('id ASC');
            $firstCategory = $allCats[0] ?? null;
            if ($firstCategory) {
                $primaryCategoryId = (int)$firstCategory['id'];
            }
        }
        if ($primaryCategoryId > 0 && !in_array($primaryCategoryId, $categoryIds)) {
            $categoryIds[] = $primaryCategoryId;
        }

        $inputBrandId = $this->request->input('brand_id');
        $primaryBrandId = !empty($inputBrandId) ? (int)$inputBrandId : (!empty($brandIds[0]) ? (int)$brandIds[0] : null);

        $stock = (int)$this->request->input('stock', 0);
        $description = trim($_POST['description'] ?? '');

        // Handle Main Image
        $mainImage = trim(htmlspecialchars_decode($this->request->input('main_image_url', ''))) ?: '/assets/images/placeholder.jpg';
        if (!empty($_FILES['main_image']['tmp_name']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $uploadedUrl = $this->uploadImageFile($_FILES['main_image'], 'products');
            if (!empty($uploadedUrl)) {
                $mainImage = $uploadedUrl;
            }
        }

        $slug = slugify($name);
        $origSlug = $slug;
        $suffix = 1;
        while ($this->productModel->findBy('slug', $slug)) {
            $suffix++;
            $slug = $origSlug . '-' . $suffix;
        }

        // Handle Product Demo Video & Covers
        $videoUrl = trim($this->request->input('video_url', ''));
        $videoThumbnail = '';
        $autoVideoThumbnail = '';

        $uploadDir = __DIR__ . '/../../../public/uploads/videos/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        // 1. Video File Upload if provided
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $baseName = 'prod_vid_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['video_file']['name']);
            $videoAbsPath = $uploadDir . $baseName;
            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $videoAbsPath)) {
                $videoUrl = '/uploads/videos/' . $baseName;

                // Extract frame at 1-sec mark (FFmpeg / Canvas Base64 fallback)
                $base64Thumb = $_POST['auto_video_thumbnail_base64'] ?? '';
                $autoThumbName = \App\Helpers\VideoThumbnailHelper::processAutoThumbnail($videoAbsPath, $base64Thumb, $uploadDir, pathinfo($baseName, PATHINFO_FILENAME));
                if (!empty($autoThumbName)) {
                    $autoVideoThumbnail = '/uploads/videos/' . $autoThumbName;
                }
            }
        }

        // 2. YouTube auto thumbnail if URL is YouTube
        if (empty($autoVideoThumbnail) && !empty($videoUrl) && class_exists('\App\Models\HomepageVideo')) {
            $autoVideoThumbnail = \App\Models\HomepageVideo::getYouTubeThumbnail($videoUrl);
        }

        // 3. Manual Cover Photo Upload if provided
        if (isset($_FILES['video_thumbnail']) && $_FILES['video_thumbnail']['error'] === UPLOAD_ERR_OK) {
            $fileName = 'prod_thumb_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['video_thumbnail']['name']);
            if (move_uploaded_file($_FILES['video_thumbnail']['tmp_name'], $uploadDir . $fileName)) {
                $videoThumbnail = '/uploads/videos/' . $fileName;
            }
        } elseif (!empty($_POST['video_thumbnail_url'])) {
            $videoThumbnail = trim($_POST['video_thumbnail_url']);
        }

        $subcategoryId = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
        $basePrice = isset($_POST['base_price']) && $_POST['base_price'] !== '' ? (float)$_POST['base_price'] : $price;
        $moq = isset($_POST['moq']) && (int)$_POST['moq'] > 0 ? (int)$_POST['moq'] : 1;
        $totalSold = isset($_POST['total_sold']) && (int)$_POST['total_sold'] >= 0 ? (int)$_POST['total_sold'] : 0;
        $isBestSeller = isset($_POST['is_best_seller']) ? 1 : 0;
        $isNew = isset($_POST['is_new']) || isset($_POST['is_new_arrival']) ? 1 : 0;

        $productId = $this->productModel->insert([
            'name'                 => $name,
            'slug'                 => $slug,
            'sku'                  => $sku,
            'barcode'              => $this->request->input('barcode'),
            'hsn_code'             => $this->request->input('hsn_code'),
            'category_id'          => $primaryCategoryId,
            'subcategory_id'       => $subcategoryId,
            'brand_id'             => $primaryBrandId,
            'price'                => $price,
            'sale_price'           => $salePrice,
            'base_price'           => $basePrice,
            'moq'                  => $moq,
            'total_sold'           => $totalSold,
            'sales_count'          => $totalSold,
            'tax_percent'          => (float)$this->request->input('tax_percent', 18),
            'stock'                => $stock,
            'main_image'           => $mainImage,
            'video_url'            => $videoUrl,
            'video_thumbnail'      => $videoThumbnail,
            'auto_video_thumbnail' => $autoVideoThumbnail,
            'pdf_manual'           => $this->request->input('pdf_manual'),
            'description'          => $description,
            'warranty_info'        => $this->request->input('warranty_info'),
            'tags'                 => $this->request->input('tags'),
            'meta_title'           => $name,
            'meta_description'     => $this->request->input('meta_description'),
            'is_featured'          => isset($_POST['is_featured']) ? 1 : 0,
            'is_best_seller'       => $isBestSeller,
            'is_new'               => $isNew,
            'is_new_arrival'       => $isNew,
            'is_free_shipping'      => isset($_POST['is_free_shipping']) ? 1 : 0,
            'is_flash_sale'        => isset($_POST['is_flash_sale']) ? 1 : 0,
            'status'               => $this->request->input('status', 'active'),
            // OEM & Warranty fields (shown in Compare page)
            'warranty_months'      => (int)$this->request->input('warranty_months', 12),
            'oem_price'            => $this->request->input('oem_price') !== '' && $this->request->input('oem_price') !== null
                                        ? (float)$this->request->input('oem_price') : null,
            'oem_warranty_months'  => (int)$this->request->input('oem_warranty_months', 6),
            'oem_material'         => trim($this->request->input('oem_material', 'Standard Steel / Plastic')),
        ]);

        // Sync Categories & Brands
        $this->productModel->syncProductCategories($productId, $categoryIds);
        $this->productModel->syncProductBrands($productId, $brandIds);

        // Save Wholesale Tiered Prices
        $this->saveTieredPrices($productId, $_POST['tier_min_qty'] ?? [], $_POST['tier_max_qty'] ?? [], $_POST['tier_unit_price'] ?? []);

        // Main Image into product_images as primary
        $this->imageModel->insert([
            'product_id' => $productId,
            'image_url'  => $mainImage,
            'sort_order' => 0,
            'is_primary' => 1,
        ]);

        // Additional Gallery URLs if provided
        $galleryUrls = trim($_POST['gallery_urls'] ?? '');
        if (!empty($galleryUrls)) {
            $lines = array_filter(array_map('trim', explode("\n", $galleryUrls)));
            $gIdx = 1;
            foreach ($lines as $gUrl) {
                if (!empty($gUrl)) {
                    $this->imageModel->insert([
                        'product_id' => $productId,
                        'image_url'  => $gUrl,
                        'sort_order' => $gIdx++,
                        'is_primary' => 0,
                    ]);
                }
            }
        }

        // Multiple Gallery Image Files Uploaded in store()
        if (!empty($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
            foreach ($_FILES['gallery_images']['name'] as $idx => $fileName) {
                if (!empty($_FILES['gallery_images']['tmp_name'][$idx]) && $_FILES['gallery_images']['error'][$idx] === UPLOAD_ERR_OK) {
                    $fileArr = [
                        'name' => $_FILES['gallery_images']['name'][$idx],
                        'type' => $_FILES['gallery_images']['type'][$idx],
                        'tmp_name' => $_FILES['gallery_images']['tmp_name'][$idx],
                        'error' => $_FILES['gallery_images']['error'][$idx],
                        'size' => $_FILES['gallery_images']['size'][$idx]
                    ];
                    $uUrl = $this->uploadImageFile($fileArr, 'products');
                    if (!empty($uUrl)) {
                        $this->imageModel->add($productId, $uUrl);
                    }
                }
            }
        }

        // Process Variations JSON if provided
        $variationsJson = trim($_POST['variations_json'] ?? '');
        if (!empty($variationsJson)) {
            $types = json_decode($variationsJson, true);
            if (is_array($types) && !empty($types)) {
                $this->productModel->saveVariationTypes($productId, $types);
            }
        }

        activity_log('Create Product', 'Products', $productId, "Created product: {$name} (SKU: {$sku})");

        // Flush Cache
        try { \App\Infrastructure\Cache\CacheManager::getInstance()->flush(); } catch (\Throwable $e) {}

        $this->setFlash('success', 'Product created successfully!');
        $this->redirect(url('admin/products/edit/' . $productId));
    }

    public function edit(int $id): string {
        if (!Auth::hasPermission('products.edit')) {
            $this->redirect(url('admin/products'));
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            $this->setFlash('error', 'Product not found.');
            $this->redirect(url('admin/products'));
        }

        $categoryModel = new Category();
        $brandModel = new Brand();
        $scooterModel = new ScooterModel();

        $compatibles = $this->productModel->getCompatibleScooters($id);
        $selectedModelIds = array_column($compatibles, 'id');
        $selectedCategoryIds = $this->productModel->getProductCategoryIds($id);
        if ($product['category_id'] && !in_array($product['category_id'], $selectedCategoryIds)) {
            $selectedCategoryIds[] = (int)$product['category_id'];
        }

        $selectedBrandIds = $this->productModel->getProductBrandIds($id);
        if ($product['brand_id'] && !in_array($product['brand_id'], $selectedBrandIds)) {
            $selectedBrandIds[] = (int)$product['brand_id'];
        }

        $galleryImages    = $this->imageModel->getByProduct($id);
        $frequentlyBought = $this->productModel->getRelatedProducts($id, 'frequently_bought', 10);

        $db = \App\Core\Database::getInstance();
        $tierStmt = $db->prepare("SELECT * FROM tiered_prices WHERE product_id = ? ORDER BY min_qty ASC");
        $tierStmt->execute([$id]);
        $tieredPrices = $tierStmt->fetchAll();

        $subcatModel = new \App\Models\Subcategory();
        $subcategories = !empty($product['category_id']) ? $subcatModel->getByCategoryId((int)$product['category_id']) : [];

        return $this->render('admin/products/edit', [
            'product'             => $product,
            'categories'          => $categoryModel->all('name ASC'),
            'subcategories'       => $subcategories,
            'tieredPrices'        => $tieredPrices,
            'brands'              => $brandModel->all('name ASC'),
            'scooterModels'       => $scooterModel->getAllWithBrand(),
            'selectedModelIds'    => $selectedModelIds,
            'selectedCategoryIds' => $selectedCategoryIds,
            'selectedBrandIds'    => $selectedBrandIds,
            'galleryImages'       => $galleryImages,
            'frequentlyBought'    => $frequentlyBought
        ]);
    }

    public function update(int $id): void {
        if (!Auth::hasPermission('products.edit')) {
            $this->redirect(url('admin/products'));
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            $this->redirect(url('admin/products'));
        }

        $name = htmlspecialchars_decode($this->request->input('name'));
        $sku = trim($this->request->input('sku'));
        $price = (float)$this->request->input('price', 0);
        $salePrice = $this->request->input('sale_price') ? (float)$this->request->input('sale_price') : null;
        
        $categoryIds = $_POST['categories'] ?? [];
        $brandIds = $_POST['brands'] ?? [];

        $inputCatId = $this->request->input('category_id');
        $primaryCategoryId = !empty($inputCatId) ? (int)$inputCatId : (!empty($categoryIds[0]) ? (int)$categoryIds[0] : (int)($product['category_id'] ?? 0));
        if ($primaryCategoryId <= 0) {
            $catModel = new Category();
            $allCats = $catModel->all('id ASC');
            $firstCategory = $allCats[0] ?? null;
            if ($firstCategory) {
                $primaryCategoryId = (int)$firstCategory['id'];
            }
        }
        if ($primaryCategoryId > 0 && !in_array($primaryCategoryId, $categoryIds)) {
            $categoryIds[] = $primaryCategoryId;
        }

        $inputBrandId = $this->request->input('brand_id');
        $primaryBrandId = !empty($inputBrandId) ? (int)$inputBrandId : (!empty($brandIds[0]) ? (int)$brandIds[0] : (!empty($product['brand_id']) ? (int)$product['brand_id'] : null));

        // RAW DESCRIPTION FROM $_POST to avoid sanitization corruption
        $description = trim($_POST['description'] ?? '');

        // Handle Product Demo Video & Cover Thumbnails
        $videoUrl = trim($this->request->input('video_url', $product['video_url'] ?? ''));
        $videoThumbnail = $product['video_thumbnail'] ?? '';
        $autoVideoThumbnail = $product['auto_video_thumbnail'] ?? '';

        $uploadDir = __DIR__ . '/../../../public/uploads/videos/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        // 1. Re-upload video file
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $baseName = 'prod_vid_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['video_file']['name']);
            $videoAbsPath = $uploadDir . $baseName;
            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $videoAbsPath)) {
                $videoUrl = '/uploads/videos/' . $baseName;

                // Auto extract 1-second frame thumbnail on new video re-upload
                $base64Thumb = $_POST['auto_video_thumbnail_base64'] ?? '';
                $autoThumbName = \App\Helpers\VideoThumbnailHelper::processAutoThumbnail($videoAbsPath, $base64Thumb, $uploadDir, pathinfo($baseName, PATHINFO_FILENAME));
                if (!empty($autoThumbName)) {
                    $autoVideoThumbnail = '/uploads/videos/' . $autoThumbName;
                }
            }
        }

        // 2. YouTube auto thumbnail if URL is YouTube
        if (!empty($videoUrl) && class_exists('\App\Models\HomepageVideo')) {
            $ytThumb = \App\Models\HomepageVideo::getYouTubeThumbnail($videoUrl);
            if (!empty($ytThumb)) {
                $autoVideoThumbnail = $ytThumb;
            }
        }

        // 3. Manual Cover Photo Upload if provided
        if (isset($_FILES['video_thumbnail']) && $_FILES['video_thumbnail']['error'] === UPLOAD_ERR_OK) {
            $fileName = 'prod_thumb_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['video_thumbnail']['name']);
            if (move_uploaded_file($_FILES['video_thumbnail']['tmp_name'], $uploadDir . $fileName)) {
                $videoThumbnail = '/uploads/videos/' . $fileName;
            }
        } elseif (isset($_POST['video_thumbnail_url']) && trim($_POST['video_thumbnail_url']) !== '') {
            $videoThumbnail = trim($_POST['video_thumbnail_url']);
        }

        $subcategoryId = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
        $basePrice = isset($_POST['base_price']) && $_POST['base_price'] !== '' ? (float)$_POST['base_price'] : $price;
        $moq = isset($_POST['moq']) && (int)$_POST['moq'] > 0 ? (int)$_POST['moq'] : 1;
        $totalSold = isset($_POST['total_sold']) && (int)$_POST['total_sold'] >= 0 ? (int)$_POST['total_sold'] : (int)($product['total_sold'] ?? 0);
        $isBestSeller = isset($_POST['is_best_seller']) ? 1 : 0;
        $isNew = isset($_POST['is_new']) || isset($_POST['is_new_arrival']) ? 1 : 0;

        $data = [
            'name'                 => $name,
            'sku'                  => $sku,
            'barcode'              => $this->request->input('barcode'),
            'hsn_code'             => $this->request->input('hsn_code'),
            'category_id'          => $primaryCategoryId,
            'subcategory_id'       => $subcategoryId,
            'brand_id'             => $primaryBrandId,
            'price'                => $price,
            'sale_price'           => $salePrice,
            'base_price'           => $basePrice,
            'moq'                  => $moq,
            'total_sold'           => $totalSold,
            'sales_count'          => $totalSold,
            'tax_percent'          => (float)$this->request->input('tax_percent', 18),
            'stock'                => (int)$this->request->input('stock', 0),
            'video_url'            => $videoUrl,
            'video_thumbnail'      => $videoThumbnail,
            'auto_video_thumbnail' => $autoVideoThumbnail,
            'description'          => $description,
            'warranty_info'        => $this->request->input('warranty_info'),
            'tags'                 => $this->request->input('tags'),
            'meta_title'           => $name,
            'meta_description'     => $this->request->input('meta_description'),
            'is_featured'          => isset($_POST['is_featured']) ? 1 : 0,
            'is_best_seller'       => $isBestSeller,
            'is_new'               => $isNew,
            'is_new_arrival'       => $isNew,
            'is_free_shipping'      => isset($_POST['is_free_shipping']) ? 1 : 0,
            'is_flash_sale'        => isset($_POST['is_flash_sale']) ? 1 : 0,
            'status'               => $this->request->input('status', 'active'),
            // OEM & Warranty fields (shown in Compare page)
            'warranty_months'      => (int)$this->request->input('warranty_months', 12),
            'oem_price'            => $this->request->input('oem_price') !== '' && $this->request->input('oem_price') !== null
                                        ? (float)$this->request->input('oem_price') : null,
            'oem_warranty_months'  => (int)$this->request->input('oem_warranty_months', 6),
            'oem_material'         => trim($this->request->input('oem_material', 'Standard Steel / Plastic')),
        ];

        // Handle Main Image: 1. Uploaded File or 2. Direct Image URL
        $imageUrl = trim($this->request->input('main_image_url', ''));
        if (!empty($_FILES['main_image']['tmp_name']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $uploadedUrl = $this->uploadImageFile($_FILES['main_image'], 'products');
            if (!empty($uploadedUrl)) {
                $data['main_image'] = $uploadedUrl;
            }
        } elseif (!empty($imageUrl)) {
            $data['main_image'] = $imageUrl;
        }

        $this->productModel->update($id, $data);
        $this->productModel->syncProductCategories($id, $categoryIds);
        $this->productModel->syncProductBrands($id, $brandIds);

        // Save Wholesale Tiered Prices
        $this->saveTieredPrices($id, $_POST['tier_min_qty'] ?? [], $_POST['tier_max_qty'] ?? [], $_POST['tier_unit_price'] ?? []);

        // 1. Handle Primary Image Radio selection from single image section
        $primaryImageId = (int)($this->request->input('primary_image_id', 0));
        if ($primaryImageId > 0) {
            $this->imageModel->setPrimary($primaryImageId, $id);
        }

        // 2. Handle New Gallery URLs pasted in single form
        $galleryUrls = trim($_POST['gallery_urls'] ?? '');
        if (!empty($galleryUrls)) {
            $lines = array_filter(array_map('trim', explode("\n", $galleryUrls)));
            foreach ($lines as $gUrl) {
                if (!empty($gUrl)) {
                    $this->imageModel->add($id, $gUrl);
                }
            }
        }

        // 3. Handle Multiple Image Files Uploaded in single form
        if (!empty($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
            foreach ($_FILES['gallery_images']['name'] as $idx => $fileName) {
                if (!empty($_FILES['gallery_images']['tmp_name'][$idx]) && $_FILES['gallery_images']['error'][$idx] === UPLOAD_ERR_OK) {
                    $fileArr = [
                        'name' => $_FILES['gallery_images']['name'][$idx],
                        'type' => $_FILES['gallery_images']['type'][$idx],
                        'tmp_name' => $_FILES['gallery_images']['tmp_name'][$idx],
                        'error' => $_FILES['gallery_images']['error'][$idx],
                        'size' => $_FILES['gallery_images']['size'][$idx]
                    ];
                    $uUrl = $this->uploadImageFile($fileArr, 'products');
                    if (!empty($uUrl)) {
                        $this->imageModel->add($id, $uUrl);
                    }
                }
            }
        }

        // Save Frequently Bought Products
        $frequentlyBoughtIds = $_POST['frequently_bought'] ?? $this->request->input('frequently_bought', []);
        if (is_string($frequentlyBoughtIds)) {
            $frequentlyBoughtIds = array_filter(explode(',', $frequentlyBoughtIds));
        }
        $this->productModel->saveRelatedProducts($id, (array)$frequentlyBoughtIds, 'frequently_bought');

        activity_log('Update Product', 'Products', $id, "Updated product: {$name}");

        // Flush Cache
        try { \App\Infrastructure\Cache\CacheManager::getInstance()->flush(); } catch (\Throwable $e) {}

        $this->setFlash('success', 'Product saved successfully!');
        $this->redirect(url('admin/products/edit/' . $id));
    }

    public function delete(int $id): void {
        if (!Auth::hasPermission('products.delete')) {
            $this->redirect(url('admin/products'));
        }

        $product = $this->productModel->find($id);
        if ($product) {
            $this->productModel->delete($id);
            activity_log('Delete Product', 'Products', $id, "Deleted product ID: {$id}");
            try { \App\Infrastructure\Cache\CacheManager::getInstance()->flush(); } catch (\Throwable $e) {}
            $this->setFlash('success', 'Product deleted successfully.');
        }
        $this->redirect(url('admin/products'));
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Gallery Image AJAX Methods
    // ─────────────────────────────────────────────────────────────────────────────

    private function jsonResponse(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function uploadImageFile(array $file, string $folder = 'products'): ?string {
        if (empty($file['tmp_name'])) return null;
        try {
            if (class_exists('\App\Services\CloudflareR2')) {
                $r2 = new CloudflareR2();
                $url = $r2->upload($file);
                if (!empty($url)) return $url;
            }
        } catch (\Throwable $e) {}

        // Fallback to local
        $uploadDir = __DIR__ . '/../../../public/uploads/' . $folder . '/';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);
        $ext  = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION)) ?: 'jpg';
        $name = time() . '_' . uniqid() . '.' . $ext;
        if (@move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
            return '/uploads/' . $folder . '/' . $name;
        }
        return null;
    }

    public function galleryUpload(int $productId): void {
        if (!Auth::hasPermission('products.edit')) $this->jsonResponse(['error' => 'Forbidden'], 403);
        $product = $this->productModel->find($productId);
        if (!$product) $this->jsonResponse(['error' => 'Product not found'], 404);

        // Count existing images
        $existing = $this->imageModel->getByProduct($productId);
        if (count($existing) >= 8) $this->jsonResponse(['error' => 'Maximum 8 images allowed per product'], 422);

        $url = null;
        // File upload
        if (!empty($_FILES['image']['tmp_name'])) {
            $url = $this->uploadImageFile($_FILES['image'], 'gallery');
        }
        // URL input
        if (!$url) {
            $url = trim(htmlspecialchars_decode($_POST['image_url'] ?? ''));
        }
        if (!$url) $this->jsonResponse(['error' => 'No image provided'], 422);

        $isPrimary = (count($existing) === 0) ? 1 : 0;
        $imageId = $this->imageModel->insert([
            'product_id' => $productId,
            'image_url'  => $url,
            'sort_order' => count($existing),
            'is_primary' => $isPrimary,
        ]);
        if ($isPrimary) {
            $this->productModel->update($productId, ['main_image' => $url]);
        }

        $this->jsonResponse(['success' => true, 'image_id' => $imageId, 'image_url' => $url, 'is_primary' => $isPrimary]);
    }

    public function galleryDelete(int $imageId): void {
        if (!Auth::hasPermission('products.edit')) $this->jsonResponse(['error' => 'Forbidden'], 403);
        $url = $this->imageModel->delete($imageId);
        $this->jsonResponse(['success' => true, 'deleted_url' => $url]);
    }

    public function gallerySetPrimary(int $imageId): void {
        if (!Auth::hasPermission('products.edit')) $this->jsonResponse(['error' => 'Forbidden'], 403);
        // Get product_id from image
        $db = \App\Core\Database::getInstance();
        $row = $db->prepare("SELECT product_id FROM product_images WHERE id = ?")->execute([$imageId]);
        $imgRow = $db->prepare("SELECT product_id FROM product_images WHERE id = ?");
        $imgRow->execute([$imageId]);
        $img = $imgRow->fetch();
        if (!$img) $this->jsonResponse(['error' => 'Image not found'], 404);
        $this->imageModel->setPrimary($imageId, (int)$img['product_id']);
        $this->jsonResponse(['success' => true]);
    }

    public function galleryReorder(int $productId): void {
        if (!Auth::hasPermission('products.edit')) $this->jsonResponse(['error' => 'Forbidden'], 403);
        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids)) $ids = json_decode($ids, true) ?: [];
        $this->imageModel->reorder(array_map('intval', $ids));
        $this->jsonResponse(['success' => true]);
    }

    /**
     * Save wholesale tiered volume prices for product
     */
    protected function saveTieredPrices(int $productId, array $minQtys, array $maxQtys, array $unitPrices): void {
        $db = \App\Core\Database::getInstance();
        $db->prepare("DELETE FROM tiered_prices WHERE product_id = ?")->execute([$productId]);
        
        if (empty($minQtys)) return;

        $stmt = $db->prepare("INSERT INTO tiered_prices (product_id, min_qty, max_qty, unit_price) VALUES (?, ?, ?, ?)");
        foreach ($minQtys as $idx => $minQty) {
            $min = (int)$minQty;
            $max = isset($maxQtys[$idx]) && $maxQtys[$idx] !== '' ? (int)$maxQtys[$idx] : null;
            $unit = (float)($unitPrices[$idx] ?? 0);
            if ($min > 0 && $unit > 0) {
                $stmt->execute([$productId, $min, $max, $unit]);
            }
        }
    }

    public function toggleFlag(): void {
        if (!Auth::hasPermission('products.edit')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }

        $id = (int)($this->request->input('id', 0));
        $field = trim($this->request->input('field', ''));

        if ($id <= 0 || !in_array($field, ['is_best_seller', 'is_new', 'is_new_arrival', 'is_free_shipping', 'status'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid arguments']);
            exit;
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        $newVal = 0;
        if ($field === 'is_best_seller') {
            $newVal = !empty($product['is_best_seller']) ? 0 : 1;
            $this->productModel->update($id, ['is_best_seller' => $newVal]);
        } elseif ($field === 'is_new' || $field === 'is_new_arrival') {
            $newVal = (!empty($product['is_new']) || !empty($product['is_new_arrival'])) ? 0 : 1;
            $this->productModel->update($id, ['is_new' => $newVal, 'is_new_arrival' => $newVal]);
        } elseif ($field === 'is_free_shipping') {
            $newVal = !empty($product['is_free_shipping']) ? 0 : 1;
            $this->productModel->update($id, ['is_free_shipping' => $newVal]);
        }

        // Immediate Cache Flush
        try { \App\Infrastructure\Cache\CacheManager::getInstance()->flush(); } catch (\Throwable $e) {}

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $id, 'field' => $field, 'newValue' => $newVal]);
        exit;
    }
}
