<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Core\PerfDebug;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Models\ProductVariant;
use App\Models\ProductSpecification;
use App\Models\ProductImage;
use App\Models\Setting;

class ProductDetailController extends BaseController
{
    private ProductRepository $productRepo;
    private CategoryRepository $categoryRepo;
    private ProductVariant $variantModel;
    private ProductSpecification $specModel;
    private ProductImage $imageModel;

    public function __construct()
    {
        parent::__construct();
        $this->productRepo = new ProductRepository();
        $this->categoryRepo = new CategoryRepository();
        $this->variantModel = new ProductVariant();
        $this->specModel = new ProductSpecification();
        $this->imageModel = new ProductImage();
    }

    public function show(string $slug, ?string $variantCode = null): void
    {
        PerfDebug::bootIfRequested();
        $reqStart = microtime(true);

        $selectedVariantCode = $variantCode ?: ($_GET['variant'] ?? null);

        $t0 = microtime(true);
        $product = $this->productRepo->findBySlug($slug);
        if (!$product && is_numeric($slug)) {
            $product = $this->productRepo->findById((int) $slug);
        }
        PerfDebug::mark('findBySlug', $t0);

        if (!$product || ($product['status'] ?? 'active') !== 'active') {
            http_response_code(404);
            echo "Product Not Found";
            return;
        }

        $productId = (int) $product['id'];

        // Session-dependent reads first, then release lock before heavy DB / visual work.
        $t0 = microtime(true);
        $cartWishlistState = function_exists('get_cart_and_wishlist_state')
            ? get_cart_and_wishlist_state()
            : ['wishlist_ids' => [], 'wishlist_count' => 0, 'cart_ids' => [], 'cart_count' => 0];
        if (!isset($cartWishlistState['wishlist_product_ids'])) {
            $cartWishlistState['wishlist_product_ids'] = $cartWishlistState['wishlist_ids'] ?? [];
        }
        $csrfToken = '';
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            $csrfToken = (string) $_SESSION['csrf_token'];
            session_write_close();
        }
        PerfDebug::mark('session_cart_wishlist_close', $t0);

        // 1. Fetch Variants (batched attributes — no N+1)
        $t0 = microtime(true);
        $variants = $this->variantModel->getByProduct($productId, true);
        PerfDebug::mark('variants', $t0);

        // 2. Fetch Specifications
        $t0 = microtime(true);
        $specifications = $this->specModel->getByProduct($productId);
        PerfDebug::mark('specifications', $t0);

        // 3. Build Merged Deduplicated Gallery Images Array
        $t0 = microtime(true);
        $galleryImages = [];
        $mainImg = !empty($product['main_image']) ? asset($product['main_image']) : asset('assets/images/placeholder.jpg');
        if ($mainImg) {
            $galleryImages[] = $mainImg;
        }

        $dbImages = $this->imageModel->getByProduct($productId);
        foreach ($dbImages as $img) {
            $u = asset($img['image_url'] ?: $img['image_path']);
            if ($u && !in_array($u, $galleryImages)) {
                $galleryImages[] = $u;
            }
        }
        PerfDebug::mark('gallery_images', $t0);

        // 4. Compute Dynamic Starting Prices for Dual Modes
        $baseWholesale = (float) ($product['price'] ?? 0);
        $baseOnePiece = !empty($product['sale_price']) ? (float) $product['sale_price'] : $baseWholesale;

        $wholesalePrices = [$baseWholesale];
        $onePiecePrices = [$baseOnePiece];

        foreach ($variants as $v) {
            if ((float) $v['wholesale_price'] > 0) {
                $wholesalePrices[] = (float) $v['wholesale_price'];
            }
            if ((float) $v['one_piece_price'] > 0) {
                $onePiecePrices[] = (float) $v['one_piece_price'];
            }
        }

        $minWholesale = min(array_filter($wholesalePrices, fn($p) => $p > 0) ?: [0]);
        $minOnePiece = min(array_filter($onePiecePrices, fn($p) => $p > 0) ?: [0]);

        // 5. Visually Similar & Related Products (cached; never indexes images on request)
        $t0 = microtime(true);
        $visualService = new \App\Services\VisualSearchService();
        $visuallySimilar = $visualService->searchByProductId($productId, 8);
        PerfDebug::mark('visual_similar', $t0);

        $t0 = microtime(true);
        $categories = $this->categoryRepo->getTree();
        PerfDebug::mark('categories_tree', $t0);

        $t0 = microtime(true);
        $relatedProducts = $this->productRepo->getByCategory((int) ($product['category_id'] ?? 0), 12);

        // Prefer subcategory peers when available (exclude current product)
        $subcatId = (int) ($product['subcategory_id'] ?? 0);
        if ($subcatId > 0) {
            try {
                $db = \App\Core\Database::getInstance();
                $stmt = $db->prepare("
                    SELECT * FROM products
                    WHERE subcategory_id = ? AND status = 'active' AND id <> ?
                    ORDER BY is_featured DESC, id DESC
                    LIMIT 12
                ");
                $stmt->execute([$subcatId, $productId]);
                $subRelated = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                if (!empty($subRelated)) {
                    $relatedProducts = $subRelated;
                }
            } catch (\Throwable $e) {
            }
        }
        if (!empty($relatedProducts)) {
            $relatedProducts = array_values(array_filter($relatedProducts, static function ($p) use ($productId) {
                return (int) ($p['id'] ?? 0) !== $productId;
            }));
        }
        PerfDebug::mark('related_products', $t0);

        // Image-search arrival: load "Similar to your photo" from session token
        $fromImageSearch = (($_GET['from'] ?? '') === 'image_search');
        $imageSearchSid = preg_replace('/[^a-f0-9]/', '', strtolower((string) ($_GET['sid'] ?? ''))) ?? '';
        $imageSearchSimilar = null;
        if ($fromImageSearch && $imageSearchSid !== '') {
            try {
                $imageSearchSimilar = (new \App\Services\ImageSearchSessionService())
                    ->getActiveProducts($imageSearchSid, $productId, 12);
            } catch (\Throwable $e) {
                $imageSearchSimilar = null;
            }
        }

        $initialVariantId = isset($_GET['variant_id']) ? (int) $_GET['variant_id'] : 0;

        // 6. WhatsApp Number & Template Settings (cached)
        $t0 = microtime(true);
        $whatsappNumber = preg_replace('/[^0-9]/', '', Setting::get('whatsapp_business_number') ?? '919540317079');
        PerfDebug::mark('settings_whatsapp', $t0);

        // 7. Fetch Tiered Volume Pricing (Product level + Variant level)
        $t0 = microtime(true);
        $db = \App\Core\Database::getInstance();
        // Prefer already-loaded tiers from getProductWithDetails when present
        $allTiers = $product['tiered_prices'] ?? null;
        if (!is_array($allTiers)) {
            $allTiersStmt = $db->prepare("SELECT * FROM tiered_prices WHERE product_id = ? ORDER BY min_qty ASC");
            $allTiersStmt->execute([$productId]);
            $allTiers = $allTiersStmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        $productTiers = [];
        $variantTiersMap = [];

        foreach ($allTiers as $t) {
            if (empty($t['variant_id'])) {
                $productTiers[] = $t;
            } else {
                $vId = (int) $t['variant_id'];
                if (!isset($variantTiersMap[$vId])) {
                    $variantTiersMap[$vId] = [];
                }
                $variantTiersMap[$vId][] = $t;
            }
        }
        PerfDebug::mark('tiered_prices', $t0);

        // --- FETCH VARIATION MATRIX ---
        $t0 = microtime(true);
        $variationMatrix = $this->variantModel->getVariantMatrix($productId);
        PerfDebug::mark('variation_matrix', $t0);
        
        // POLYFILL for legacy single-table variants (e.g. Color - Size)
        $varCount = count($variants);
        if (empty($variationMatrix['colors']) && $varCount > 0) {
            $colorMap = [];
            $polyCombinations = [];
            $colorIdCounter = 1;
            $isDoubleMode = false;
            
            foreach ($variants as $vi => $v) {
                $parts = explode(' - ', $v['attribute_value'] ?? '');
                if (count($parts) >= 2) {
                    $isDoubleMode = true;
                    $cName = trim($parts[0]);
                    $sName = trim(implode(' - ', array_slice($parts, 1)));
                    
                    if (!isset($colorMap[$cName])) {
                        $colorMap[$cName] = $colorIdCounter++;
                        $variationMatrix['colors'][] = [
                            'id' => $colorMap[$cName],
                            'color_name' => $cName,
                            'swatch_hex_or_image' => !empty($v['image_url']) ? asset($v['image_url']) : $mainImg,
                            'sizes' => []
                        ];
                    }
                    
                    // Find color index
                    $cIdx = -1;
                    foreach ($variationMatrix['colors'] as $idx => $c) {
                        if ($c['color_name'] === $cName) { $cIdx = $idx; break; }
                    }
                    
                    $fakeSizeId = (int)$v['id']; // Use actual variant ID as size ID for cart compatibility
                    
                    if ($cIdx !== -1) {
                        $variationMatrix['colors'][$cIdx]['sizes'][] = [
                            'id' => $fakeSizeId,
                            'size_label' => $sName,
                            'price' => (float)$v['wholesale_price'],
                            'stock_qty' => (int)$v['stock_quantity'],
                            'sku' => $v['sku'] ?? $v['variant_code'] ?? null,
                        ];
                    }
                    
                    $polyCombinations[] = [
                        'id' => $fakeSizeId,
                        'color_id' => $colorMap[$cName],
                        'size_id' => $fakeSizeId,
                        'size_name' => $sName,
                        'sku' => $v['sku'] ?? $v['variant_code'] ?? null,
                        'wholesale_price' => (float)$v['wholesale_price'],
                        'stock' => (int)$v['stock_quantity'],
                        'image_url' => $v['image_url'] ?? null,
                    ];
                }
            }
            if ($isDoubleMode) {
                $variationMatrix['variation_mode'] = 'double';
                $variationMatrix['combinations'] = $polyCombinations;
            }
        }

        $variationMatrix['product_tiers'] = $productTiers;
        $variationMatrix['variant_tiers_map'] = $variantTiersMap;

        PerfDebug::mark('controller_total', $reqStart);

        $this->renderView('web/product_detail', [
            'product' => $product,
            'variants' => $variants,
            'specifications' => $specifications,
            'galleryImages' => $galleryImages,
            'minWholesalePrice' => $minWholesale,
            'minOnePiecePrice' => $minOnePiece,
            'categories' => $categories,
            'relatedProducts' => $relatedProducts,
            'visuallySimilar' => $visuallySimilar['items'] ?? [],
            'similarHeadline' => $visuallySimilar['headline'] ?? 'Visually Similar Products',
            'fromImageSearch' => $fromImageSearch,
            'imageSearchSimilar' => $imageSearchSimilar,
            'imageSearchSid' => $imageSearchSid,
            'initialVariantId' => $initialVariantId,
            'whatsappNumber' => $whatsappNumber,
            'productTiers' => $productTiers,
            'variantTiersMap' => $variantTiersMap,
            'variationMatrix' => $variationMatrix,
            'selectedVariantCode' => $selectedVariantCode,
            'hideGlobalHeader' => false,
            'hideMobileBottomNav' => true,
            'cartWishlistState' => $cartWishlistState,
            'csrfToken' => $csrfToken,
            'pdpPerfDebugHtml' => PerfDebug::renderHtmlComment(),
        ]);
    }
}
