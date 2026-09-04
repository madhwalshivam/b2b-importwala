<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class VisualSearchService
{
    /**
     * Thresholds for visual search matching:
     * - STRONG_MATCH_THRESHOLD (>= 0.75): "Matching Products"
     * - RELATED_MATCH_THRESHOLD (0.60 - 0.75): "Similar Products You Might Like"
     * - Below 0.60: Fallback to Catalog Trending/Featured Products
     */
    public const STRONG_MATCH_THRESHOLD = 0.85;
    public const RELATED_MATCH_THRESHOLD = 0.70;

    private static array $categoryHierarchyCache = [];

    private PDO $db;
    private string $publicDir;
    private string $microserviceUrl = 'http://127.0.0.1:5005';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->publicDir = rtrim(realpath(__DIR__ . '/../../public'), '/\\');
    }

    /**
     * Check if two categories are identical or belong to the same parent family.
     */
    /**
     * Check if two categories are identical or closely related (same category or direct subcategory family).
     * Top-level root categories (e.g., 'Jewelry' root ID 1) are excluded so sibling subcategories (Bracelets vs Earrings vs Rings)
     * are NOT falsely matched as related.
     */
    public function areCategoriesRelated(?int $catId1, ?int $catId2): bool
    {
        if (empty($catId1) || empty($catId2)) {
            return false;
        }

        $c1 = (int)$catId1;
        $c2 = (int)$catId2;

        if ($c1 === $c2) {
            return true;
        }

        $this->loadCategoryHierarchy();

        $p1 = self::$categoryHierarchyCache[$c1]['parent_id'] ?? 0;
        $p2 = self::$categoryHierarchyCache[$c2]['parent_id'] ?? 0;

        // Direct Parent-Child relationship
        if ($p1 === $c2 || $p2 === $c1) {
            return true;
        }

        // Shared parent ONLY if parent is not a top-level root category (parent_id > 0 and parent's parent_id > 0)
        if ($p1 > 0 && $p1 === $p2) {
            $grandparent = self::$categoryHierarchyCache[$p1]['parent_id'] ?? 0;
            if ($grandparent > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get top-level root category ID for a category.
     */
    private function getRootCategoryId(int $catId): int
    {
        if ($catId <= 0) return 0;

        $this->loadCategoryHierarchy();

        $curr = $catId;
        $visited = [];

        while ($curr > 0 && !isset($visited[$curr])) {
            $visited[$curr] = true;
            $parent = self::$categoryHierarchyCache[$curr]['parent_id'] ?? 0;
            if ($parent > 0) {
                $curr = (int)$parent;
            } else {
                break;
            }
        }

        return $curr;
    }

    private function loadCategoryHierarchy(): void
    {
        if (!empty(self::$categoryHierarchyCache)) {
            return;
        }

        try {
            $stmt = $this->db->query("SELECT id, name, parent_id FROM categories");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                self::$categoryHierarchyCache[(int)$row['id']] = [
                    'name'      => $row['name'],
                    'parent_id' => $row['parent_id'] ? (int)$row['parent_id'] : 0,
                ];
            }
        } catch (\Throwable $e) {
            self::$categoryHierarchyCache = [];
        }
    }

    /**
     * Generate visual embedding vector (128-dim normalized float array) using Python microservice or CLI fallback.
     */
    public function generateEmbedding(string $imagePath): ?array
    {
        $resolvedPath = $this->resolveImagePath($imagePath);
        if (!$resolvedPath) {
            return null;
        }

        if (!preg_match('~^https?://~i', $resolvedPath) && !file_exists($resolvedPath)) {
            return null;
        }

        // 1. Try HTTP Microservice first (http://127.0.0.1:5005/embed)
        $httpRes = $this->callMicroserviceEmbed($resolvedPath);
        if (!empty($httpRes) && is_array($httpRes)) {
            return $httpRes;
        }

        // 2. CLI Fallback execution if HTTP microservice is unreachable
        return $this->callCliEmbed($resolvedPath);
    }

    /**
     * Index a single product by ID (main_image + gallery images + variant images).
     */
    public function indexProduct(int $productId): bool
    {
        $stmt = $this->db->prepare("SELECT id, main_image FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return false;
        }

        $imagesToIndex = [];

        // 1. Cover Main Image
        if (!empty($product['main_image'])) {
            $path = trim($product['main_image']);
            $imagesToIndex[$path] = [
                'image_path' => $path,
                'image_type' => 'main',
                'image_id'   => null,
                'variant_id' => null,
            ];
        }

        // 2. Gallery Images
        $stmtGal = $this->db->prepare("SELECT id, image_url, image_path, variation_value_id FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC");
        $stmtGal->execute([$productId]);
        $gallery = $stmtGal->fetchAll(PDO::FETCH_ASSOC);

        foreach ($gallery as $g) {
            $path = trim($g['image_url'] ?: $g['image_path'] ?? '');
            if (empty($path)) continue;

            if (!isset($imagesToIndex[$path])) {
                $imagesToIndex[$path] = [
                    'image_path' => $path,
                    'image_type' => 'gallery',
                    'image_id'   => (int)$g['id'],
                    'variant_id' => !empty($g['variation_value_id']) ? (int)$g['variation_value_id'] : null,
                ];
            } else {
                if (empty($imagesToIndex[$path]['image_id'])) {
                    $imagesToIndex[$path]['image_id'] = (int)$g['id'];
                }
                if (empty($imagesToIndex[$path]['variant_id']) && !empty($g['variation_value_id'])) {
                    $imagesToIndex[$path]['variant_id'] = (int)$g['variation_value_id'];
                }
            }
        }

        // 3. Variant Images
        $stmtVar = $this->db->prepare("SELECT id, image_url FROM product_variants WHERE product_id = ? AND is_active = 1");
        $stmtVar->execute([$productId]);
        $variants = $stmtVar->fetchAll(PDO::FETCH_ASSOC);

        foreach ($variants as $v) {
            $path = trim($v['image_url'] ?? '');
            if (empty($path)) continue;

            if (!isset($imagesToIndex[$path])) {
                $imagesToIndex[$path] = [
                    'image_path' => $path,
                    'image_type' => 'variant',
                    'image_id'   => null,
                    'variant_id' => (int)$v['id'],
                ];
            } else {
                if (empty($imagesToIndex[$path]['variant_id'])) {
                    $imagesToIndex[$path]['variant_id'] = (int)$v['id'];
                }
            }
        }

        if (empty($imagesToIndex)) {
            return false;
        }

        $stmtSave = $this->db->prepare("
            INSERT INTO product_image_embeddings (product_id, image_id, variant_id, image_type, image_path, embedding_vector, dhash, generated_at)
            VALUES (:pid, :img_id, :var_id, :img_type, :img_path, :vec, :dhash, NOW())
            ON DUPLICATE KEY UPDATE
                image_id = VALUES(image_id),
                variant_id = VALUES(variant_id),
                image_type = VALUES(image_type),
                embedding_vector = VALUES(embedding_vector),
                dhash = VALUES(dhash),
                generated_at = NOW()
        ");

        $indexedCount = 0;

        foreach ($imagesToIndex as $imgData) {
            $imgPath = $imgData['image_path'];
            $embedding = $this->generateEmbedding($imgPath);

            if (!$embedding) {
                continue;
            }

            $jsonEmbedding = json_encode($embedding);
            $dhash = substr(md5($jsonEmbedding), 0, 16);

            $ok = $stmtSave->execute([
                'pid'      => $productId,
                'img_id'   => $imgData['image_id'],
                'var_id'   => $imgData['variant_id'],
                'img_type' => $imgData['image_type'],
                'img_path' => $imgPath,
                'vec'      => $jsonEmbedding,
                'dhash'    => $dhash,
            ]);

            if ($ok) {
                $indexedCount++;
            }
        }

        return $indexedCount > 0;
    }

    /**
     * Index all active products in the database (main + gallery + all variant images).
     */
    public function indexAllProducts(bool $forceReindex = false): array
    {
        if ($forceReindex) {
            $this->db->exec("TRUNCATE TABLE product_image_embeddings");
        }

        $query = "SELECT id, name, main_image FROM products WHERE status = 'active'";
        if (!$forceReindex) {
            $query .= " AND id NOT IN (SELECT product_id FROM product_image_embeddings)";
        }

        $stmt = $this->db->query($query);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $successCount = 0;
        $failCount = 0;

        foreach ($products as $p) {
            if ($this->indexProduct((int)$p['id'])) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $totalImagesIndexed = (int)$this->db->query("SELECT COUNT(*) FROM product_image_embeddings")->fetchColumn();

        return [
            'total'                => count($products),
            'indexed'              => $successCount,
            'failed'               => $failCount,
            'total_images_indexed' => $totalImagesIndexed,
        ];
    }

    /**
     * Perform Visual Search on an uploaded image file across all catalog image embeddings.
     */
    public function searchByUploadedImage(string $uploadedTmpPath, ?string $categoryFilter = null, int $limit = 12): array
    {
        $this->ensureCatalogIndexed();

        // 1. Generate embedding for query image
        $queryVector = $this->generateEmbedding($uploadedTmpPath);

        // Delete temporary upload file after generating vector
        if (file_exists($uploadedTmpPath) && strpos($uploadedTmpPath, 'temp_visual_search') !== false) {
            @unlink($uploadedTmpPath);
        }

        if (!$queryVector) {
            $fallback = $this->getFallbackTrendingProducts($limit, 'Image could not be parsed. Check out our top wholesale items:');
            $fallback['image_parse_error'] = true;
            return $fallback;
        }

        // 2. Fetch all stored embeddings from database
        $storedEmbeddings = $this->getAllStoredEmbeddings($categoryFilter);
        if (empty($storedEmbeddings)) {
            return $this->getFallbackTrendingProducts($limit, 'Catalog vector index updating. Check out our top wholesale items:');
        }

        // 3. Compute Cosine Similarity for each target image embedding and aggregate max score per product
        $bestPerProduct = [];

        foreach ($storedEmbeddings as $row) {
            $targetVector = json_decode($row['embedding_vector'], true);
            if (!$targetVector || !is_array($targetVector)) {
                continue;
            }

            $score = $this->calculateCosineSimilarity($queryVector, $targetVector);
            $pid = (int)$row['product_id'];

            if (!isset($bestPerProduct[$pid]) || $score > $bestPerProduct[$pid]['raw_score']) {
                $bestPerProduct[$pid] = [
                    'row'       => $row,
                    'raw_score' => $score,
                ];
            }
        }

        $matchingProducts = [];
        $similarProducts  = [];

        foreach ($bestPerProduct as $pid => $best) {
            $row = $best['row'];
            $score = $best['raw_score'];
            $scorePercent = round($score * 100, 1);

            $displayImage = !empty($row['image_path']) ? $row['image_path'] : $row['main_image'];

            $item = [
                'id'                 => $pid,
                'name'               => $row['name'],
                'slug'               => $row['slug'],
                'price'              => number_format((float)($row['sale_price'] > 0 ? $row['sale_price'] : $row['price']), 2, '.', ''),
                'main_image'         => $row['main_image'],
                'matched_image'      => $displayImage,
                'matched_image_type' => $row['image_type'] ?? 'main',
                'matched_variant_id' => $row['variant_id'] ?? null,
                'image_url'          => $this->formatImageUrl($displayImage),
                'category_name'      => $row['category_name'] ?? 'Wholesale',
                'similarity_score'   => $scorePercent,
                'raw_score'          => $score,
            ];

            if ($score >= self::STRONG_MATCH_THRESHOLD) {
                $item['match_badge'] = 'Exact Match (' . $scorePercent . '%)';
                $item['match_type']  = 'strong';
                $matchingProducts[]  = $item;
            } elseif ($score >= self::RELATED_MATCH_THRESHOLD) {
                $item['match_badge'] = 'Similar Match (' . $scorePercent . '%)';
                $item['match_type']  = 'related';
                $similarProducts[]   = $item;
            }
        }

        // Sort both buckets by highest similarity score
        usort($matchingProducts, fn($a, $b) => $b['raw_score'] <=> $a['raw_score']);
        usort($similarProducts,  fn($a, $b) => $b['raw_score'] <=> $a['raw_score']);

        $combinedResults = array_merge($matchingProducts, $similarProducts);

        // Check if top match is strong (>= 0.85) -> auto redirect
        if (!empty($matchingProducts) && $matchingProducts[0]['raw_score'] >= self::STRONG_MATCH_THRESHOLD) {
            $topMatch = $matchingProducts[0];
            $redirectUrl = function_exists('url') ? url('product/' . $topMatch['slug']) : '/importwala/product/' . $topMatch['slug'];
            return [
                'has_matches'       => true,
                'auto_redirect'     => true,
                'redirect_url'      => $redirectUrl,
                'top_match'         => $topMatch,
                'strong_count'      => count($matchingProducts),
                'related_count'     => count($similarProducts),
                'total'             => count($combinedResults),
                'headline'          => 'Exact Visual Match Found! Redirecting...',
                'items'             => array_slice($combinedResults, 0, $limit),
            ];
        }

        if (!empty($combinedResults)) {
            return [
                'has_matches'       => true,
                'auto_redirect'     => false,
                'strong_count'      => count($matchingProducts),
                'related_count'     => count($similarProducts),
                'total'             => count($combinedResults),
                'headline'          => 'Similar Products You Might Like',
                'items'             => array_slice($combinedResults, 0, $limit),
            ];
        }

        // 4. If zero matches >= 0.60, return Fallback Trending/Featured Products
        return $this->getFallbackTrendingProducts($limit, 'No exact visual match found (< 60%). Check out our top wholesale items:');
    }

    /**
     * Search products visually similar to an existing product ID.
     */
    public function searchByProductId(int $productId, int $limit = 12, float $minThreshold = 0.80): array
    {
        $this->ensureCatalogIndexed();

        // Fetch query product main image embedding
        $stmtProd = $this->db->prepare("
            SELECT p.category_id, e.embedding_vector 
            FROM products p 
            LEFT JOIN product_image_embeddings e ON p.id = e.product_id 
            WHERE p.id = ? LIMIT 1
        ");
        $stmtProd->execute([$productId]);
        $queryProduct = $stmtProd->fetch(PDO::FETCH_ASSOC);

        if (!$queryProduct || empty($queryProduct['embedding_vector'])) {
            $this->indexProduct($productId);
            $stmtProd->execute([$productId]);
            $queryProduct = $stmtProd->fetch(PDO::FETCH_ASSOC);
        }

        if (!$queryProduct || empty($queryProduct['embedding_vector'])) {
            return [
                'has_matches' => false,
                'total'       => 0,
                'headline'    => 'Similar Products',
                'items'       => [],
            ];
        }

        $queryCatId = !empty($queryProduct['category_id']) ? (int)$queryProduct['category_id'] : 0;
        $queryVector = json_decode($queryProduct['embedding_vector'], true);

        if (!$queryVector || !is_array($queryVector)) {
            return [
                'has_matches' => false,
                'total'       => 0,
                'headline'    => 'Similar Products',
                'items'       => [],
            ];
        }

        $storedEmbeddings = $this->getAllStoredEmbeddings();
        $bestPerTarget = [];

        foreach ($storedEmbeddings as $target) {
            $targetPid = (int)$target['product_id'];
            if ($targetPid === $productId) {
                continue;
            }

            // Category Relevance Filter: Require exact category match or strict subcategory relation
            $targetCatId = !empty($target['category_id']) ? (int)$target['category_id'] : 0;
            if ($queryCatId > 0 && $targetCatId > 0 && !$this->areCategoriesRelated($queryCatId, $targetCatId)) {
                continue;
            }

            $targetVector = json_decode($target['embedding_vector'], true);
            if (!$targetVector || !is_array($targetVector)) continue;

            $score = $this->calculateCosineSimilarity($queryVector, $targetVector);

            if ($score < $minThreshold) {
                continue;
            }

            if (!isset($bestPerTarget[$targetPid]) || $score > $bestPerTarget[$targetPid]['raw_score']) {
                $bestPerTarget[$targetPid] = [
                    'target'    => $target,
                    'raw_score' => $score,
                ];
            }
        }

        $results = [];
        foreach ($bestPerTarget as $targetPid => $best) {
            $target = $best['target'];
            $score = $best['raw_score'];
            $scorePercent = round($score * 100, 1);

            $results[] = [
                'id'               => $targetPid,
                'name'             => $target['name'],
                'slug'             => $target['slug'],
                'price'            => (float)($target['price'] ?? 0),
                'sale_price'       => !empty($target['sale_price']) ? (float)$target['sale_price'] : null,
                'moq'              => (int)($target['moq'] ?? 1),
                'total_sold'       => (int)($target['total_sold'] ?? 0),
                'is_new'           => !empty($target['is_new']),
                'is_featured'      => !empty($target['is_featured']),
                'is_free_shipping' => !isset($target['is_free_shipping']) || !empty($target['is_free_shipping']),
                'main_image'       => $target['main_image'],
                'matched_image'    => $target['image_path'],
                'image_url'        => $this->formatImageUrl($target['main_image']),
                'category_name'    => $target['category_name'] ?? 'Wholesale',
                'category_id'      => !empty($target['category_id']) ? (int)$target['category_id'] : 0,
                'similarity_score' => $scorePercent,
                'raw_score'        => $score,
            ];
        }

        usort($results, fn($a, $b) => $b['raw_score'] <=> $a['raw_score']);

        return [
            'has_matches' => !empty($results),
            'total'       => count($results),
            'headline'    => 'Similar Products',
            'items'       => array_slice($results, 0, $limit),
        ];
    }

    /**
     * Calculate Cosine Similarity between two L2-normalized vectors.
     */
    public function calculateCosineSimilarity(array $vec1, array $vec2): float
    {
        $count = min(count($vec1), count($vec2));
        if ($count === 0) return 0.0;

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $a = (float)$vec1[$i];
            $b = (float)$vec2[$i];
            $dotProduct += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 0 || $normB <= 0) return 0.0;

        return max(0.0, min(1.0, $dotProduct / (sqrt($normA) * sqrt($normB))));
    }

    /**
     * Get fallback trending & featured catalog products when visual search has no close matches.
     */
    public function getFallbackTrendingProducts(int $limit = 12, string $headline = 'Top Trending Wholesale Products'): array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.main_image, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
            ORDER BY p.is_featured DESC, p.views_count DESC, p.id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            $item['image_url'] = $this->formatImageUrl($item['main_image']);
            $item['similarity_score'] = 0.0;
            $item['raw_score'] = 0.0;
            $item['match_badge'] = 'Featured Fallback';
            $item['is_fallback'] = true;
        }

        return [
            'has_matches' => false,
            'is_fallback' => true,
            'total'       => count($items),
            'headline'    => $headline,
            'items'       => $items,
        ];
    }

    /**
     * Admin / Debug tool helper to analyze an image against catalog embeddings and return top matches aggregated per product.
     */
    public function getDebugAnalysis(string $imagePath): array
    {
        $startTime = microtime(true);
        $resolvedPath = $this->resolveImagePath($imagePath);

        $queryVector = $resolvedPath ? $this->generateEmbedding($resolvedPath) : null;
        $genTimeMs = round((microtime(true) - $startTime) * 1000, 2);

        $stored = $this->getAllStoredEmbeddings();
        $bestPerProduct = [];
        $topMatches = [];

        if ($queryVector) {
            foreach ($stored as $row) {
                $targetVec = json_decode($row['embedding_vector'], true);
                if (!$targetVec || !is_array($targetVec)) continue;

                $rawScore = $this->calculateCosineSimilarity($queryVector, $targetVec);
                $pid = (int)$row['product_id'];

                if (!isset($bestPerProduct[$pid]) || $rawScore > $bestPerProduct[$pid]['raw_score']) {
                    $bestPerProduct[$pid] = [
                        'row'       => $row,
                        'raw_score' => $rawScore,
                    ];
                }
            }

            foreach ($bestPerProduct as $pid => $best) {
                $row = $best['row'];
                $rawScore = $best['raw_score'];
                $pct = round($rawScore * 100, 2);

                $category = 'Below Threshold (< 60%)';
                if ($rawScore >= self::STRONG_MATCH_THRESHOLD) {
                    $category = 'Exact / Strong Match (>= 85%)';
                } elseif ($rawScore >= self::RELATED_MATCH_THRESHOLD) {
                    $category = 'Similar Match (60% - 85%)';
                }

                $displayImage = !empty($row['image_path']) ? $row['image_path'] : $row['main_image'];

                $topMatches[] = [
                    'product_id'       => $pid,
                    'name'             => $row['name'],
                    'slug'             => $row['slug'],
                    'main_image'       => $row['main_image'],
                    'matched_image'    => $displayImage,
                    'matched_type'     => $row['image_type'] ?? 'main',
                    'variant_id'       => $row['variant_id'] ?? null,
                    'image_url'        => $this->formatImageUrl($displayImage),
                    'category_name'    => $row['category_name'] ?? 'Wholesale',
                    'raw_score'        => number_format($rawScore, 4, '.', ''),
                    'score_float'      => $rawScore,
                    'similarity_pct'   => $pct,
                    'match_category'   => $category,
                ];
            }

            usort($topMatches, fn($a, $b) => $b['score_float'] <=> $a['score_float']);
        }

        $totalActive = (int)$this->db->query("SELECT COUNT(id) FROM products WHERE status = 'active'")->fetchColumn();
        $totalIndexed = (int)$this->db->query("SELECT COUNT(id) FROM product_image_embeddings")->fetchColumn();

        return [
            'query_image_path'       => $imagePath,
            'resolved_path'          => $resolvedPath,
            'embedding_generated'    => !empty($queryVector),
            'vector_dimensions'      => $queryVector ? count($queryVector) : 0,
            'dhash'                  => $queryVector ? substr(md5(json_encode($queryVector)), 0, 16) : null,
            'embedding_time_ms'      => $genTimeMs,
            'total_active_products'  => $totalActive,
            'total_indexed_products' => $totalIndexed,
            'top_5_matches'          => array_slice($topMatches, 0, 5),
        ];
    }

    // =========================================================================
    //  PRIVATE UTILITIES & MICROSERVICE HTTP CLIENT
    // =========================================================================

    private function callMicroserviceEmbed(string $resolvedPath): ?array
    {
        try {
            $ch = curl_init($this->microserviceUrl . '/embed');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);

            if (preg_match('~^https?://~i', $resolvedPath)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['image_path' => $resolvedPath]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            } elseif (file_exists($resolvedPath) && class_exists('\CURLFile')) {
                $mime = mime_content_type($resolvedPath) ?: 'image/jpeg';
                $cfile = new \CURLFile($resolvedPath, $mime, basename($resolvedPath));
                curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $cfile]);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['image_path' => $resolvedPath]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                if (!empty($data['success']) && !empty($data['embedding'])) {
                    return $data['embedding'];
                }
            } else {
                echo "[CURL ERROR] HTTP $httpCode, CurlErr: $curlErr, Response: $response\n";
            }
        } catch (\Throwable $e) {
            echo "[CURL EXCEPTION] " . $e->getMessage() . "\n";
        }

        return null;
    }

    private function callCliEmbed(string $resolvedPath): ?array
    {
        try {
            $scriptPath = ROOT_PATH . '/scripts/visual_embedding_service.py';
            $cmd = "python " . escapeshellarg($scriptPath) . " --image " . escapeshellarg($resolvedPath);
            $output = shell_exec($cmd);
            if ($output) {
                $data = json_decode(trim($output), true);
                if (!empty($data['success']) && !empty($data['embedding'])) {
                    return $data['embedding'];
                }
            }
        } catch (\Throwable $e) {
            // CLI execution error
        }

        return null;
    }

    private function getAllStoredEmbeddings(?string $categoryFilter = null): array
    {
        $where = ["p.status = 'active'"];
        $params = [];

        if (!empty($categoryFilter) && $categoryFilter !== 'all') {
            $where[] = "(c.name LIKE :cat OR c.slug LIKE :cat OR p.name LIKE :cat OR p.tags LIKE :cat)";
            $params['cat'] = '%' . $categoryFilter . '%';
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT e.product_id, e.image_id, e.variant_id, e.image_type, e.image_path, e.embedding_vector, p.name, p.slug, p.price, p.sale_price, p.moq, p.total_sold, p.is_new, p.is_featured, p.is_free_shipping, p.main_image, p.category_id, c.name as category_name
            FROM product_image_embeddings e
            JOIN products p ON e.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereSql}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function resolveImagePath(string $path): ?string
    {
        $path = trim($path);
        if (empty($path)) return null;

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        if (file_exists($path)) {
            return realpath($path);
        }

        $clean = ltrim(parse_url($path, PHP_URL_PATH), '/');
        $clean = preg_replace('~^importwala/~i', '', $clean);
        
        $candidate1 = $this->publicDir . '/' . ltrim($clean, '/');
        if (file_exists($candidate1)) return realpath($candidate1);

        $candidate2 = ROOT_PATH . '/' . ltrim($clean, '/');
        if (file_exists($candidate2)) return realpath($candidate2);

        return null;
    }

    private function formatImageUrl(?string $imagePath): string
    {
        $path = !empty($imagePath) ? $imagePath : 'assets/images/placeholder.jpg';
        if (preg_match('~^https?://~i', $path)) return $path;

        if (function_exists('asset')) {
            return asset($path);
        }

        return '/importwala/public/' . ltrim($path, '/');
    }

    private function ensureCatalogIndexed(): void
    {
        try {
            $totalP = (int)$this->db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
            $totalSig = (int)$this->db->query("SELECT COUNT(*) FROM product_image_embeddings")->fetchColumn();

            if ($totalP > 0 && $totalSig < ($totalP * 0.8)) {
                $this->indexAllProducts(false);
            }
        } catch (\Throwable $e) {
            // Silence indexing check errors
        }
    }
}
