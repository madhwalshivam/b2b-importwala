<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ScooterModel;
use App\Helpers\Paginator;

class ShopController extends Controller {
    public function index(): string {
        $productModel = new Product();
        $brandModel = new Brand();
        $categoryModel = new Category();
        $scooterModel = new ScooterModel();

        $page = (int)($this->request->input('page', 1));
        $perPage = (int)($this->request->input('per_page', 12));

        $filters = [
            'brand_id' => $this->request->input('brand_id'),
            'brand_slug' => $this->request->input('brand'),
            'model_id' => $this->request->input('model_id'),
            'model_slug' => $this->request->input('model'),
            'category_id' => $this->request->input('category_id'),
            'category_slug' => $this->request->input('category'),
            'search' => $this->request->input('search'),
            'min_price' => $this->request->input('min_price'),
            'max_price' => $this->request->input('max_price'),
            'sort' => $this->request->input('sort', 'newest')
        ];

        $result = $productModel->getFilteredProducts($filters, $page, $perPage);
        $paginatorBaseUrl = !empty($filters['category_slug']) ? url('category/' . $filters['category_slug']) : url('shop');
        $paginator = new Paginator($result['total'], $result['per_page'], $result['current_page'], $paginatorBaseUrl, $_GET);

        // Fetch brands & models for filter sidebar
        $brands = $brandModel->getActiveBrands();
        $categories = $categoryModel->getActiveCategories();
        $allModels = $scooterModel->getAllWithBrand();

        // Selected filter objects for header label rendering
        $selectedBrand = !empty($filters['brand_slug']) ? $brandModel->findBy('slug', $filters['brand_slug']) : null;
        $selectedModel = !empty($filters['model_slug']) ? $scooterModel->findBy('slug', $filters['model_slug']) : null;
        $selectedCategory = !empty($filters['category_slug']) ? $categoryModel->findBy('slug', $filters['category_slug']) : null;

        return $this->render('storefront/shop', [
            'products' => $result['items'],
            'paginator' => $paginator,
            'filters' => $filters,
            'brands' => $brands,
            'categories' => $categories,
            'allModels' => $allModels,
            'selectedBrand' => $selectedBrand,
            'selectedModel' => $selectedModel,
            'selectedCategory' => $selectedCategory
        ]);
    }
}
