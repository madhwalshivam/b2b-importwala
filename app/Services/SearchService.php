<?php

namespace App\Services;

use App\Repositories\Eloquent\ProductRepository;
use App\Core\Database;
use PDO;

class SearchService extends BaseService
{
    private ProductRepository $productRepo;
    private bool $useElasticsearch = false;
    private ?string $esEndpoint = null;

    public function __construct()
    {
        parent::__construct();
        $this->productRepo = new ProductRepository();
        $this->esEndpoint = getenv('ELASTICSEARCH_HOST') ?: null;
        if ($this->esEndpoint) {
            $this->useElasticsearch = true;
        }
    }

    /**
     * Search products with query, category filter, price range, and faceted sorting
     */
    public function search(string $query, array $filters = [], int $limit = 24, int $offset = 0): array
    {
        $cacheKey = "search:" . md5($query . json_encode($filters) . "_{$limit}_{$offset}");
        
        return $this->cache->remember($cacheKey, 600, function () use ($query, $filters, $limit, $offset) {
            if ($this->useElasticsearch) {
                try {
                    return $this->searchElasticsearch($query, $filters, $limit, $offset);
                } catch (\Throwable $e) {
                    // Fallback gracefully to MySQL EXPLAIN-optimized search
                }
            }

            return $this->searchMySql($query, $filters, $limit, $offset);
        });
    }

    private function searchMySql(string $query, array $filters = [], int $limit = 24, int $offset = 0): array
    {
        $db = Database::getReadConnection();
        $where = ["p.`status` = 'active'"];
        $params = [];

        if (!empty($query)) {
            $where[] = "(p.`name` LIKE :q1 OR p.`sku` LIKE :q2 OR p.`description` LIKE :q3 OR p.`tags` LIKE :q4)";
            $searchTerm = '%' . trim($query) . '%';
            $params['q1'] = $searchTerm;
            $params['q2'] = $searchTerm;
            $params['q3'] = $searchTerm;
            $params['q4'] = $searchTerm;
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.`category_id` = :cat_id";
            $params['cat_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['subcategory_id'])) {
            $where[] = "p.`subcategory_id` = :subcat_id";
            $params['subcat_id'] = (int)$filters['subcategory_id'];
        }

        if (!empty($filters['brand_id'])) {
            $where[] = "p.`brand_id` = :brand_id";
            $params['brand_id'] = (int)$filters['brand_id'];
        }

        if (!empty($filters['collection_id'])) {
            $where[] = "p.`id` IN (SELECT `product_id` FROM `collection_card_products` WHERE `collection_card_id` = :collection_id)";
            $params['collection_id'] = (int)$filters['collection_id'];
        }

        if (!empty($filters['similar_to'])) {
            $targetId = (int)$filters['similar_to'];
            $targetStmt = $db->prepare("SELECT id, title, category_id FROM `products` WHERE id = ?");
            $targetStmt->execute([$targetId]);
            $targetProd = $targetStmt->fetch();
            if ($targetProd) {
                $where[] = "p.`id` != :target_id";
                $params['target_id'] = $targetId;
                if (empty($filters['category_id'])) {
                    $where[] = "p.`category_id` = :target_cat_id";
                    $params['target_cat_id'] = (int)$targetProd['category_id'];
                }
            }
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '' && $filters['min_price'] !== null) {
            $where[] = "COALESCE(NULLIF(p.`sale_price`, 0), p.`base_price`, p.`price`) >= :min_price";
            $params['min_price'] = (float)$filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '' && $filters['max_price'] !== null) {
            $where[] = "COALESCE(NULLIF(p.`sale_price`, 0), p.`base_price`, p.`price`) <= :max_price";
            $params['max_price'] = (float)$filters['max_price'];
        }

        if (isset($filters['min_moq']) && $filters['min_moq'] !== '' && $filters['min_moq'] !== null) {
            $where[] = "p.`moq` >= :min_moq";
            $params['min_moq'] = (int)$filters['min_moq'];
        }

        if (isset($filters['max_moq']) && $filters['max_moq'] !== '' && $filters['max_moq'] !== null) {
            $where[] = "p.`moq` <= :max_moq";
            $params['max_moq'] = (int)$filters['max_moq'];
        }

        // Dynamic Filter Attributes (AND logic across attributes, OR logic within options)
        if (!empty($filters['attr']) && is_array($filters['attr'])) {
            $attrParamIdx = 1;
            foreach ($filters['attr'] as $attrIdOrSlug => $selectedOpts) {
                if (empty($selectedOpts)) continue;

                if (!is_array($selectedOpts)) {
                    $selectedOpts = array_filter(array_map('trim', explode(',', (string)$selectedOpts)));
                }

                $numericOptIds = array_filter(array_map('intval', $selectedOpts));

                if (!empty($numericOptIds)) {
                    $inPlaceholders = [];
                    foreach ($numericOptIds as $optVal) {
                        $pKey = "attr_opt_" . $attrParamIdx++;
                        $inPlaceholders[] = ":" . $pKey;
                        $params[$pKey] = $optVal;
                    }
                    $inSql = implode(',', $inPlaceholders);
                    $where[] = "p.`id` IN (SELECT `product_id` FROM `product_filter_attribute_values` WHERE `option_id` IN ({$inSql}))";
                }
            }
        }

        $sortClause = match ($filters['sort'] ?? 'relevance') {
            'price_asc', 'price_low_high' => 'ORDER BY COALESCE(NULLIF(p.`sale_price`, 0), p.`base_price`, p.`price`) ASC, p.`id` ASC',
            'price_desc', 'price_high_low' => 'ORDER BY COALESCE(NULLIF(p.`sale_price`, 0), p.`base_price`, p.`price`) DESC, p.`id` DESC',
            'newest'     => 'ORDER BY p.`id` DESC',
            'popular'    => 'ORDER BY p.`sales_count` DESC, p.`id` DESC',
            default      => 'ORDER BY p.`sales_count` DESC, p.`id` DESC',
        };

        $whereSql = implode(" AND ", $where);
        
        // Count total matching
        $countStmt = $db->prepare("SELECT COUNT(*) as total FROM `products` p WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int)($countStmt->fetch()['total'] ?? 0);

        // Fetch products
        $sql = "SELECT p.*, c.name as category_name FROM `products` p JOIN `categories` c ON p.category_id = c.id WHERE {$whereSql} {$sortClause} LIMIT {$limit} OFFSET {$offset}";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        return [
            'total' => $total,
            'items' => $items,
            'limit' => $limit,
            'offset' => $offset,
            'facets' => $this->generateFacetsMySql($whereSql, $params),
        ];
    }

    private function generateFacetsMySql(string $whereSql, array $params): array
    {
        $db = Database::getReadConnection();
        $stmt = $db->prepare("SELECT c.id, c.name, COUNT(p.id) as count FROM `products` p JOIN `categories` c ON p.category_id = c.id WHERE {$whereSql} GROUP BY c.id, c.name ORDER BY count DESC LIMIT 10");
        $stmt->execute($params);
        $categories = $stmt->fetchAll();

        // Calculate price and MOQ bounds
        $statsStmt = $db->prepare("SELECT MIN(COALESCE(NULLIF(p.sale_price, 0), p.base_price, p.price)) as min_price, MAX(COALESCE(NULLIF(p.sale_price, 0), p.base_price, p.price)) as max_price, MIN(p.moq) as min_moq, MAX(p.moq) as max_moq FROM `products` p WHERE p.status = 'active'");
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        // Calculate live matching counts per filter attribute option
        $optCounts = [];
        try {
            $optCountStmt = $db->prepare("
                SELECT pfav.option_id, COUNT(DISTINCT p.id) as total_count 
                FROM `products` p 
                JOIN `product_filter_attribute_values` pfav ON p.id = pfav.product_id 
                WHERE {$whereSql} AND pfav.option_id IS NOT NULL 
                GROUP BY pfav.option_id
            ");
            $optCountStmt->execute($params);
            $rows = $optCountStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $optCounts[(int)$r['option_id']] = (int)$r['total_count'];
            }
        } catch (\Throwable $e) {}

        return [
            'categories' => $categories,
            'option_counts' => $optCounts,
            'stats' => [
                'min_price' => (float)($stats['min_price'] ?? 0),
                'max_price' => (float)($stats['max_price'] ?? 5000),
                'min_moq'   => (int)($stats['min_moq'] ?? 1),
                'max_moq'   => (int)($stats['max_moq'] ?? 100),
            ],
        ];
    }

    private function searchElasticsearch(string $query, array $filters, int $limit, int $offset): array
    {
        // Simulated Elasticsearch endpoint HTTP client mapping
        throw new \Exception("Elasticsearch cluster not configured.");
    }
}
