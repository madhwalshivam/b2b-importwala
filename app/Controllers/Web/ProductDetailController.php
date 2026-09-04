<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
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
        $this->productRepo = new ProductRepository();
        $this->categoryRepo = new CategoryRepository();
        $this->variantModel = new ProductVariant();
        $this->specModel = new ProductSpecification();
        $this->imageModel = new ProductImage();
    }

    public function show(string $slug, ?string $variantCode = null): void
    {
        $selectedVariantCode = $variantCode ?: ($_GET['variant'] ?? null);
        $product = $this->productRepo->findBySlug($slug);
        if (!$product && is_numeric($slug)) {
            $product = $this->productRepo->find((int)$slug);
        }

        if (!$product || ($product['status'] ?? 'active') !== 'active') {
            http_response_code(404);
            echo "Product Not Found";
            return;
        }

        $productId = (int)$product['id'];

        // 1. Fetch Variants
        $variants = $this->variantModel->getByProduct($productId, true);

        // 2. Fetch Specifications
        $specifications = $this->specModel->getByProduct($productId);

        // 3. Build Merged Deduplicated Gallery Images Array
        $galleryImages = [];
        $mainImg = !empty($product['main_image']) ? asset($product['main_image']) : asset('assets/images/placeholder.jpg');
        if ($mainImg) {
            $galleryImages[] = $mainImg;
        }

        // Product gallery images
        $dbImages = $this->imageModel->getByProduct($productId);
        foreach ($dbImages as $img) {
            $u = asset($img['image_url'] ?: $img['image_path']);
            if ($u && !in_array($u, $galleryImages)) {
                $galleryImages[] = $u;
            }
        }

        // 4. Compute Dynamic Starting Prices for Dual Modes
        $baseWholesale = (float)($product['price'] ?? 0);
        $baseOnePiece  = !empty($product['sale_price']) ? (float)$product['sale_price'] : $baseWholesale;

        $wholesalePrices = [$baseWholesale];
        $onePiecePrices  = [$baseOnePiece];

        foreach ($variants as $v) {
            if ((float)$v['wholesale_price'] > 0) {
                $wholesalePrices[] = (float)$v['wholesale_price'];
            }
            if ((float)$v['one_piece_price'] > 0) {
                $onePiecePrices[] = (float)$v['one_piece_price'];
            }
        }

        $minWholesale = min(array_filter($wholesalePrices, fn($p) => $p > 0) ?: [0]);
        $minOnePiece  = min(array_filter($onePiecePrices, fn($p) => $p > 0) ?: [0]);

        // 5. Visually Similar & Related Products (AI Feature Vector Match)
        $visualService = new \App\Services\VisualSearchService();
        $visuallySimilar = $visualService->searchByProductId($productId, 8);

        $categories = $this->categoryRepo->getTree();
        $relatedProducts = $this->productRepo->getByCategory($product['category_id'] ?? 0, 8);

        // 6. WhatsApp Number & Template Settings
        $settingModel = new Setting();
        $whatsappNumber = preg_replace('/[^0-9]/', '', $settingModel->get('whatsapp_business_number') ?? '919217714452');

        // 7. Fetch Tiered Volume Pricing (Product level + Variant level)
        $db = \App\Core\Database::getInstance();
        $allTiersStmt = $db->prepare("SELECT * FROM tiered_prices WHERE product_id = ? ORDER BY min_qty ASC");
        $allTiersStmt->execute([$productId]);
        $allTiers = $allTiersStmt->fetchAll(\PDO::FETCH_ASSOC);

        $productTiers = [];
        $variantTiersMap = [];

        foreach ($allTiers as $t) {
            if (empty($t['variant_id'])) {
                $productTiers[] = $t;
            } else {
                $vId = (int)$t['variant_id'];
                if (!isset($variantTiersMap[$vId])) {
                    $variantTiersMap[$vId] = [];
                }
                $variantTiersMap[$vId][] = $t;
            }
        }

        $this->renderView('web/product_detail', [
            'product'               => $product,
            'variants'              => $variants,
            'specifications'        => $specifications,
            'galleryImages'         => $galleryImages,
            'minWholesalePrice'     => $minWholesale,
            'minOnePiecePrice'      => $minOnePiece,
            'categories'            => $categories,
            'relatedProducts'       => $relatedProducts,
            'visuallySimilar'       => $visuallySimilar['items'] ?? [],
            'similarHeadline'       => $visuallySimilar['headline'] ?? 'Visually Similar Products',
            'whatsappNumber'        => $whatsappNumber,
            'productTiers'          => $productTiers,
            'variantTiersMap'       => $variantTiersMap,
            'selectedVariantCode'   => $selectedVariantCode,
        ]);
    }
}
