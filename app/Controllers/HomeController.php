<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\HomeSection;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ScooterModel;
use App\Models\HomepageVideo;
use App\Models\GoogleReview;
use App\Models\BlogPost;
use App\Core\Database;

class HomeController extends Controller {
    public function index(): string {
        $blogModel = new BlogPost();
        $latestArticles = $blogModel->getRecentPublished(4);
        $sectionModel = new HomeSection();
        $sections = $sectionModel->getActiveSections();

        // Database-driven homepage sections with admin-selected products
        $homepageSections = $sectionModel->getEnabledSectionsWithProducts();

        $brandModel = new Brand();
        $categoryModel = new Category();
        $productModel = new Product();
        $scooterModel = new ScooterModel();

        $brands = $brandModel->getActiveBrands();
        $categories = $categoryModel->getActiveCategories();
        $allModels = $scooterModel->getAllWithBrand();
        $allProducts = $productModel->getAllActiveProducts();

        $featuredProducts = $homepageSections['featured_products']['products'] ?? [];
        $bestSellers      = $homepageSections['best_sellers']['products'] ?? [];
        $newArrivals      = $homepageSections['new_arrivals']['products'] ?? [];
        $featuredDeals    = $homepageSections['featured_deals']['products'] ?? [];
        $flashSale        = $homepageSections['flash_sale']['products'] ?? [];

        // Videos & Google Reviews
        $videoModel = new HomepageVideo();
        $homepageVideos = $videoModel->getActiveVideos();

        $googleReviewModel = new GoogleReview();
        $googleReviews = $googleReviewModel->getActiveReviews();

        $db = Database::getInstance();
        $reviews = $db->query("SELECT r.*, p.name as product_name FROM reviews r JOIN products p ON r.product_id = p.id WHERE r.status = 'approved' ORDER BY r.id DESC LIMIT 6")->fetchAll();

        // Hero Banners
        $bannerModel = new Banner();
        $heroBanners = $bannerModel->getActiveBanners();

        // Dynamic Announcement from DB
        $announcementStmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        $announcement = $announcementStmt->fetch();

        // Dynamic Homepage Compare Products from DB
        $compareStmt = $db->query("
            SELECT p.*, c.name as category_name, b.name as brand_name
            FROM homepage_compare_products hcp
            JOIN products p ON hcp.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            ORDER BY hcp.sort_order ASC
        ");
        $homepageCompareProducts = $compareStmt->fetchAll();

        if (empty($homepageCompareProducts) && !empty($allProducts)) {
            // Fallback to first 3 products
            $homepageCompareProducts = array_slice($allProducts, 0, 3);
        }

        return $this->render('storefront/home', [
            'sections'                => $sections,
            'homepageSections'        => $homepageSections,
            'heroBanners'             => $heroBanners,
            'brands'                  => $brands,
            'categories'              => $categories,
            'allModels'               => $allModels,
            'allProducts'             => $allProducts,
            'featuredProducts'        => $featuredProducts,
            'bestSellers'             => $bestSellers,
            'newArrivals'             => $newArrivals,
            'featuredDeals'           => $featuredDeals,
            'flashSale'               => $flashSale,
            'homepageVideos'          => $homepageVideos,
            'googleReviews'           => $googleReviews,
            'reviews'                 => $reviews,
            'announcement'            => $announcement,
            'homepageCompareProducts' => $homepageCompareProducts,
            'latestArticles'          => $latestArticles
        ]);
    }
}
