<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\CategoryRepository;

class ProductDetailController extends BaseController
{
    private ProductRepository $productRepo;
    private CategoryRepository $categoryRepo;

    public function __construct()
    {
        $this->productRepo = new ProductRepository();
        $this->categoryRepo = new CategoryRepository();
    }

    public function show(string $slug): void
    {
        $product = $this->productRepo->findBySlug($slug);
        if (!$product) {
            http_response_code(404);
            echo "Product Not Found";
            return;
        }

        $categories = $this->categoryRepo->getTree();
        $relatedProducts = $this->productRepo->getByCategory($product['category_id'], 8);

        $this->renderView('web/product_detail', [
            'product' => $product,
            'categories' => $categories,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
