<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\ProductRepository;

class HomeController extends BaseController
{
    private CategoryRepository $categoryRepo;
    private ProductRepository $productRepo;

    public function __construct()
    {
        $this->categoryRepo = new CategoryRepository();
        $this->productRepo = new ProductRepository();
    }

    public function index(): void
    {
        $categories = $this->categoryRepo->getTree();
        $featuredProducts = $this->productRepo->getFeatured(12);
        $newArrivals = $this->productRepo->getNewArrivals(12);
        $bestSellers = $this->productRepo->getBestSellers(12);

        $this->renderView('web/home', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'newArrivals' => $newArrivals,
            'bestSellers' => $bestSellers,
        ]);
    }
}
