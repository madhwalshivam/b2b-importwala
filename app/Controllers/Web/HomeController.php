<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Models\FeaturedCategory;

class HomeController extends BaseController
{
    private CategoryRepository $categoryRepo;
    private ProductRepository $productRepo;
    private FeaturedCategory $featuredCategoryModel;

    public function __construct()
    {
        $this->categoryRepo = new CategoryRepository();
        $this->productRepo = new ProductRepository();
        $this->featuredCategoryModel = new FeaturedCategory();
    }

    public function index(): void
    {
        $categories = $this->categoryRepo->getTree();
        $featuredProducts = $this->productRepo->getFeatured(12);
        $newArrivals = $this->productRepo->getNewArrivals(12);
        $bestSellers = $this->productRepo->getBestSellers(12);
        $featuredCategories = $this->featuredCategoryModel->getActiveWithSubcategories();

        $bannerModel = new \App\Models\Banner();
        $heroBanners = $bannerModel->getActiveBanners();

        $this->renderView('web/home', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'newArrivals' => $newArrivals,
            'bestSellers' => $bestSellers,
            'featuredCategories' => $featuredCategories,
            'heroBanners' => $heroBanners,
        ]);
    }
}
