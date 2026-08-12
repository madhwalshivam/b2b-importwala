<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Core\Database;

use App\Models\UrlRedirect;

class ProductDetailController extends Controller {
    public function show(string $slug): string {
        // 1. Check 301 Redirect Table for old/merged slugs
        $redirectModel = new UrlRedirect();
        $redir = $redirectModel->findByOldSlug($slug);
        if ($redir && !empty($redir['target_url'])) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $redir['target_url']);
            exit;
        }

        $productModel = new Product();
        $product = $productModel->findBy('slug', $slug);
        if (!$product && is_numeric($slug)) {
            $product = $productModel->find((int)$slug);
        }
        if (!$product) {
            $product = $productModel->findBy('slug', urldecode($slug));
        }

        if (!$product || $product['status'] !== 'active') {
            // If product status is 'merged', check again if there is a target URL redirect
            if ($product && $product['status'] === 'merged') {
                $redir = $redirectModel->findByOldSlug($product['slug']);
                if ($redir && !empty($redir['target_url'])) {
                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: " . $redir['target_url']);
                    exit;
                }
            }
            $this->response->setStatusCode(404);
            return $this->render('errors/404');
        }

        $pid = (int)$product['id'];

        // Increment views count
        $productModel->update($pid, ['views_count' => $product['views_count'] + 1]);

        $compatibleScooters = $productModel->getCompatibleScooters($pid);
        $specifications     = $productModel->getSpecifications($pid);
        $faqs               = $productModel->getFaqs($pid);

        // Ensure product main_image is correctly formatted
        $mainImgUrl = asset($product['main_image'] ?? 'assets/images/placeholder.jpg');
        $product['main_image'] = $mainImgUrl;

        // Gallery: all images ordered from DB
        $imageModel       = new ProductImage();
        $rawGalleryImages = $imageModel->getByProduct($pid);

        $allImagesMap = [];
        if ($mainImgUrl) {
            $allImagesMap[$mainImgUrl] = [
                'id'      => 0,
                'url'     => $mainImgUrl,
                'primary' => true,
                'is_video'=> false,
            ];
        }

        // Add Product Demo Video if available
        $productVideoUrl = trim($product['video_url'] ?? '');
        if (!empty($productVideoUrl)) {
            $videoPoster = \App\Helpers\VideoThumbnailHelper::resolveThumbnail(
                $product['video_thumbnail'] ?? null,
                $product['auto_video_thumbnail'] ?? null,
                $productVideoUrl
            );
            $allImagesMap['video_media'] = [
                'id'        => 'video_media',
                'url'       => $videoPoster ?: asset('assets/images/placeholder.jpg'),
                'video_url' => $productVideoUrl,
                'is_video'  => true,
                'primary'   => false,
            ];
        }

        foreach ($rawGalleryImages as $gi) {
            $u = asset($gi['image_url'] ?: $gi['image_path']);
            if ($u && !isset($allImagesMap[$u])) {
                $allImagesMap[$u] = [
                    'id'       => (int)$gi['id'],
                    'url'      => $u,
                    'primary'  => (bool)$gi['is_primary'],
                    'is_video' => false,
                ];
            }
        }

        $galleryImages = array_values($allImagesMap);
        $galleryJson   = json_encode($galleryImages);

        // Extended Spare Parts & Comparison Data
        $vehicleCompatibilities = $productModel->getVehicleCompatibilities($pid);
        $includedItems          = $productModel->getIncludedItems($pid);
        $vehicleImages          = $productModel->getVehicleInstallationImages($pid);
        $badges                 = $productModel->getBadges($pid);

        // Computed Price Savings
        $effectivePrice = $product['sale_price'] ?: $product['price'];
        $oemPrice       = $product['oem_price'] ?: ($effectivePrice * 1.45);
        $savings        = max(0, $oemPrice - $effectivePrice);
        $savingsPct     = round(($savings / $oemPrice) * 100);

        // Fetch Frequently Bought Together Products
        $frequentlyBought = $productModel->getRelatedProducts($pid, 'frequently_bought', 4);

        // Fetch 4 Random Active Products for "You May Also Like" Section
        $db = Database::getInstance();
        $stmtRand = $db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id != ? AND p.status = 'active' ORDER BY RAND() LIMIT 4");
        $stmtRand->execute([$pid]);
        $relatedProducts = $stmtRand->fetchAll(\PDO::FETCH_ASSOC);

        // Ensure strictly max 4 products
        if (count($relatedProducts) > 4) {
            $relatedProducts = array_slice($relatedProducts, 0, 4);
        }

        foreach ($relatedProducts as &$rp) {
            $rp['main_image'] = asset($rp['main_image'] ?? 'assets/images/placeholder.jpg');
        }

        // Fetch Approved Customer Reviews
        $stmtRev = $db->prepare("SELECT * FROM reviews WHERE product_id = ? AND status = 'approved' ORDER BY id DESC");
        $stmtRev->execute([$pid]);
        $reviews = $stmtRev->fetchAll();

        // Calculate exact DB rating_avg and review_count from actual database reviews
        $reviewCount = count($reviews);
        $ratingAvg   = $reviewCount > 0 ? round(array_sum(array_column($reviews, 'rating')) / $reviewCount, 1) : 0;

        $product['review_count'] = $reviewCount;
        $product['rating_avg']   = $ratingAvg;

        return $this->render('storefront/product', [
            'product'               => $product,
            'compatibleScooters'    => $compatibleScooters,
            'galleryImages'         => $galleryImages,
            'galleryJson'           => $galleryJson,
            'specifications'        => $specifications,
            'faqs'                  => $faqs,
            'frequentlyBought'      => $frequentlyBought,
            'relatedProducts'       => $relatedProducts,
            'reviews'               => $reviews,
            'vehicleCompatibilities'=> $vehicleCompatibilities,
            'includedItems'         => $includedItems,
            'vehicleImages'         => $vehicleImages,
            'badges'                => $badges,
            'effectivePrice'        => $effectivePrice,
            'oemPrice'              => $oemPrice,
            'savings'               => $savings,
            'savingsPct'            => $savingsPct
        ]);
    }
}
