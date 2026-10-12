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
    /** @var array<int, int[]> */
    private array $scopeCache = [];

    public function __construct()
    {
        parent::__construct();
        $this->productRepo = new ProductRepository();
        $this->esEndpoint = env('ELASTICSEARCH_HOST');
        if ($this->esEndpoint) {
            $this->useElasticsearch = true;
        }
    }

    /**
     * Search products with query, category filter, price range, and faceted sorting
     */
    public function search(string $query, array $filters = [], int $limit = 24, int $offset = 0): array
    {
        $cacheKey = "search:v3:" . md5($query . json_encode($filters) . "_{$limit}_{$offset}");
        
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
        $scoreParams = [];
        $relevanceSql = "0";

        if (!empty($query)) {
            $words = array_filter(explode(' ', preg_replace('/\s+/', ' ', trim(strtolower($query)))));
            $whereWords = [];
            $scoreExprs = [];

            foreach ($words as $idx => $w) {
                $s_exact = "s_exact_" . $idx;
                $s_name  = "s_name_" . $idx;
                $s_sku   = "s_sku_" . $idx;
                $s_desc  = "s_desc_" . $idx;
                $s_tags  = "s_tags_" . $idx;

                $w_name  = "w_name_" . $idx;
                $w_sku   = "w_sku_" . $idx;
                $w_desc  = "w_desc_" . $idx;
                $w_tags  = "w_tags_" . $idx;

                $scoreParams[$s_exact] = '[[:<:]]' . preg_quote($w, '/') . '[[:>:]]';
                $scoreParams[$s_name]  = '%' . $w . '%';
                $scoreParams[$s_sku]   = '%' . $w . '%';
                $scoreParams[$s_desc]  = '[[:<:]]' . preg_quote($w, '/') . '[[:>:]]';
                $scoreParams[$s_tags]  = '%' . $w . '%';

                $params[$w_name]  = '%' . $w . '%';
                $params[$w_sku]   = '%' . $w . '%';
                $params[$w_desc]  = '[[:<:]]' . preg_quote($w, '/') . '[[:>:]]';
                $params[$w_tags]  = '%' . $w . '%';

                $scoreExprs[] = "
                    (CASE WHEN LOWER(p.`name`) REGEXP :{$s_exact} THEN 100 ELSE 0 END) +
                    (CASE WHEN LOWER(p.`name`) LIKE :{$s_name} THEN 40 ELSE 0 END) +
                    (CASE WHEN p.`tags` IS NOT NULL AND LOWER(p.`tags`) LIKE :{$s_tags} THEN 30 ELSE 0 END) +
                    (CASE WHEN LOWER(p.`sku`) LIKE :{$s_sku} THEN 25 ELSE 0 END) +
                    (CASE WHEN LOWER(p.`description`) REGEXP :{$s_desc} THEN 10 ELSE 0 END)
                ";

                $whereWords[] = "(LOWER(p.`name`) LIKE :{$w_name} OR LOWER(p.`sku`) LIKE :{$w_sku} OR (p.`tags` IS NOT NULL AND LOWER(p.`tags`) LIKE :{$w_tags}) OR LOWER(p.`description`) REGEXP :{$w_desc})";
            }

            if (!empty($whereWords)) {
                $where[] = "(" . implode(" AND ", $whereWords) . ")";
            }
            if (!empty($scoreExprs)) {
                $relevanceSql = "(" . implode(" + ", $scoreExprs) . ")";
            }
        }

        $this->applyCategoryScope($where, $params, $filters, $db);

        if (!empty($filters['brand_id'])) {
            $where[] = "p.`brand_id` = :brand_id";
            $params['brand_id'] = (int)$filters['brand_id'];
        }

        if (!empty($filters['factory_ids']) && is_array($filters['factory_ids'])) {
            $ids = array_map('intval', array_filter($filters['factory_ids']));
            if (!empty($ids)) {
                $where[] = "p.`factory_id` IN (" . implode(',', $ids) . ")";
            }
        } elseif (!empty($filters['factory_id'])) {
            $where[] = "p.`factory_id` = :factory_id";
            $params['factory_id'] = (int)$filters['factory_id'];
        }

        if (!empty($filters['collection_id'])) {
            $where[] = "p.`id` IN (SELECT `product_id` FROM `collection_card_products` WHERE `collection_card_id` = :collection_id)";
            $params['collection_id'] = (int)$filters['collection_id'];
        }

        if (!empty($filters['is_top_deals']) || (!empty($filters['section_id']) && (int)$filters['section_id'] === 999999)) {
            $checkStmt = $db->query("SELECT COUNT(*) FROM `top_deals_products`");
            $mappedCount = (int)($checkStmt ? $checkStmt->fetchColumn() : 0);
            if ($mappedCount > 0) {
                $where[] = "p.`id` IN (SELECT `product_id` FROM `top_deals_products`)";
            } else {
                $where[] = "(p.`sale_price` IS NOT NULL AND p.`sale_price` > 0 AND p.`sale_price` < p.`price`)";
            }
        } elseif (!empty($filters['section_id'])) {
            $secId = (int)$filters['section_id'];
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM `homepage_section_products` WHERE `section_id` = ?");
            $checkStmt->execute([$secId]);
            $mappedCount = (int)$checkStmt->fetchColumn();

            if ($mappedCount > 0) {
                $where[] = "p.`id` IN (SELECT `product_id` FROM `homepage_section_products` WHERE `section_id` = :sec_id)";
                $params['sec_id'] = $secId;
            } else {
                $secStmt = $db->prepare("SELECT * FROM `homepage_sections` WHERE id = ? LIMIT 1");
                $secStmt->execute([$secId]);
                $secRow = $secStmt->fetch();
                if ($secRow) {
                    $secKey = $secRow['section_key'] ?? '';
                    if ($secKey === 'featured_products') {
                        $where[] = "p.`is_featured` = 1";
                    } elseif ($secKey === 'best_sellers') {
                        $where[] = "p.`is_best_seller` = 1";
                    } elseif ($secKey === 'new_arrivals') {
                        $where[] = "p.`is_new_arrival` = 1";
                    }
                }
            }
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
            $where[] = "COALESCE(NULLIF(p.`sale_price`, 0), p.`price`) >= :min_price";
            $params['min_price'] = (float)$filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '' && $filters['max_price'] !== null) {
            $where[] = "COALESCE(NULLIF(p.`sale_price`, 0), p.`price`) <= :max_price";
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

        // Dynamic Filter Attributes (AND logic across attributes, OR logic within options).
        // Applied only after the category scope above.
        if (!empty($filters['attr']) && is_array($filters['attr'])) {
            $categoryIdForAttrs = (int) ($filters['category_id'] ?? 0);
            $scopeIds = $categoryIdForAttrs > 0 ? $this->categoryScopeIds($categoryIdForAttrs) : [];
            $scopeIn = implode(',', array_map('intval', $scopeIds));
            $attrParamIdx = 1;
            foreach ($filters['attr'] as $attrIdOrSlug => $selectedOpts) {
                if (empty($selectedOpts)) continue;

                if (!is_array($selectedOpts)) {
                    $selectedOpts = array_filter(array_map('trim', explode(',', (string)$selectedOpts)));
                }

                $numericOptIds = array_values(array_filter(array_map('intval', $selectedOpts)));
                if (empty($numericOptIds)) continue;

                $attrSlug = '';
                if (is_numeric($attrIdOrSlug) && (int) $attrIdOrSlug > 0) {
                    $slugStmt = $db->prepare("SELECT `slug` FROM `filter_attributes` WHERE `id` = ? LIMIT 1");
                    $slugStmt->execute([(int) $attrIdOrSlug]);
                    $attrSlug = strtolower((string) $slugStmt->fetchColumn());
                }

                if ($attrSlug === 'subcategory' || $attrSlug === 'category') {
                    $optionValues = $this->optionValuesByIds($db, $numericOptIds);
                    if ($attrSlug === 'subcategory') {
                        $matchedIds = $this->matchingSubcategoryIds($db, $optionValues, $scopeIds);
                        if (empty($matchedIds)) {
                            $where[] = '1 = 0';
                        } else {
                            $where[] = 'p.`subcategory_id` IN (' . implode(',', $matchedIds) . ')';
                        }
                    } else {
                        $matchedIds = $this->matchingCategoryIds($db, $optionValues, $scopeIds);
                        if (empty($matchedIds)) {
                            $where[] = '1 = 0';
                        } else {
                            $where[] = 'p.`category_id` IN (' . implode(',', $matchedIds) . ')';
                        }
                    }
                    continue;
                }

                $inPlaceholders = [];
                foreach ($numericOptIds as $optVal) {
                    $pKey = "attr_opt_" . $attrParamIdx++;
                    $inPlaceholders[] = ":" . $pKey;
                    $params[$pKey] = $optVal;
                }
                $inSql = implode(',', $inPlaceholders);
                $attrClause = "`option_id` IN ({$inSql})";
                if (is_numeric($attrIdOrSlug) && (int) $attrIdOrSlug > 0) {
                    $attrKey = "attr_id_" . $attrParamIdx++;
                    $params[$attrKey] = (int) $attrIdOrSlug;
                    $attrClause = "`attribute_id` = :{$attrKey} AND " . $attrClause;
                }
                if ($scopeIn !== '') {
                    $attrClause .= " AND `product_id` IN (SELECT `id` FROM `products` WHERE `category_id` IN ({$scopeIn}) OR `id` IN (SELECT `product_id` FROM `product_categories` WHERE `category_id` IN ({$scopeIn})))";
                }
                $where[] = "p.`id` IN (SELECT `product_id` FROM `product_filter_attribute_values` WHERE {$attrClause})";
            }
        }

        $sortClause = match ($filters['sort'] ?? 'relevance') {
            'price_asc', 'price_low_high' => 'ORDER BY COALESCE(NULLIF(p.`sale_price`, 0), p.`price`) ASC, p.`id` ASC',
            'price_desc', 'price_high_low' => 'ORDER BY COALESCE(NULLIF(p.`sale_price`, 0), p.`price`) DESC, p.`id` DESC',
            'newest'     => 'ORDER BY p.`id` DESC',
            'popular'    => 'ORDER BY p.`sales_count` DESC, p.`id` DESC',
            default      => (!empty($query) ? 'ORDER BY relevance_score DESC, p.`is_featured` DESC, p.`sales_count` DESC, p.`id` DESC' : 'ORDER BY p.`sales_count` DESC, p.`id` DESC'),
        };

        $whereSql = implode(" AND ", $where);
        
        // Count total matching
        $countStmt = $db->prepare("SELECT COUNT(*) as total FROM `products` p WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int)($countStmt->fetch()['total'] ?? 0);

        // Fetch products with relevance score
        $sql = "SELECT p.*, c.name as category_name, ({$relevanceSql}) as relevance_score FROM `products` p LEFT JOIN `categories` c ON p.category_id = c.id WHERE {$whereSql} {$sortClause} LIMIT {$limit} OFFSET {$offset}";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge($scoreParams, $params));
        $items = $stmt->fetchAll();

        return [
            'total' => $total,
            'items' => $items,
            'limit' => $limit,
            'offset' => $offset,
            'facets' => $this->generateFacetsMySql($whereSql, $params),
        ];
    }

    /**
     * Selected category plus nested child categories.
     * Sibling categories are never included.
     *
     * @return int[]
     */
    public function categoryScopeIds(int $categoryId): array
    {
        if ($categoryId <= 0) {
            return [];
        }
        if (isset($this->scopeCache[$categoryId])) {
            return $this->scopeCache[$categoryId];
        }

        $db = Database::getReadConnection();
        $ids = [$categoryId];
        $frontier = [$categoryId];

        for ($depth = 0; $depth < 6 && !empty($frontier); $depth++) {
            $in = implode(',', array_map('intval', $frontier));
            $rows = $db->query("SELECT `id` FROM `categories` WHERE `parent_id` IN ({$in})")->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $frontier = [];
            foreach ($rows as $id) {
                $id = (int) $id;
                if ($id > 0 && !in_array($id, $ids, true)) {
                    $ids[] = $id;
                    $frontier[] = $id;
                }
            }
        }

        $this->scopeCache[$categoryId] = $ids;
        return $ids;
    }

    /**
     * Category membership is applied before attribute filters.
     * A subcategory filter never replaces the category constraint.
     */
    private function applyCategoryScope(array &$where, array &$params, array $filters, PDO $db): void
    {
        $categoryId = (int) ($filters['category_id'] ?? 0);
        $subcategoryId = (int) ($filters['subcategory_id'] ?? 0);

        if ($subcategoryId > 0) {
            $subStmt = $db->prepare("SELECT `id`, `category_id` FROM `subcategories` WHERE `id` = ? LIMIT 1");
            $subStmt->execute([$subcategoryId]);
            $subRow = $subStmt->fetch(PDO::FETCH_ASSOC);

            if (!$subRow) {
                $where[] = '1 = 0';
                return;
            }

            $ownerId = (int) $subRow['category_id'];
            if ($categoryId > 0) {
                $scope = $this->categoryScopeIds($categoryId);
                if (!in_array($ownerId, $scope, true)) {
                    $where[] = '1 = 0';
                    return;
                }
            } else {
                $categoryId = $ownerId;
            }

            $where[] = "p.`subcategory_id` = :subcat_id";
            $params['subcat_id'] = $subcategoryId;
        }

        if ($categoryId > 0) {
            $scope = $this->categoryScopeIds($categoryId);
            $in = implode(',', array_map('intval', $scope));
            $where[] = "(p.`category_id` IN ({$in}) OR p.`id` IN (SELECT `product_id` FROM `product_categories` WHERE `category_id` IN ({$in})))";
        }
    }

    /**
     * @param int[] $optionIds
     * @return string[]
     */
    private function optionValuesByIds(PDO $db, array $optionIds): array
    {
        $ids = array_values(array_filter(array_map('intval', $optionIds)));
        if (empty($ids)) {
            return [];
        }
        $in = implode(',', $ids);
        $rows = $db->query("SELECT `value` FROM `filter_attribute_options` WHERE `id` IN ({$in})")->fetchAll(PDO::FETCH_COLUMN) ?: [];
        return array_values(array_filter(array_map('strval', $rows)));
    }

    /**
     * Subcategory rows for the selected labels, limited to the open category when one is set.
     *
     * @param string[] $values
     * @param int[] $scopeIds
     * @return int[]
     */
    private function matchingSubcategoryIds(PDO $db, array $values, array $scopeIds): array
    {
        $sql = "SELECT `id`, `name` FROM `subcategories` WHERE (`status` = 'active' OR `status` = 'enabled')";
        if (!empty($scopeIds)) {
            $sql .= ' AND `category_id` IN (' . implode(',', array_map('intval', $scopeIds)) . ')';
        }
        $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $ids = [];
        foreach ($rows as $row) {
            foreach ($values as $value) {
                if ($this->filterLabelsMatch((string) $row['name'], $value)) {
                    $ids[] = (int) $row['id'];
                    break;
                }
            }
        }
        return array_values(array_unique($ids));
    }

    /**
     * Category rows for the selected labels. Inside a category page, labels outside that category are dropped.
     *
     * @param string[] $values
     * @param int[] $scopeIds
     * @return int[]
     */
    private function matchingCategoryIds(PDO $db, array $values, array $scopeIds): array
    {
        $sql = "SELECT `id`, `name` FROM `categories` WHERE (`status` = 'active' OR `status` = 'enabled')";
        if (!empty($scopeIds)) {
            $sql .= ' AND `id` IN (' . implode(',', array_map('intval', $scopeIds)) . ')';
        }
        $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $ids = [];
        foreach ($rows as $row) {
            foreach ($values as $value) {
                if ($this->filterLabelsMatch((string) $row['name'], $value)) {
                    $ids[] = (int) $row['id'];
                    break;
                }
            }
        }
        return array_values(array_unique($ids));
    }

    private function filterLabelsMatch(string $left, string $right): bool
    {
        $norm = static function (string $value): string {
            $value = strtolower(trim($value));
            $value = preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
            if (str_ends_with($value, 'ies') && strlen($value) > 4) {
                $value = substr($value, 0, -3) . 'y';
            } elseif (str_ends_with($value, 's') && !str_ends_with($value, 'ss') && strlen($value) > 3) {
                $value = substr($value, 0, -1);
            }
            return $value;
        };
        return $norm($left) !== '' && $norm($left) === $norm($right);
    }

    private function generateFacetsMySql(string $whereSql, array $params): array
    {
        $db = Database::getReadConnection();
        $stmt = $db->prepare("SELECT c.id, c.name, COUNT(p.id) as count FROM `products` p JOIN `categories` c ON p.category_id = c.id WHERE {$whereSql} GROUP BY c.id, c.name ORDER BY count DESC LIMIT 10");
        $stmt->execute($params);
        $categories = $stmt->fetchAll();

        // Calculate price and MOQ bounds
        $statsStmt = $db->prepare("SELECT MIN(COALESCE(NULLIF(p.sale_price, 0), p.price)) as min_price, MAX(COALESCE(NULLIF(p.sale_price, 0), p.price)) as max_price, MIN(p.moq) as min_moq, MAX(p.moq) as max_moq FROM `products` p WHERE p.status = 'active'");
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
