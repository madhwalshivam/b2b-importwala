<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ScooterModel;
use App\Helpers\Paginator;

class BrandFrontendController extends Controller {
    protected Brand $brandModel;
    protected Product $productModel;

    public function __construct() {
        parent::__construct();
        $this->brandModel = new Brand();
        $this->productModel = new Product();
    }

    /**
     * All Brands Listing Page (/brands)
     */
    public function index(): string {
        $brands = $this->brandModel->getActiveBrands();

        return $this->render('storefront/brands', [
            'brands' => $brands,
            'seoOptions' => [
                'title' => 'Electric Scooter Brands & Manufacturers | Mudsor',
                'description' => 'Browse custom accessories and spare parts engineered specifically for Ola, Ather, TVS, Bajaj Chetak, Hero Vida, and top EV manufacturers.'
            ]
        ]);
    }

    /**
     * Products Filtered by Brand Page (/brand/{slug})
     */
    public function products($slug = null): string {
        $brandId = (int)$this->request->input('brand_id', 0);
        $slug = $slug ?: trim($this->request->input('slug', $this->request->input('brand', '')));

        $brand = null;
        if ($brandId > 0) {
            $brand = $this->brandModel->find($brandId);
        } elseif (!empty($slug)) {
            $matches = $this->brandModel->where("slug = ? OR name = ?", [$slug, $slug]);
            $brand = $matches[0] ?? null;
        }

        // If brand not found, fallback to first active brand or redirect
        if (!$brand) {
            $allActive = $this->brandModel->getActiveBrands();
            if (!empty($allActive)) {
                $brand = $allActive[0];
            } else {
                $this->redirect(url('brands'));
            }
        }

        $page = max(1, (int)$this->request->input('page', 1));
        $perPage = 12;

        $categoryModel = new Category();
        $scooterModel = new ScooterModel();

        $filters = [
            'brand_id'      => $brand['id'],
            'category_slug' => $this->request->input('category'),
            'model_slug'    => $this->request->input('model'),
            'sort'          => $this->request->input('sort', 'newest'),
            'search'        => $this->request->input('search')
        ];

        $result = $this->productModel->getFilteredProducts($filters, $page, $perPage);
        $paginator = new Paginator($result['total'], $result['per_page'], $result['current_page'], url('brand/' . $brand['slug']), $_GET);

        return $this->render('storefront/brand_products', [
            'brand'           => $brand,
            'products'        => $result['items'],
            'paginator'       => $paginator,
            'filters'         => $filters,
            'categories'      => $categoryModel->getActiveCategories(),
            'allModels'       => $scooterModel->getAllWithBrand(),
            'compareList'     => $_SESSION['compare'] ?? [],
            'wishlistProductIds' => !empty($_SESSION['user_id']) ? [] : ($_SESSION['guest_wishlist'] ?? []),
            'seoOptions'      => [
                'title'       => htmlspecialchars($brand['name']) . ' Accessories & Spare Parts | Mudsor',
                'description' => 'Explore precision-fit accessories engineered specifically for ' . htmlspecialchars($brand['name']) . ' electric scooters.'
            ]
        ]);
    }
}
