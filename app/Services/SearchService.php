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
            $where[] = "(p.`title` LIKE :q OR p.`sku` LIKE :q OR p.`short_description` LIKE :q)";
            $params['q'] = '%' . trim($query) . '%';
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.`category_id` = :cat_id";
            $params['cat_id'] = (int)$filters['category_id'];
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
            'price_asc' => 'ORDER BY p.`base_price` ASC',
            'price_desc' => 'ORDER BY p.`base_price` DESC',
            'newest'     => 'ORDER BY p.`id` DESC',
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
