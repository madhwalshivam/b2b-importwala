<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\SearchService;
use App\Repositories\Eloquent\CategoryRepository;
use App\Core\Database;

class CatalogController extends BaseController
{
    private SearchService $searchService;
    private CategoryRepository $categoryRepo;

    public function __construct()
    {
        $this->searchService = new SearchService();
        $this->categoryRepo = new CategoryRepository();
    }

    /**
     * Standard Catalog Index Handler (/catalog)
     * Performs 301 SEO Auto-Redirects for legacy query string parameters.
     */
    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');
        $catId = (int)($_GET['category_id'] ?? 0);
        $catSlugParam = trim($_GET['category'] ?? '');
        $collectionId = (int)($_GET['collection_id'] ?? $_GET['collection'] ?? 0);

        // 1. Auto-Redirect legacy query params to Clean SEO URLs if no extra filters
        $isPureQuery = empty($_GET['min_price']) && empty($_GET['max_price']) && empty($_GET['sort']) && empty($_GET['page']);

        if ($isPureQuery) {
            // Category by Slug parameter (?category=earrings)
            if (!empty($catSlugParam)) {
                $targetUrl = url('category/' . slugify($catSlugParam));
                header('Location: ' . $targetUrl, true, 301);
                exit;
            }

            // Category by ID parameter (?category_id=5)
            if ($catId > 0) {
                $cat = $this->categoryRepo->findById($catId);
                if ($cat && !empty($cat['slug'])) {
                    header('Location: ' . url('category/' . $cat['slug']), true, 301);
                    exit;
                }
            }

            // Legacy Search query parameter (?q=jewelry or ?q=hoop-&-stud-earrings)
            if (!empty($q)) {
                $cleanTerm = str_replace('&', 'and', $q);
                $querySlug = slugify($cleanTerm);

                // Check if query matches an existing Category slug
                $catMatch = $this->categoryRepo->findBySlug($querySlug);
                if (!$catMatch) {
                    // Try without 'and' replacements if needed
                    $catMatch = $this->categoryRepo->findBySlug(slugify($q));
                }

                if ($catMatch) {
                    header('Location: ' . url('category/' . $catMatch['slug']), true, 301);
                    exit;
                }

                // Check if query matches an existing Subcategory slug
                $subMatch = $this->findSubcategoryBySlug($querySlug) ?: $this->findSubcategoryBySlug(slugify($q));
                if ($subMatch) {
                    $parentCat = $this->categoryRepo->findById((int)$subMatch['category_id']);
                    $catSlug = $parentCat['slug'] ?? 'catalog';
                    header('Location: ' . url('category/' . $catSlug . '/' . $subMatch['slug']), true, 301);
                    exit;
                }

                // Redirect to Clean Search URL (/search/hoop-and-stud-earrings)
                header('Location: ' . url('search/' . $querySlug), true, 301);
                exit;
            }
        }

        $this->renderCatalogPage([
            'q' => $q,
            'category_id' => $catId,
            'collection_id' => $collectionId,
        ]);
    }

    /**
     * Clean Category URL Handler (/category/{slug})
     */
    public function category(string $slug): void
    {
        $slug = slugify($slug);
        $category = $this->categoryRepo->findBySlug($slug);

        if (!$category) {
            // Check subcategories table if not found in categories
            $sub = $this->findSubcategoryBySlug($slug);
            if ($sub) {
                $parentCat = $this->categoryRepo->findById((int)$sub['category_id']);
                if ($parentCat) {
                    $this->subcategory($parentCat['slug'], $sub['slug']);
                    return;
                }
            }

            // Fallback: search query with clean slug
            $this->search($slug);
            return;
        }

        $seoTitle = !empty($category['meta_title']) ? $category['meta_title'] : ($category['name'] . ' Wholesale Catalog | ImportWale');
        $seoDesc = !empty($category['meta_description']) ? $category['meta_description'] : ('Buy wholesale ' . $category['name'] . ' at factory-direct prices from ImportWale.');
        $canonical = category_url($category);

        $this->renderCatalogPage([
            'category_id' => (int)$category['id'],
            'active_category' => $category,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDesc,
            'canonical_url' => $canonical,
            'page_heading' => $category['name'],
        ]);
    }

    /**
     * Clean Subcategory URL Handler (/category/{cat_slug}/{sub_slug})
     */
    public function subcategory(string $catSlug, string $subSlug): void
    {
        $catSlug = slugify($catSlug);
        $subSlug = slugify($subSlug);

        $category = $this->categoryRepo->findBySlug($catSlug);
        $subcategory = $this->findSubcategoryBySlug($subSlug);

        if (!$category && $subcategory) {
            $category = $this->categoryRepo->findById((int)$subcategory['category_id']);
        }

        if (!$subcategory) {
            if ($category) {
                $this->category($catSlug);
                return;
            }
            $this->search($subSlug);
            return;
        }

        $catName = $category['name'] ?? 'Catalog';
        $seoTitle = $subcategory['name'] . ' - ' . $catName . ' Wholesale | ImportWale';
        $seoDesc = 'Shop wholesale ' . $subcategory['name'] . ' under ' . $catName . ' at factory rates on ImportWale.';
        $canonical = subcategory_url($category, $subcategory);

        $this->renderCatalogPage([
            'category_id' => (int)($category['id'] ?? 0),
            'subcategory_id' => (int)$subcategory['id'],
            'active_category' => $category,
            'active_subcategory' => $subcategory,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDesc,
            'canonical_url' => $canonical,
            'page_heading' => $subcategory['name'] . ' (' . $catName . ')',
        ]);
    }

    /**
     * Clean Search URL Handler (/search/{query})
     */
    public function search(string $query): void
    {
        // Decode slugified query e.g. "hoop-and-stud-earrings" -> "hoop & stud earrings"
        $rawQuery = str_replace('-', ' ', $query);
        $searchTerms = trim(preg_replace('/\s+/', ' ', $rawQuery));

        $seoTitle = 'Search: ' . ucwords($searchTerms) . ' | ImportWale';
        $seoDesc = 'Browse wholesale results for "' . htmlspecialchars($searchTerms) . '" on ImportWale.';
        $canonical = search_url($query);

        $this->renderCatalogPage([
            'q' => $searchTerms,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDesc,
            'canonical_url' => $canonical,
            'page_heading' => 'Search Results for "' . htmlspecialchars($searchTerms) . '"',
        ]);
    }

    /**
     * Search Query String Handler (/search?q=...)
     */
    public function searchQueryString(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (!empty($q)) {
            $cleanTerm = str_replace('&', 'and', $q);
            $querySlug = slugify($cleanTerm);
            header('Location: ' . url('search/' . $querySlug), true, 301);
            exit;
        }

        header('Location: ' . url('catalog'), true, 301);
        exit;
    }

    /**
     * Clean Collection URL Handler (/collection/{slug})
     */
    public function collection(string $slug): void
    {
        $collectionId = (int)$slug;
        $collectionCard = null;

        if ($collectionId > 0) {
            $cardModel = new \App\Models\CollectionCard();
            $collectionCard = $cardModel->findById($collectionId);
        }

        $heading = $collectionCard['title'] ?? 'Collection Catalog';
        $seoTitle = $heading . ' | ImportWale';
        $canonical = url('collection/' . $slug);

        $this->renderCatalogPage([
            'collection_id' => $collectionId,
            'collection_card' => $collectionCard,
            'seo_title' => $seoTitle,
            'canonical_url' => $canonical,
            'page_heading' => $heading,
        ]);
    }

    /**
     * Clean Brand URL Handler (/brand/{slug})
     */
    public function brand(string $slug): void
    {
        $slug = slugify($slug);
        $db = Database::getReadConnection();
        $stmt = $db->prepare("SELECT * FROM `brands` WHERE `slug` = ? AND `status` = 'active' LIMIT 1");
        $stmt->execute([$slug]);
        $brand = $stmt->fetch();

        $brandId = (int)($brand['id'] ?? 0);
        $heading = $brand['name'] ?? ucwords(str_replace('-', ' ', $slug));
        $seoTitle = $heading . ' Products Wholesale | ImportWale';
        $canonical = url('brand/' . $slug);

        $this->renderCatalogPage([
            'brand_id' => $brandId,
            'active_brand' => $brand,
            'seo_title' => $seoTitle,
            'canonical_url' => $canonical,
            'page_heading' => $heading,
        ]);
    }

    /**
     * All Categories Directory (/categories)
     */
    public function categoriesDirectory(): void
    {
        $categoryModel = new \App\Models\Category();
        $categories = $categoryModel->getActiveCategories();
        $db = Database::getInstance();

        foreach ($categories as &$cat) {
            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT p.id) as total 
                FROM products p
                WHERE p.category_id = ? AND p.status = 'active'
            ");
            $stmt->execute([$cat['id']]);
            $countRow = $stmt->fetch();
            $cat['product_count'] = (int)($countRow['total'] ?? 0);
            $cat['subcategories'] = $categoryModel->getSubcategories((int)$cat['id']);
        }
        unset($cat);

        $this->renderView('web/categories_directory', [
            'categories' => $categories,
            'seoTitle' => 'Explore All Categories | ImportWale',
            'seoDescription' => 'Browse our complete wholesale catalog across all categories and subcategories.',
            'canonicalUrl' => url('categories'),
        ]);
    }

    /**
     * Helper to render shop/catalog view with standard filtering, search, and pagination
     */
    private function renderCatalogPage(array $options): void
    {
        $q = trim($options['q'] ?? $_GET['q'] ?? '');
        $catId = (int)($options['category_id'] ?? $_GET['category_id'] ?? 0);
        $subId = (int)($options['subcategory_id'] ?? $_GET['subcategory_id'] ?? 0);
        $collectionId = (int)($options['collection_id'] ?? $_GET['collection_id'] ?? $_GET['collection'] ?? 0);
        $brandId = (int)($options['brand_id'] ?? $_GET['brand_id'] ?? 0);

        $minPrice = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (float)$_GET['min_price'] : null;
        $maxPrice = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (float)$_GET['max_price'] : null;
        $minMoq   = (isset($_GET['min_moq']) && $_GET['min_moq'] !== '') ? (int)$_GET['min_moq'] : null;
        $maxMoq   = (isset($_GET['max_moq']) && $_GET['max_moq'] !== '') ? (int)$_GET['max_moq'] : null;
        $sort     = $_GET['sort'] ?? 'relevance';

        // Selectable per-page size (default: 24)
        $allowedLimits = [12, 24, 48];
        $perPage = (int)($_GET['per_page'] ?? 24);
        if (!in_array($perPage, $allowedLimits)) {
            $perPage = 24;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $similarToId = (int)($_GET['similar_to'] ?? 0);

        $similarProduct = null;
        if ($similarToId > 0) {
            $productRepo = new \App\Repositories\Eloquent\ProductRepository();
            $similarProduct = $productRepo->getProductWithDetails($similarToId);
        }

        $attrFilters = $_GET['attr'] ?? [];
        if (!is_array($attrFilters)) {
            $attrFilters = [];
        }

        $filters = [
            'category_id'    => $catId,
            'subcategory_id' => $subId,
            'collection_id'  => $collectionId,
            'brand_id'       => $brandId,
            'similar_to'     => $similarToId,
            'min_price'      => $minPrice,
            'max_price'      => $maxPrice,
            'min_moq'        => $minMoq,
            'max_moq'        => $maxMoq,
            'sort'           => $sort,
            'per_page'       => $perPage,
            'attr'           => $attrFilters,
        ];

        $collectionCard = $options['collection_card'] ?? null;
        if (!$collectionCard && $collectionId > 0) {
            $cardModel = new \App\Models\CollectionCard();
            $collectionCard = $cardModel->findById($collectionId);
        }

        $searchResults = $this->searchService->search($q, $filters, $perPage, $offset);
        $categoriesTree = $this->getCategoryTreeWithCounts();

        $filterService = new \App\Services\FilterAttributeService();
        $dynamicFilterAttributes = $filterService->getAttributesForCategory($catId ?: null);

        $totalItems = (int)($searchResults['total'] ?? 0);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));

        // Ensure requested page doesn't exceed max total pages
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $pageWindow = $this->getPageWindow($page, $totalPages);

        $seoOptions = [
            'title'       => $options['seo_title'] ?? 'Shop All Wholesale Products | ImportWale',
            'description' => $options['seo_description'] ?? 'Browse all wholesale products with direct factory pricing and low MOQ.',
            'canonical'   => $options['canonical_url'] ?? url('shop'),
        ];

        $viewData = [
            'q'                 => $q,
            'filters'           => $filters,
            'results'           => $searchResults,
            'categoriesTree'    => $categoriesTree,
            'collectionCard'    => $collectionCard,
            'similarProduct'    => $similarProduct,
            'currentPage'       => $page,
            'perPage'           => $perPage,
            'totalPages'        => $totalPages,
            'totalItems'        => $totalItems,
            'pageWindow'        => $pageWindow,
            'seoOptions'              => $seoOptions,
            'dynamicFilterAttributes' => $dynamicFilterAttributes,
            'pageHeading'             => $options['page_heading'] ?? null,
            'activeCategory'    => $options['active_category'] ?? null,
            'activeSubcategory' => $options['active_subcategory'] ?? null,
        ];

        // AJAX dynamic JSON endpoint handling
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || !empty($_GET['ajax'])) {
            header('Content-Type: application/json');
            ob_start();
            require __DIR__ . '/../../../views/web/shop.php';
            $fullHtml = ob_get_clean();
            echo json_encode([
                'success'    => true,
                'total'      => $totalItems,
                'page'       => $page,
                'totalPages' => $totalPages,
                'html'       => $fullHtml,
            ]);
            exit;
        }

        $this->renderView('web/shop', $viewData);
    }

    /**
     * Get active categories tree with subcategories & product counts
     */
    private function getCategoryTreeWithCounts(): array
    {
        try {
            $db = Database::getReadConnection();
            $cats = $db->query("
                SELECT c.*, 
                       (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'active') as product_count
                FROM categories c
                WHERE c.status = 'active'
                ORDER BY c.sort_order ASC, c.name ASC
            ")->fetchAll();

            foreach ($cats as &$cat) {
                $subStmt = $db->prepare("
                    SELECT s.*, 
                           (SELECT COUNT(*) FROM products p WHERE p.subcategory_id = s.id AND p.status = 'active') as product_count
                    FROM subcategories s
                    WHERE s.category_id = ? AND s.status = 'active'
                    ORDER BY s.sort_order ASC, s.name ASC
                ");
                $subStmt->execute([$cat['id']]);
                $cat['subcategories'] = $subStmt->fetchAll();
            }
            unset($cat);

            return $cats;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Generate numbered pagination page window array (e.g. 1 2 ... 5 6 7 ... 12)
     */
    private function getPageWindow(int $currentPage, int $totalPages): array
    {
        if ($totalPages <= 7) {
            return range(1, $totalPages);
        }

        $pages = [];
        $pages[] = 1;
        $pages[] = 2;

        $start = max(3, $currentPage - 1);
        $end = min($totalPages - 2, $currentPage + 1);

        if ($start > 3) {
            $pages[] = '...';
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        if ($end < $totalPages - 2) {
            $pages[] = '...';
        }

        $pages[] = $totalPages - 1;
        $pages[] = $totalPages;

        $unique = [];
        foreach ($pages as $p) {
            if ($p === '...' || !in_array($p, $unique)) {
                $unique[] = $p;
            }
        }

        return $unique;
    }

    /**
     * Helper to find subcategory by slug
     */
    private function findSubcategoryBySlug(string $slug): ?array
    {
        try {
            $db = Database::getReadConnection();
            $stmt = $db->prepare("SELECT * FROM `subcategories` WHERE `slug` = ? AND `status` = 'active' LIMIT 1");
            $stmt->execute([$slug]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
