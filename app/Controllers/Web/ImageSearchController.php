<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Services\ImageSearchStoreService;
use App\Services\VisualSearchService;
use PDO;

class ImageSearchController extends BaseController
{
    private const PER_PAGE = 24;

    public function index(): void
    {
        $token = preg_replace('/[^a-f0-9]/', '', strtolower((string) ($_GET['sid'] ?? ''))) ?? '';
        $sort = strtolower(trim((string) ($_GET['sort'] ?? 'best')));
        $allowedSort = ['best', 'price_asc', 'price_desc', 'newest'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'best';
        }
        $categoryFilter = isset($_GET['category']) ? (int) $_GET['category'] : 0;
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $store = new ImageSearchStoreService();
        $search = ($token !== '' && strlen($token) === 32) ? $store->findValid($token) : null;

        if (!$search) {
            $this->renderView('web/image_search', [
                'title' => 'Image Search Results | ImportWala',
                'seoOptions' => [
                    'title' => 'Image Search Results | ImportWala',
                    'robots' => 'noindex, nofollow',
                ],
                'noindex' => true,
                'invalidToken' => true,
                'sid' => $token,
                'products' => [],
                'totalCount' => 0,
                'page' => 1,
                'perPage' => self::PER_PAGE,
                'totalPages' => 1,
                'sort' => $sort,
                'categoryFilter' => 0,
                'categoryOptions' => [],
                'hasConfidentMatch' => false,
                'queryImageUrl' => null,
                'hideMobileBottomNav' => false,
            ]);
            return;
        }

        $results = $this->withSimilarStyle($search['results']);
        $hydrated = $this->hydrateProducts($results);
        $categoryOptions = $this->buildCategoryOptions($hydrated);

        if ($categoryFilter > 0) {
            $hydrated = array_values(array_filter($hydrated, static function ($p) use ($categoryFilter) {
                return (int) ($p['category_id'] ?? 0) === $categoryFilter
                    || (int) ($p['subcategory_id'] ?? 0) === $categoryFilter;
            }));
        }

        $hydrated = $this->sortProducts($hydrated, $sort);

        $totalCount = count($hydrated);
        $totalPages = max(1, (int) ceil($totalCount / self::PER_PAGE));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * self::PER_PAGE;
        $pageItems = array_slice($hydrated, $offset, self::PER_PAGE);

        // Best-match badge from stored score (works even if older rows have has_confident_match=0)
        $topScore = 0.0;
        if (!empty($hydrated[0])) {
            $topScore = (float) ($hydrated[0]['_score'] ?? $hydrated[0]['score'] ?? $hydrated[0]['raw_score'] ?? 0);
        }
        $hasConfident = !empty($search['has_confident_match']) || $topScore >= 0.85;
        if ($page === 1 && $sort === 'best' && $categoryFilter <= 0 && !empty($pageItems) && $topScore >= 0.85) {
            $pageItems[0]['_is_best_match'] = true;
        }

        $queryImageUrl = function_exists('url')
            ? url('image-search/thumb?sid=' . urlencode($search['token']))
            : '/image-search/thumb?sid=' . urlencode($search['token']);

        $this->renderView('web/image_search', [
            'title' => 'Image Search Results | ImportWala',
            'seoOptions' => [
                'title' => 'Image Search Results | ImportWala',
                'robots' => 'noindex, nofollow',
            ],
            'noindex' => true,
            'invalidToken' => false,
            'sid' => $search['token'],
            'products' => $pageItems,
            'totalCount' => $totalCount,
            'page' => $page,
            'perPage' => self::PER_PAGE,
            'totalPages' => $totalPages,
            'sort' => $sort,
            'categoryFilter' => $categoryFilter,
            'categoryOptions' => $categoryOptions,
            'hasConfidentMatch' => $hasConfident,
            'queryImageUrl' => $queryImageUrl,
            'hideMobileBottomNav' => false,
        ]);
    }

    /**
     * Older searches stored only the exact photo hit. Fill in same-type products
     * (other anklets, other bracelets) so the results page is not a single card.
     *
     * @param array<int, array{product_id:int,variant_id:?int,score:float}> $results
     * @return array<int, array{product_id:int,variant_id:?int,score:float}>
     */
    private function withSimilarStyle(array $results): array
    {
        if (count($results) >= 8) {
            return $results;
        }
        $topId = (int) ($results[0]['product_id'] ?? 0);
        $topScore = (float) ($results[0]['score'] ?? 0);
        if ($topId <= 0 || $topScore < 0.85) {
            return $results;
        }

        try {
            $extra = (new VisualSearchService())->similarStyleProductIds($topId, 36);
        } catch (\Throwable $e) {
            return $results;
        }

        $seen = [];
        foreach ($results as $row) {
            $seen[(int) ($row['product_id'] ?? 0)] = true;
        }
        foreach ($extra as $row) {
            $pid = (int) ($row['product_id'] ?? 0);
            if ($pid <= 0 || isset($seen[$pid])) {
                continue;
            }
            $seen[$pid] = true;
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Stream the private uploaded query image for a valid sid.
     */
    public function thumb(): void
    {
        $token = preg_replace('/[^a-f0-9]/', '', strtolower((string) ($_GET['sid'] ?? ''))) ?? '';
        $store = new ImageSearchStoreService();
        $search = ($token !== '' && strlen($token) === 32) ? $store->findValid($token) : null;
        if (!$search) {
            http_response_code(404);
            header('Content-Type: text/plain');
            echo 'Not found';
            return;
        }

        $abs = $store->absoluteImagePath($search['uploaded_image_path']);
        if (!$abs) {
            http_response_code(404);
            header('Content-Type: text/plain');
            echo 'Not found';
            return;
        }

        header('Content-Type: image/jpeg');
        header('Cache-Control: private, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        readfile($abs);
        exit;
    }

    /**
     * @param array<int, array{product_id:int,variant_id:?int,score:float}> $results
     * @return array<int, array>
     */
    private function hydrateProducts(array $results): array
    {
        if (empty($results)) {
            return [];
        }

        $ids = [];
        $metaById = [];
        foreach ($results as $r) {
            $pid = (int) ($r['product_id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            $ids[] = $pid;
            // Keep best (first) meta per product — results already ordered by score
            if (!isset($metaById[$pid])) {
                $metaById[$pid] = [
                    'variant_id' => !empty($r['variant_id']) ? (int) $r['variant_id'] : null,
                    'score' => (float) ($r['score'] ?? 0),
                ];
            }
        }
        $ids = array_values(array_unique($ids));
        if (empty($ids)) {
            return [];
        }

        $db = Database::getInstance();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.main_image, p.moq,
                   p.is_featured, p.is_new_arrival, p.category_id, p.subcategory_id, p.created_at,
                   c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.id IN ($placeholders) AND p.status = 'active'
        ");
        $stmt->execute($ids);
        $byId = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $byId[(int) $row['id']] = $row;
        }

        // Prefetch variant images/prices for matched variants
        $variantIds = [];
        foreach ($metaById as $m) {
            if (!empty($m['variant_id'])) {
                $variantIds[] = (int) $m['variant_id'];
            }
        }
        $variantsById = [];
        if (!empty($variantIds)) {
            $variantIds = array_values(array_unique($variantIds));
            $vp = implode(',', array_fill(0, count($variantIds), '?'));
            try {
                $vStmt = $db->prepare("
                    SELECT id, product_id, image_url, wholesale_price, one_piece_price
                    FROM product_variants
                    WHERE id IN ($vp) AND is_active = 1
                ");
                $vStmt->execute($variantIds);
                foreach ($vStmt->fetchAll(PDO::FETCH_ASSOC) as $v) {
                    $variantsById[(int) $v['id']] = $v;
                }
            } catch (\Throwable $e) {
            }
        }

        $ordered = [];
        foreach ($ids as $pid) {
            if (!isset($byId[$pid])) {
                continue; // deactivated / deleted
            }
            $p = $byId[$pid];
            $meta = $metaById[$pid];
            $p['_score'] = $meta['score'];
            $p['_matched_variant_id'] = $meta['variant_id'];
            $p['is_new'] = !empty($p['is_new_arrival']);

            if (!empty($meta['variant_id']) && isset($variantsById[$meta['variant_id']])) {
                $v = $variantsById[$meta['variant_id']];
                if (!empty($v['image_url'])) {
                    $p['main_image'] = $v['image_url'];
                    $p['_force_image'] = $v['image_url'];
                }
                $vPrice = (float) ($v['one_piece_price'] ?? 0);
                if ($vPrice <= 0) {
                    $vPrice = (float) ($v['wholesale_price'] ?? 0);
                }
                if ($vPrice > 0) {
                    $p['sale_price'] = $vPrice;
                    $p['price'] = $vPrice;
                }
                $p['url_query'] = 'variant_id=' . (int) $meta['variant_id'];
            }

            $ordered[] = $p;
        }

        return $ordered;
    }

    private function buildCategoryOptions(array $products): array
    {
        $map = [];
        foreach ($products as $p) {
            $cid = (int) ($p['category_id'] ?? 0);
            $name = trim((string) ($p['category_name'] ?? ''));
            if ($cid > 0 && $name !== '' && !isset($map[$cid])) {
                $map[$cid] = $name;
            }
        }
        asort($map, SORT_NATURAL | SORT_FLAG_CASE);
        $out = [];
        foreach ($map as $id => $name) {
            $out[] = ['id' => $id, 'name' => $name];
        }
        return $out;
    }

    private function sortProducts(array $products, string $sort): array
    {
        usort($products, static function ($a, $b) use ($sort) {
            switch ($sort) {
                case 'price_asc':
                    $pa = (float) (($a['sale_price'] ?? 0) > 0 ? $a['sale_price'] : $a['price']);
                    $pb = (float) (($b['sale_price'] ?? 0) > 0 ? $b['sale_price'] : $b['price']);
                    return $pa <=> $pb;
                case 'price_desc':
                    $pa = (float) (($a['sale_price'] ?? 0) > 0 ? $a['sale_price'] : $a['price']);
                    $pb = (float) (($b['sale_price'] ?? 0) > 0 ? $b['sale_price'] : $b['price']);
                    return $pb <=> $pa;
                case 'newest':
                    return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
                case 'best':
                default:
                    return ((float) ($b['_score'] ?? 0)) <=> ((float) ($a['_score'] ?? 0));
            }
        });
        return $products;
    }
}
