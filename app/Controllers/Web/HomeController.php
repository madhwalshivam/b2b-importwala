<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Models\FeaturedCategory;
use App\Models\CollectionCard;
use App\Models\Testimonial;

class HomeController extends BaseController
{
    private CategoryRepository $categoryRepo;
    private ProductRepository $productRepo;
    private FeaturedCategory $featuredCategoryModel;
    private CollectionCard $collectionCardModel;
    private Testimonial $testimonialModel;

    public function __construct()
    {
        $this->categoryRepo = new CategoryRepository();
        $this->productRepo = new ProductRepository();
        $this->featuredCategoryModel = new FeaturedCategory();
        $this->collectionCardModel = new CollectionCard();
        $this->testimonialModel = new Testimonial();
    }

    public function index(): void
    {
        $categories = $this->categoryRepo->getTree();
        $featuredProducts = $this->productRepo->getFeatured(12);
        $newArrivals = $this->productRepo->getNewArrivals(12);
        $bestSellers = $this->productRepo->getBestSellers(12);
        $featuredCategories = $this->featuredCategoryModel->getActiveWithSubcategories();
        $collectionCards = $this->collectionCardModel->getActiveWithProducts(6);
        $testimonials = $this->testimonialModel->getFeatured(6);

        $bannerModel = new \App\Models\Banner();
        $heroBanners = $bannerModel->getActiveBanners();

        $this->renderView('web/home', [
            'categories'         => $categories,
            'featuredProducts'   => $featuredProducts,
            'newArrivals'        => $newArrivals,
            'bestSellers'        => $bestSellers,
            'featuredCategories' => $featuredCategories,
            'heroBanners'        => $heroBanners,
            'collectionCards'    => $collectionCards,
            'testimonials'       => $testimonials,
        ]);
    }
}
