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
            $where[] = "(p.`title` LIKE :q OR p.`name` LIKE :q OR p.`sku` LIKE :q OR p.`description` LIKE :q OR p.`tags` LIKE :q)";
            $params['q'] = '%' . trim($query) . '%';
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.`category_id` = :cat_id";
            $params['cat_id'] = (int)$filters['category_id'];
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

        if (!empty($filters['min_price'])) {
            $where[] = "p.`base_price` >= :min_price";
            $params['min_price'] = (float)$filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = "p.`base_price` <= :max_price";
            $params['max_price'] = (float)$filters['max_price'];
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

        return [
            'categories' => $categories,
        ];
    }

    private function searchElasticsearch(string $query, array $filters, int $limit, int $offset): array
    {
        // Simulated Elasticsearch endpoint HTTP client mapping
        throw new \Exception("Elasticsearch cluster not configured.");
    }
}
