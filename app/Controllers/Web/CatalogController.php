<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\SearchService;
use App\Repositories\Eloquent\CategoryRepository;

class CatalogController extends BaseController
{
    private SearchService $searchService;
    private CategoryRepository $categoryRepo;

    public function __construct()
    {
        $this->searchService = new SearchService();
        $this->categoryRepo = new CategoryRepository();
    }

    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');
        $catId = (int)($_GET['category_id'] ?? 0);
        $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
        $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
        $sort = $_GET['sort'] ?? 'relevance';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 24;
        $offset = ($page - 1) * $limit;

        $filters = [
            'category_id' => $catId,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort' => $sort,
        ];

        $searchResults = $this->searchService->search($q, $filters, $limit, $offset);
        $categories = $this->categoryRepo->getTree();

        $this->renderView('web/catalog', [
            'q' => $q,
            'filters' => $filters,
            'results' => $searchResults,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => ceil(($searchResults['total'] ?? 0) / $limit),
        ]);
    }
}
