<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Pure-PHP Visual Search (dHash + aHash + HSV histogram).
 * No external paid APIs; no Python microservice required.
 */
class VisualSearchService
{
    public const STRONG_MATCH_THRESHOLD = 0.85;
    public const RELATED_MATCH_THRESHOLD = 0.55;
    public const MIN_MATCH_THRESHOLD = 0.55;
    /** Confident / "exact" UI when top score is at least this (same bar as strong match). */
    public const EXACT_THRESHOLD = 0.85;
    /** Top must beat #2 by this margin (relaxed — jewelry SKUs often look alike). */
    public const EXACT_SCORE_GAP = 0.03;
    /** Hamming distance (of 64) at/below which hashes count as near-duplicate. */
    public const NEAR_DUP_DHASH_DIST = 8;
    public const WORK_MAX_SIDE = 256;
    public const DOWNLOAD_MAX_BYTES = 8 * 1024 * 1024;

    private static array $categoryHierarchyCache = [];

    private PDO $db;
    private string $publicDir;
    private string $rootPath;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        $resolved = realpath($this->rootPath . '/public');
        $this->publicDir = $resolved ? rtrim($resolved, '/\\') : rtrim($this->rootPath . '/public', '/\\');
        $this->ensureFeaturesTable();
    }

    // =========================================================================
    //  FEATURE EXTRACTION (shared for query + catalog)
    // =========================================================================

    /**
     * Extract comparable visual features from a local path or remote URL.
     *
     * @return array{dhash:string,ahash:string,color_hist:array,dominant_colors:array,aspect_ratio:float}|null
     */
    public function extractFeatures(string $imagePathOrUrl): ?array
    {
        $binary = $this->loadImageBinary($imagePathOrUrl);
        if ($binary === null) {
            return null;
        }

        $img = @imagecreatefromstring($binary);
        if (!$img) {
            error_log('[VisualSearch] imagecreatefromstring failed for: ' . substr($imagePathOrUrl, 0, 200));
            return null;
        }

        $img = $this->fixExifOrientation($img, $binary);
        $img = $this->flattenTransparency($img);

        $origW = imagesx($img);
        $origH = imagesy($img);
        if ($origW < 1 || $origH < 1) {
            imagedestroy($img);
            return null;
        }

        $aspect = round($origW / max(1, $origH), 4);
        $work = $this->resizeMaxSide($img, self::WORK_MAX_SIDE);
        if ($work !== $img) {
            imagedestroy($img);
        }

        $dhash = $this->computeDHash($work);
        $ahash = $this->computeAHash($work);
        $hist = $this->computeHsvHistogram($work, 8, 4, 4);
        $dominant = $this->computeDominantColors($work, 3);

        imagedestroy($work);

        if ($dhash === null || $ahash === null || empty($hist)) {
            return null;
        }

        return [
            'dhash' => $dhash,
            'ahash' => $ahash,
            'color_hist' => $hist,
            'dominant_colors' => $dominant,
            'aspect_ratio' => $aspect,
        ];
    }

    // =========================================================================
    //  INDEXING
    // =========================================================================

    /**
     * Index main + gallery + variant images for one product.
     * Safe to re-run; skips URLs already indexed unless $force.
     */
    public function indexProduct(int $productId, bool $force = false): bool
    {
        $stmt = $this->db->prepare("SELECT id, main_image FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            return false;
        }

        $imagesToIndex = [];

        if (!empty($product['main_image'])) {
            $path = trim($product['main_image']);
            $imagesToIndex[$path] = ['image_url' => $path, 'variant_id' => null];
        }

        try {
            $stmtGal = $this->db->prepare("
                SELECT id, image_url, image_path, variation_value_id
                FROM product_images
                WHERE product_id = ?
                ORDER BY is_primary DESC, sort_order ASC, id ASC
            ");
            $stmtGal->execute([$productId]);
            foreach ($stmtGal->fetchAll(PDO::FETCH_ASSOC) as $g) {
                $path = trim($g['image_url'] ?: ($g['image_path'] ?? ''));
                if ($path === '') {
                    continue;
                }
                if (!isset($imagesToIndex[$path])) {
                    $imagesToIndex[$path] = [
                        'image_url' => $path,
                        'variant_id' => !empty($g['variation_value_id']) ? (int) $g['variation_value_id'] : null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            error_log('[VisualSearch] gallery fetch failed: ' . $e->getMessage());
        }

        try {
            $stmtVar = $this->db->prepare("
                SELECT id, image_url FROM product_variants
                WHERE product_id = ? AND is_active = 1
                  AND image_url IS NOT NULL AND image_url <> ''
            ");
            $stmtVar->execute([$productId]);
            foreach ($stmtVar->fetchAll(PDO::FETCH_ASSOC) as $v) {
                $path = trim($v['image_url'] ?? '');
                if ($path === '') {
                    continue;
                }
                if (!isset($imagesToIndex[$path])) {
                    $imagesToIndex[$path] = [
                        'image_url' => $path,
                        'variant_id' => (int) $v['id'],
                    ];
                } elseif (empty($imagesToIndex[$path]['variant_id'])) {
                    $imagesToIndex[$path]['variant_id'] = (int) $v['id'];
                }
            }
        } catch (\Throwable $e) {
            error_log('[VisualSearch] variants fetch failed: ' . $e->getMessage());
        }

        // Color swatches often hold the variant product photo
        try {
            $stmtColor = $this->db->prepare("
                SELECT id, swatch_hex_or_image FROM product_colors
                WHERE product_id = ?
                  AND swatch_hex_or_image IS NOT NULL
                  AND swatch_hex_or_image <> ''
                  AND (swatch_hex_or_image LIKE 'http%' OR swatch_hex_or_image LIKE '%/%')
            ");
            $stmtColor->execute([$productId]);
            foreach ($stmtColor->fetchAll(PDO::FETCH_ASSOC) as $c) {
                $path = trim($c['swatch_hex_or_image'] ?? '');
                if ($path === '' || (strlen($path) <= 7 && $path[0] === '#')) {
                    continue;
                }
                if (!isset($imagesToIndex[$path])) {
                    $imagesToIndex[$path] = ['image_url' => $path, 'variant_id' => null];
                }
            }
        } catch (\Throwable $e) {
            // table may differ on older schemas
        }

        if (empty($imagesToIndex)) {
            return false;
        }

        $existing = [];
        if (!$force) {
            $stmtEx = $this->db->prepare("SELECT image_url FROM product_image_features WHERE product_id = ?");
            $stmtEx->execute([$productId]);
            foreach ($stmtEx->fetchAll(PDO::FETCH_COLUMN) as $u) {
                $existing[$u] = true;
            }
        } else {
            $this->db->prepare("DELETE FROM product_image_features WHERE product_id = ?")->execute([$productId]);
        }

        $stmtSave = $this->db->prepare("
            INSERT INTO product_image_features
                (product_id, variant_id, image_url, dhash, ahash, color_hist, dominant_colors, aspect_ratio, created_at)
            VALUES
                (:pid, :vid, :url, :dhash, :ahash, :hist, :dom, :aspect, NOW())
            ON DUPLICATE KEY UPDATE
                variant_id = VALUES(variant_id),
                dhash = VALUES(dhash),
                ahash = VALUES(ahash),
                color_hist = VALUES(color_hist),
                dominant_colors = VALUES(dominant_colors),
                aspect_ratio = VALUES(aspect_ratio),
                created_at = NOW()
        ");

        $indexed = 0;
        $keptUrls = [];
        foreach ($imagesToIndex as $imgData) {
            $url = $imgData['image_url'];
            $keptUrls[] = $url;
            if (!$force && isset($existing[$url])) {
                continue;
            }

            try {
                $features = $this->extractFeatures($url);
            } catch (\Throwable $e) {
                error_log('[VisualSearch] extractFeatures exception for product ' . $productId . ': ' . $e->getMessage());
                continue;
            }

            if (!$features) {
                continue;
            }

            $ok = $stmtSave->execute([
                'pid' => $productId,
                'vid' => $imgData['variant_id'],
                'url' => $url,
                'dhash' => $features['dhash'],
                'ahash' => $features['ahash'],
                'hist' => json_encode($features['color_hist']),
                'dom' => implode(',', $features['dominant_colors']),
                'aspect' => $features['aspect_ratio'],
            ]);

            if ($ok) {
                $indexed++;
            }
        }

        // Drop feature rows for images that were removed/replaced
        if (!empty($keptUrls)) {
            $placeholders = implode(',', array_fill(0, count($keptUrls), '?'));
            $del = $this->db->prepare("DELETE FROM product_image_features WHERE product_id = ? AND image_url NOT IN ($placeholders)");
            $del->execute(array_merge([$productId], $keptUrls));
        }

        return $indexed > 0 || (!$force && !empty(array_intersect(array_keys($existing), $keptUrls)));
    }

    /**
     * Remove feature rows for a specific image URL (when image deleted/replaced).
     */
    public function removeFeaturesByImageUrl(string $imageUrl, ?int $productId = null): void
    {
        $imageUrl = trim($imageUrl);
        if ($imageUrl === '') {
            return;
        }
        if ($productId) {
            $stmt = $this->db->prepare("DELETE FROM product_image_features WHERE product_id = ? AND image_url = ?");
            $stmt->execute([$productId, $imageUrl]);
        } else {
            $stmt = $this->db->prepare("DELETE FROM product_image_features WHERE image_url = ?");
            $stmt->execute([$imageUrl]);
        }
    }

    /**
     * Batch index active products. Safe to re-run; skips already-indexed images.
     *
     * @return array{total:int,indexed:int,failed:int,total_images_indexed:int,skipped:int}
     */
    public function indexAllProducts(bool $forceReindex = false, int $batchSize = 50, ?callable $progress = null): array
    {
        $stmt = $this->db->query("SELECT id, name FROM products WHERE status = 'active' ORDER BY id ASC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $success = 0;
        $fail = 0;
        $skipped = 0;
        $total = count($products);

        foreach ($products as $i => $p) {
            $pid = (int) $p['id'];

            $cStmt = $this->db->prepare("SELECT COUNT(*) FROM product_image_features WHERE product_id = ?");
            $cStmt->execute([$pid]);
            $alreadyIndexed = (int) $cStmt->fetchColumn() > 0;

            if ($this->indexProduct($pid, $forceReindex)) {
                if ($alreadyIndexed && !$forceReindex) {
                    $skipped++;
                } else {
                    $success++;
                }
            } else {
                $fail++;
            }

            if ($progress && (($i + 1) % $batchSize === 0 || $i + 1 === $total)) {
                $progress($i + 1, $total, $success, $fail, $skipped);
            }

            // Avoid runaway memory on long CLI runs
            if (($i + 1) % $batchSize === 0) {
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
        }

        $totalImages = (int) $this->db->query("SELECT COUNT(*) FROM product_image_features")->fetchColumn();

        return [
            'total' => $total,
            'indexed' => $success,
            'failed' => $fail,
            'skipped' => $skipped,
            'total_images_indexed' => $totalImages,
        ];
    }

    // =========================================================================
    //  SEARCH
    // =========================================================================

    /**
     * Search by uploaded image file path.
     *
     * @return array result payload for API / UI
     */
    public function searchByUploadedImage(string $uploadedTmpPath, ?string $categoryFilter = null, int $limit = 24): array
    {
        $limit = max(1, min(96, $limit));

        try {
            $queryFeatures = $this->extractFeatures($uploadedTmpPath);
        } catch (\Throwable $e) {
            error_log('[VisualSearch] Query extract exception: ' . $e->getMessage());
            $queryFeatures = null;
        }

        if (file_exists($uploadedTmpPath) && strpos($uploadedTmpPath, 'temp_visual_search') !== false) {
            @unlink($uploadedTmpPath);
        }

        if (!$queryFeatures) {
            return [
                'has_matches' => false,
                'is_fallback' => false,
                'image_parse_error' => true,
                'error_code' => 'unsupported_or_corrupt',
                'total' => 0,
                'headline' => 'Could not read this image. Try JPG, PNG, WEBP or GIF under 10MB.',
                'items' => [],
            ];
        }

        $indexed = $this->getAllIndexedFeatures($categoryFilter);
        if (empty($indexed)) {
            // Never reindex the whole catalog inside a user request — that hangs the
            // upload modal for minutes (batchSize only controls GC, not product count).
            // Admin/CLI backfill must populate product_image_features beforehand.
            error_log('[VisualSearch] empty product_image_features index — returning trending fallback (run backfill/reindex)');
            $fallback = $this->getFallbackTrendingProducts($limit, 'No close match found, showing popular products');
            $fallback['match_reason'] = 'empty_index';
            return $fallback;
        }

        $bestPerProduct = [];
        foreach ($indexed as $row) {
            $score = $this->scoreFeatures($queryFeatures, $row);
            $pid = (int) $row['product_id'];
            if (!isset($bestPerProduct[$pid]) || $score > $bestPerProduct[$pid]['raw_score']) {
                $bestPerProduct[$pid] = [
                    'row' => $row,
                    'raw_score' => $score,
                ];
            }
        }

        uasort($bestPerProduct, static fn($a, $b) => $b['raw_score'] <=> $a['raw_score']);
        $top = $bestPerProduct ? reset($bestPerProduct) : null;
        $topScore = $top ? (float) $top['raw_score'] : 0.0;
        $topRow = $top['row'] ?? null;
        // A near-duplicate of a catalog photo identifies the product type. Other
        // photos of that type (white-background anklets vs a lifestyle shot) often
        // score under the global threshold, so keep them as similar style.
        $expandFamily = $topRow && $topScore >= 0.90;
        $familySubcat = $expandFamily ? (int) ($topRow['subcategory_id'] ?? 0) : 0;
        $familyType = $expandFamily ? $this->productTypeToken((string) ($topRow['name'] ?? '')) : null;
        $subMatchesType = false;
        if ($expandFamily && $familySubcat > 0) {
            $this->loadCategoryHierarchy();
            $subName = strtolower((string) (self::$categoryHierarchyCache[$familySubcat]['name'] ?? ''));
            $subMatchesType = $familyType === null || ($subName !== '' && stripos($subName, $familyType) !== false);
        }

        $matches = [];
        foreach ($bestPerProduct as $pid => $best) {
            $score = $best['raw_score'];
            $row = $best['row'];
            $sameFamily = false;
            if ($expandFamily) {
                $rowSub = (int) ($row['subcategory_id'] ?? 0);
                if ($familySubcat > 0 && $rowSub === $familySubcat && $subMatchesType) {
                    $sameFamily = true;
                } elseif ($familyType && $this->productTypeToken((string) ($row['name'] ?? '')) === $familyType) {
                    $sameFamily = true;
                }
            }
            if (!$sameFamily && $score < self::MIN_MATCH_THRESHOLD) {
                continue;
            }
            if ($sameFamily && $score < 0.20 && (int) $pid !== (int) ($topRow['product_id'] ?? 0)) {
                continue;
            }
            $scorePercent = round($score * 100, 1);
            $displayImage = !empty($row['image_url']) ? $row['image_url'] : $row['main_image'];

            $item = [
                'id' => $pid,
                'name' => $row['name'],
                'slug' => $row['slug'],
                'price' => number_format((float) (($row['sale_price'] ?? 0) > 0 ? $row['sale_price'] : $row['price']), 2, '.', ''),
                'main_image' => $row['main_image'],
                'matched_image' => $displayImage,
                'matched_variant_id' => $row['variant_id'] ?? null,
                'image_url' => $this->formatImageUrl($displayImage),
                'product_url' => function_exists('url') ? url('product/' . $row['slug']) : '/product/' . $row['slug'],
                'category_name' => $row['category_name'] ?? 'Wholesale',
                'category_id' => !empty($row['category_id']) ? (int) $row['category_id'] : 0,
                'similarity_score' => $scorePercent,
                'raw_score' => $score,
                'score' => $score,
                'is_fallback' => false,
            ];

            if ($score >= self::STRONG_MATCH_THRESHOLD) {
                $item['match_badge'] = 'Best match';
                $item['match_type'] = 'strong';
            } else {
                $item['match_badge'] = 'Similar';
                $item['match_type'] = 'related';
            }

            $matches[] = $item;
        }

        usort($matches, fn($a, $b) => $b['raw_score'] <=> $a['raw_score']);

        if (!empty($matches)) {
            // Ensure only the top result shows Best match
            foreach ($matches as $i => &$m) {
                if ($i === 0 && $m['raw_score'] >= self::MIN_MATCH_THRESHOLD) {
                    $m['match_badge'] = $m['raw_score'] >= self::STRONG_MATCH_THRESHOLD ? 'Best match' : 'Top match';
                    $m['match_type'] = 'strong';
                } elseif ($i > 0 && ($m['match_badge'] ?? '') === 'Best match') {
                    $m['match_badge'] = 'Similar';
                    $m['match_type'] = 'related';
                }
            }
            unset($m);

            $items = array_slice($matches, 0, $limit);
            return [
                'has_matches' => true,
                'auto_redirect' => false,
                'is_fallback' => false,
                'image_parse_error' => false,
                'strong_count' => count(array_filter($items, fn($x) => ($x['raw_score'] ?? 0) >= self::STRONG_MATCH_THRESHOLD)),
                'related_count' => count($items),
                'total' => count($matches),
                'headline' => 'Visual matches',
                'items' => $items,
            ];
        }

        return $this->getFallbackTrendingProducts($limit, 'No close match found, showing popular products');
    }

    /**
     * Same-type products for a confident image-search hit (anklets with anklets).
     * Ranked by visual score when features exist. Used to fill a results page
     * that was stored before family expansion.
     *
     * @return array<int, array{product_id:int,variant_id:?int,score:float}>
     */
    public function similarStyleProductIds(int $productId, int $limit = 36): array
    {
        $limit = max(1, min(96, $limit));
        $stmt = $this->db->prepare("SELECT id, name, subcategory_id FROM products WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            return [];
        }

        $sub = (int) ($product['subcategory_id'] ?? 0);
        $type = $this->productTypeToken((string) ($product['name'] ?? ''));
        $subName = '';
        if ($sub > 0) {
            $cStmt = $this->db->prepare("SELECT name FROM categories WHERE id = ? LIMIT 1");
            $cStmt->execute([$sub]);
            $subName = strtolower((string) $cStmt->fetchColumn());
        }
        $subMatchesType = $type === null || ($subName !== '' && stripos($subName, $type) !== false);
        if ($sub <= 0 && $type === null) {
            return [];
        }

        if ($sub > 0 && $subMatchesType) {
            $sib = $this->db->prepare("
                SELECT id FROM products
                WHERE status = 'active' AND id <> ? AND subcategory_id = ?
                ORDER BY is_featured DESC, id DESC
                LIMIT 150
            ");
            $sib->execute([$productId, $sub]);
        } else {
            $sib = $this->db->prepare("
                SELECT id FROM products
                WHERE status = 'active' AND id <> ? AND name LIKE ?
                ORDER BY is_featured DESC, id DESC
                LIMIT 150
            ");
            $sib->execute([$productId, '%' . $type . '%']);
        }
        $ids = array_map('intval', $sib->fetchAll(PDO::FETCH_COLUMN) ?: []);
        if (empty($ids)) {
            return [];
        }

        $qStmt = $this->db->prepare("
            SELECT dhash, ahash, color_hist FROM product_image_features
            WHERE product_id = ? ORDER BY id ASC LIMIT 1
        ");
        $qStmt->execute([$productId]);
        $queryRow = $qStmt->fetch(PDO::FETCH_ASSOC);

        $scored = [];
        if ($queryRow) {
            $queryFeatures = [
                'dhash' => $queryRow['dhash'],
                'ahash' => $queryRow['ahash'],
                'color_hist' => json_decode($queryRow['color_hist'], true) ?: [],
            ];
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $fStmt = $this->db->prepare("
                SELECT product_id, variant_id, dhash, ahash, color_hist
                FROM product_image_features
                WHERE product_id IN ($placeholders)
            ");
            $fStmt->execute($ids);
            $best = [];
            foreach ($fStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $pid = (int) $row['product_id'];
                $score = $this->scoreFeatures($queryFeatures, $row);
                if (!isset($best[$pid]) || $score > $best[$pid]['score']) {
                    $best[$pid] = [
                        'score' => $score,
                        'variant_id' => !empty($row['variant_id']) ? (int) $row['variant_id'] : null,
                    ];
                }
            }
            foreach ($ids as $pid) {
                $scored[] = [
                    'product_id' => $pid,
                    'variant_id' => $best[$pid]['variant_id'] ?? null,
                    'score' => (float) ($best[$pid]['score'] ?? 0.35),
                ];
            }
            usort($scored, static fn($a, $b) => $b['score'] <=> $a['score']);
        } else {
            foreach ($ids as $pid) {
                $scored[] = ['product_id' => $pid, 'variant_id' => null, 'score' => 0.35];
            }
        }

        return array_slice($scored, 0, $limit);
    }

    /**
     * Distinctive product-type word from a catalog title.
     */
    public function productTypeToken(string $name): ?string
    {
        $name = strtolower($name);
        $types = ['anklet', 'bracelet', 'bangle', 'necklace', 'pendant', 'earring', 'earing', 'key ring', 'keyring', 'brooch', 'ring'];
        foreach ($types as $type) {
            if (preg_match('/\b' . preg_quote($type, '/') . 's?\b/', $name)) {
                return $type;
            }
        }
        return null;
    }

    /**
     * Similar products for an existing catalog product (PDP "similar" rail).
     */
    public function searchByProductId(int $productId, int $limit = 12, float $minThreshold = 0.55): array
    {
        $limit = max(1, min(24, $limit));
        $cacheKey = sprintf('visual_similar:p%d:l%d:t%.2f', $productId, $limit, $minThreshold);

        return \App\Infrastructure\Cache\CacheManager::getInstance()->remember($cacheKey, 1800, function () use ($productId, $limit, $minThreshold) {
            return $this->searchByProductIdUncached($productId, $limit, $minThreshold);
        });
    }

    /**
     * Uncached visual-similar lookup used by PDP.
     * Never indexes images on the request path (avoids remote HTTP downloads during page render).
     */
    private function searchByProductIdUncached(int $productId, int $limit, float $minThreshold): array
    {
        $empty = [
            'has_matches' => false,
            'total' => 0,
            'headline' => 'Similar Products',
            'items' => [],
        ];

        $stmt = $this->db->prepare("
            SELECT f.*, p.category_id
            FROM product_image_features f
            JOIN products p ON p.id = f.product_id
            WHERE f.product_id = ?
            ORDER BY f.id ASC
            LIMIT 1
        ");
        $stmt->execute([$productId]);
        $queryRow = $stmt->fetch(PDO::FETCH_ASSOC);

        // Do NOT call indexProduct() here — that downloads/processes images and can block for seconds.
        // Features should be backfilled offline (scripts/backfill_image_features.php).
        if (!$queryRow) {
            return $empty;
        }

        $queryFeatures = [
            'dhash' => $queryRow['dhash'],
            'ahash' => $queryRow['ahash'],
            'color_hist' => json_decode($queryRow['color_hist'], true) ?: [],
        ];

        $queryCatId = !empty($queryRow['category_id']) ? (int) $queryRow['category_id'] : 0;
        $indexed = $this->getIndexedFeaturesForSimilarity($queryCatId);
        $bestPerTarget = [];

        foreach ($indexed as $target) {
            $targetPid = (int) $target['product_id'];
            if ($targetPid === $productId) {
                continue;
            }

            $targetCatId = !empty($target['category_id']) ? (int) $target['category_id'] : 0;
            if ($queryCatId > 0 && $targetCatId > 0 && !$this->areCategoriesRelated($queryCatId, $targetCatId)) {
                continue;
            }

            $score = $this->scoreFeatures($queryFeatures, $target);
            if ($score < $minThreshold) {
                continue;
            }

            if (!isset($bestPerTarget[$targetPid]) || $score > $bestPerTarget[$targetPid]['raw_score']) {
                $bestPerTarget[$targetPid] = [
                    'target' => $target,
                    'raw_score' => $score,
                ];
            }
        }

        $results = [];
        foreach ($bestPerTarget as $targetPid => $best) {
            $target = $best['target'];
            $score = $best['raw_score'];
            $results[] = [
                'id' => $targetPid,
                'name' => $target['name'],
                'slug' => $target['slug'],
                'price' => (float) ($target['price'] ?? 0),
                'sale_price' => !empty($target['sale_price']) ? (float) $target['sale_price'] : null,
                'moq' => (int) ($target['moq'] ?? 1),
                'total_sold' => 0,
                'is_new' => !empty($target['is_new_arrival']),
                'is_featured' => !empty($target['is_featured']),
                'is_free_shipping' => true,
                'main_image' => $target['main_image'],
                'matched_image' => $target['image_url'],
                'image_url' => $this->formatImageUrl($target['main_image']),
                'product_url' => function_exists('url') ? url('product/' . $target['slug']) : '/product/' . $target['slug'],
                'category_name' => $target['category_name'] ?? 'Wholesale',
                'category_id' => !empty($target['category_id']) ? (int) $target['category_id'] : 0,
                'similarity_score' => round($score * 100, 1),
                'raw_score' => $score,
                'score' => $score,
            ];
        }

        usort($results, fn($a, $b) => $b['raw_score'] <=> $a['raw_score']);

        return [
            'has_matches' => !empty($results),
            'total' => count($results),
            'headline' => 'Similar Products',
            'items' => array_slice($results, 0, $limit),
        ];
    }

    /**
     * Load a category-scoped feature set for PDP similarity (avoids scanning the whole catalog).
     */
    private function getIndexedFeaturesForSimilarity(int $queryCatId): array
    {
        if ($queryCatId <= 0) {
            return $this->getAllIndexedFeatures(null);
        }

        $this->loadCategoryHierarchy();
        $relatedIds = [$queryCatId];
        $parent = (int) (self::$categoryHierarchyCache[$queryCatId]['parent_id'] ?? 0);
        if ($parent > 0) {
            $relatedIds[] = $parent;
        }
        foreach (self::$categoryHierarchyCache as $id => $info) {
            $p = (int) ($info['parent_id'] ?? 0);
            if ($id === $parent || $p === $queryCatId || ($parent > 0 && $p === $parent)) {
                $relatedIds[] = (int) $id;
            }
        }
        $relatedIds = array_values(array_unique(array_filter($relatedIds)));

        $cacheKey = 'visual_features:cats:' . implode(',', $relatedIds);
        return \App\Infrastructure\Cache\CacheManager::getInstance()->remember($cacheKey, 1800, function () use ($relatedIds) {
            $placeholders = implode(',', array_fill(0, count($relatedIds), '?'));
            $sql = "
                SELECT f.product_id, f.variant_id, f.image_url, f.dhash, f.ahash, f.color_hist, f.dominant_colors,
                       p.name, p.slug, p.price, p.sale_price, p.moq, p.is_featured, p.is_new_arrival,
                       p.main_image, p.category_id, p.subcategory_id, c.name as category_name
                FROM product_image_features f
                JOIN products p ON f.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active'
                  AND (p.category_id IN ({$placeholders}) OR p.subcategory_id IN ({$placeholders}))
            ";
            $params = array_merge($relatedIds, $relatedIds);
            try {
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $e) {
                error_log('[VisualSearch] getIndexedFeaturesForSimilarity: ' . $e->getMessage());
                return $this->getAllIndexedFeatures(null);
            }
        });
    }

    /**
     * Combined similarity score for two feature sets / DB rows.
     */
    public function scoreFeatures(array $query, array $targetRow): float
    {
        $qd = $query['dhash'] ?? '';
        $qa = $query['ahash'] ?? '';
        $qh = $query['color_hist'] ?? [];

        $td = $targetRow['dhash'] ?? '';
        $ta = $targetRow['ahash'] ?? '';
        $th = $targetRow['color_hist'] ?? [];
        if (is_string($th)) {
            $th = json_decode($th, true) ?: [];
        }

        $dDist = $this->hammingDistanceHex($qd, $td);
        $aDist = $this->hammingDistanceHex($qa, $ta);
        $dSim = 1.0 - ($dDist / 64.0);
        $aSim = 1.0 - ($aDist / 64.0);
        $hSim = $this->histogramIntersection($qh, $th);

        $score = 0.5 * max(0.0, $dSim) + 0.2 * max(0.0, $aSim) + 0.3 * max(0.0, $hSim);

        // Near-identical catalog photos (same SKU, watermarked CDN, slight JPEG recompress)
        // should always surface as confident matches even if color hist drifts.
        if ($dDist <= self::NEAR_DUP_DHASH_DIST) {
            $near = 0.93 + ((self::NEAR_DUP_DHASH_DIST - $dDist) / self::NEAR_DUP_DHASH_DIST) * 0.07;
            if ($aDist <= 12) {
                $near = max($near, 0.96);
            }
            $score = max($score, $near);
        }

        return max(0.0, min(1.0, $score));
    }

    public function hammingDistanceHex(string $hexA, string $hexB): int
    {
        $hexA = strtolower(preg_replace('/[^0-9a-f]/', '', $hexA) ?? '');
        $hexB = strtolower(preg_replace('/[^0-9a-f]/', '', $hexB) ?? '');
        $hexA = str_pad(substr($hexA, 0, 16), 16, '0');
        $hexB = str_pad(substr($hexB, 0, 16), 16, '0');

        $dist = 0;
        for ($i = 0; $i < 16; $i++) {
            $xa = hexdec($hexA[$i]);
            $xb = hexdec($hexB[$i]);
            $xor = $xa ^ $xb;
            // popcount nibble
            $dist += substr_count(decbin($xor), '1');
        }
        return $dist;
    }

    public function histogramIntersection(array $a, array $b): float
    {
        $n = max(count($a), count($b));
        if ($n === 0) {
            return 0.0;
        }
        $sum = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $sum += min((float) ($a[$i] ?? 0), (float) ($b[$i] ?? 0));
        }
        return max(0.0, min(1.0, $sum));
    }

    public function getFallbackTrendingProducts(int $limit = 12, string $headline = 'No close match found, showing popular products'): array
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
            $item['product_url'] = function_exists('url') ? url('product/' . $item['slug']) : '/product/' . $item['slug'];
            $item['similarity_score'] = 0.0;
            $item['raw_score'] = 0.0;
            $item['score'] = 0.0;
            $item['match_badge'] = 'Popular';
            $item['is_fallback'] = true;
            $item['price'] = number_format((float) (($item['sale_price'] ?? 0) > 0 ? $item['sale_price'] : $item['price']), 2, '.', '');
        }

        return [
            'has_matches' => false,
            'is_fallback' => true,
            'image_parse_error' => false,
            'total' => count($items),
            'headline' => $headline,
            'items' => $items,
        ];
    }

    public function getDebugAnalysis(string $imagePath): array
    {
        $start = microtime(true);
        $features = $this->extractFeatures($imagePath);
        $genMs = round((microtime(true) - $start) * 1000, 2);

        $topMatches = [];
        if ($features) {
            $indexed = $this->getAllIndexedFeatures(null);
            $best = [];
            foreach ($indexed as $row) {
                $score = $this->scoreFeatures($features, $row);
                $pid = (int) $row['product_id'];
                if (!isset($best[$pid]) || $score > $best[$pid]['score']) {
                    $best[$pid] = ['row' => $row, 'score' => $score];
                }
            }
            foreach ($best as $pid => $b) {
                $row = $b['row'];
                $raw = $b['score'];
                $topMatches[] = [
                    'product_id' => $pid,
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'main_image' => $row['main_image'],
                    'matched_image' => $row['image_url'],
                    'matched_type' => 'indexed',
                    'variant_id' => $row['variant_id'] ?? null,
                    'image_url' => $this->formatImageUrl($row['image_url']),
                    'category_name' => $row['category_name'] ?? 'Wholesale',
                    'raw_score' => number_format($raw, 4, '.', ''),
                    'score_float' => $raw,
                    'similarity_pct' => round($raw * 100, 2),
                    'match_category' => $raw >= self::STRONG_MATCH_THRESHOLD
                        ? 'Exact / Strong Match (>= 85%)'
                        : ($raw >= self::MIN_MATCH_THRESHOLD ? 'Similar Match' : 'Below Threshold'),
                ];
            }
            usort($topMatches, fn($a, $b) => $b['score_float'] <=> $a['score_float']);
        }

        $totalActive = (int) $this->db->query("SELECT COUNT(id) FROM products WHERE status = 'active'")->fetchColumn();
        $totalFeatureRows = (int) $this->db->query("SELECT COUNT(id) FROM product_image_features")->fetchColumn();
        $totalIndexedProducts = (int) $this->db->query("SELECT COUNT(DISTINCT product_id) FROM product_image_features")->fetchColumn();

        return [
            'query_image_path' => $imagePath,
            'resolved_path' => $imagePath,
            'embedding_generated' => !empty($features),
            'vector_dimensions' => $features ? count($features['color_hist']) : 0,
            'dhash' => $features['dhash'] ?? null,
            'ahash' => $features['ahash'] ?? null,
            'embedding_time_ms' => $genMs,
            'total_active_products' => $totalActive,
            'total_indexed_products' => $totalIndexedProducts,
            'total_feature_rows' => $totalFeatureRows,
            'top_5_matches' => array_slice($topMatches, 0, 5),
        ];
    }

    public function areCategoriesRelated(?int $catId1, ?int $catId2): bool
    {
        if (empty($catId1) || empty($catId2)) {
            return false;
        }
        $c1 = (int) $catId1;
        $c2 = (int) $catId2;
        if ($c1 === $c2) {
            return true;
        }

        $this->loadCategoryHierarchy();
        $p1 = self::$categoryHierarchyCache[$c1]['parent_id'] ?? 0;
        $p2 = self::$categoryHierarchyCache[$c2]['parent_id'] ?? 0;

        if ($p1 === $c2 || $p2 === $c1) {
            return true;
        }
        if ($p1 > 0 && $p1 === $p2) {
            $grandparent = self::$categoryHierarchyCache[$p1]['parent_id'] ?? 0;
            if ($grandparent > 0) {
                return true;
            }
        }
        return false;
    }

    public function getIndexStats(): array
    {
        return [
            'features' => (int) $this->db->query("SELECT COUNT(*) FROM product_image_features")->fetchColumn(),
            'products_indexed' => (int) $this->db->query("SELECT COUNT(DISTINCT product_id) FROM product_image_features")->fetchColumn(),
            'active_products' => (int) $this->db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
            'gd' => extension_loaded('gd'),
            'imagick' => extension_loaded('imagick'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
    }

    // =========================================================================
    //  PRIVATE — IMAGE LOADING / PROCESSING
    // =========================================================================

    private function loadImageBinary(string $pathOrUrl): ?string
    {
        $pathOrUrl = trim($pathOrUrl);
        if ($pathOrUrl === '') {
            return null;
        }

        if (preg_match('~^https?://~i', $pathOrUrl)) {
            return $this->downloadRemoteImage($pathOrUrl);
        }

        $resolved = $this->resolveLocalPath($pathOrUrl);
        if (!$resolved || !is_file($resolved)) {
            error_log('[VisualSearch] Local image not found: ' . $pathOrUrl);
            return null;
        }

        $size = filesize($resolved);
        if ($size === false || $size <= 0 || $size > self::DOWNLOAD_MAX_BYTES) {
            error_log('[VisualSearch] Local image size invalid: ' . $pathOrUrl . ' size=' . $size);
            return null;
        }

        $data = @file_get_contents($resolved);
        return ($data !== false && $data !== '') ? $data : null;
    }

    private function downloadRemoteImage(string $url): ?string
    {
        try {
            if (!function_exists('curl_init')) {
                $ctx = stream_context_create([
                    'http' => ['timeout' => 10, 'follow_location' => 1, 'header' => "User-Agent: ImportWalaVisualSearch/1.0\r\n"],
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                ]);
                $data = @file_get_contents($url, false, $ctx);
                if ($data === false || strlen($data) > self::DOWNLOAD_MAX_BYTES) {
                    return null;
                }
                return $data !== '' ? $data : null;
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ImportWalaVisualSearch/1.0');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            // XAMPP OpenSSL often lacks CA bundle — ignore peer errors if body still arrives
            if (defined('CURLOPT_SSL_OPTIONS') && defined('CURLSSLOPT_NATIVE_CA')) {
                @curl_setopt($ch, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA);
            }
            $data = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            // Accept body even when OpenSSL reports a local CA warning
            if (($data === false || $data === '') && $code > 0 && $code < 400) {
                // retry once with file_get_contents fallback
                $ctx = stream_context_create([
                    'http' => ['timeout' => 10, 'header' => "User-Agent: ImportWalaVisualSearch/1.0\r\n"],
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                ]);
                $data = @file_get_contents($url, false, $ctx);
            }

            if ($data === false || $data === '' || $code >= 400) {
                error_log('[VisualSearch] cURL download failed HTTP ' . $code . ' err=' . $err . ' url=' . substr($url, 0, 180));
                return null;
            }
            if (strlen($data) > self::DOWNLOAD_MAX_BYTES) {
                error_log('[VisualSearch] Remote image too large: ' . strlen($data));
                return null;
            }
            return $data;
        } catch (\Throwable $e) {
            error_log('[VisualSearch] download exception: ' . $e->getMessage());
            return null;
        }
    }

    private function resolveLocalPath(string $path): ?string
    {
        if (file_exists($path)) {
            return realpath($path) ?: $path;
        }

        $clean = ltrim((string) (parse_url($path, PHP_URL_PATH) ?: $path), '/');
        $clean = preg_replace('~^importwala/~i', '', $clean);

        $candidates = [
            $this->publicDir . '/' . ltrim($clean, '/'),
            $this->rootPath . '/' . ltrim($clean, '/'),
            $this->publicDir . '/uploads/products/' . basename($clean),
        ];
        foreach ($candidates as $c) {
            if (file_exists($c)) {
                return realpath($c) ?: $c;
            }
        }
        return null;
    }

    private function flattenTransparency($img)
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $flat = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($flat, 255, 255, 255);
        imagefill($flat, 0, 0, $white);
        imagealphablending($flat, true);
        imagecopy($flat, $img, 0, 0, 0, 0, $w, $h);
        imagedestroy($img);
        return $flat;
    }

    private function fixExifOrientation($img, string $binary)
    {
        if (!function_exists('exif_read_data')) {
            return $img;
        }
        $tmp = tempnam(sys_get_temp_dir(), 'vs_exif_');
        if (!$tmp) {
            return $img;
        }
        file_put_contents($tmp, $binary);
        $exif = @exif_read_data($tmp);
        @unlink($tmp);
        $orient = (int) ($exif['Orientation'] ?? 1);
        if ($orient <= 1) {
            return $img;
        }

        switch ($orient) {
            case 3:
                $img = imagerotate($img, 180, 0);
                break;
            case 6:
                $img = imagerotate($img, -90, 0);
                break;
            case 8:
                $img = imagerotate($img, 90, 0);
                break;
        }
        return $img;
    }

    private function resizeMaxSide($img, int $maxSide)
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $maxDim = max($w, $h);
        if ($maxDim <= $maxSide) {
            return $img;
        }
        $scale = $maxSide / $maxDim;
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        return $dst;
    }

    private function computeDHash($img): ?string
    {
        $small = imagecreatetruecolor(9, 8);
        imagecopyresampled($small, $img, 0, 0, 0, 0, 9, 8, imagesx($img), imagesy($img));
        $bits = '';
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $l = $this->grayAt($small, $x, $y);
                $r = $this->grayAt($small, $x + 1, $y);
                $bits .= ($l < $r) ? '1' : '0';
            }
        }
        imagedestroy($small);
        return $this->bitsToHex64($bits);
    }

    private function computeAHash($img): ?string
    {
        $small = imagecreatetruecolor(8, 8);
        imagecopyresampled($small, $img, 0, 0, 0, 0, 8, 8, imagesx($img), imagesy($img));
        $vals = [];
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $vals[] = $this->grayAt($small, $x, $y);
            }
        }
        imagedestroy($small);
        $avg = array_sum($vals) / 64.0;
        $bits = '';
        foreach ($vals as $v) {
            $bits .= ($v >= $avg) ? '1' : '0';
        }
        return $this->bitsToHex64($bits);
    }

    private function grayAt($img, int $x, int $y): float
    {
        $rgb = imagecolorat($img, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return 0.299 * $r + 0.587 * $g + 0.114 * $b;
    }

    private function bitsToHex64(string $bits): ?string
    {
        $bits = str_pad(substr($bits, 0, 64), 64, '0');
        $hex = '';
        for ($i = 0; $i < 64; $i += 4) {
            $hex .= dechex(bindec(substr($bits, $i, 4)));
        }
        return str_pad($hex, 16, '0', STR_PAD_LEFT);
    }

    /** Normalized HSV histogram, 8x4x4 = 128 bins. */
    private function computeHsvHistogram($img, int $hBins, int $sBins, int $vBins): array
    {
        $bins = $hBins * $sBins * $vBins;
        $hist = array_fill(0, $bins, 0.0);
        $w = imagesx($img);
        $h = imagesy($img);
        $stepX = max(1, (int) floor($w / 64));
        $stepY = max(1, (int) floor($h / 64));
        $count = 0;

        for ($y = 0; $y < $h; $y += $stepY) {
            for ($x = 0; $x < $w; $x += $stepX) {
                $rgb = imagecolorat($img, $x, $y);
                $r = (($rgb >> 16) & 0xFF) / 255.0;
                $g = (($rgb >> 8) & 0xFF) / 255.0;
                $b = ($rgb & 0xFF) / 255.0;
                [$hh, $ss, $vv] = $this->rgbToHsv($r, $g, $b);
                $hi = min($hBins - 1, (int) floor($hh * $hBins));
                $si = min($sBins - 1, (int) floor($ss * $sBins));
                $vi = min($vBins - 1, (int) floor($vv * $vBins));
                $idx = $hi * ($sBins * $vBins) + $si * $vBins + $vi;
                $hist[$idx] += 1.0;
                $count++;
            }
        }

        if ($count > 0) {
            for ($i = 0; $i < $bins; $i++) {
                $hist[$i] = round($hist[$i] / $count, 6);
            }
        }
        return $hist;
    }

    private function computeDominantColors($img, int $topN = 3): array
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $stepX = max(1, (int) floor($w / 32));
        $stepY = max(1, (int) floor($h / 32));
        $buckets = [];

        for ($y = 0; $y < $h; $y += $stepY) {
            for ($x = 0; $x < $w; $x += $stepX) {
                $rgb = imagecolorat($img, $x, $y);
                $r = (($rgb >> 16) & 0xFF) >> 4;
                $g = (($rgb >> 8) & 0xFF) >> 4;
                $b = ($rgb & 0xFF) >> 4;
                $key = ($r << 8) | ($g << 4) | $b;
                $buckets[$key] = ($buckets[$key] ?? 0) + 1;
            }
        }
        arsort($buckets);
        $colors = [];
        $i = 0;
        foreach ($buckets as $key => $_) {
            if ($i >= $topN) {
                break;
            }
            $r = (($key >> 8) & 0xF) * 17;
            $g = (($key >> 4) & 0xF) * 17;
            $b = ($key & 0xF) * 17;
            $colors[] = sprintf('#%02X%02X%02X', $r, $g, $b);
            $i++;
        }
        return $colors;
    }

    private function rgbToHsv(float $r, float $g, float $b): array
    {
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $d = $max - $min;
        $v = $max;
        $s = $max <= 0 ? 0.0 : $d / $max;
        if ($d <= 0) {
            $h = 0.0;
        } elseif ($max === $r) {
            $h = fmod((($g - $b) / $d), 6.0);
        } elseif ($max === $g) {
            $h = (($b - $r) / $d) + 2.0;
        } else {
            $h = (($r - $g) / $d) + 4.0;
        }
        $h /= 6.0;
        if ($h < 0) {
            $h += 1.0;
        }
        return [$h, $s, $v];
    }

    private function getAllIndexedFeatures(?string $categoryFilter = null): array
    {
        $where = ["p.status = 'active'"];
        $params = [];

        if (!empty($categoryFilter) && $categoryFilter !== 'all') {
            // Accept category id or name/slug fragment
            if (ctype_digit((string) $categoryFilter)) {
                $where[] = "(p.category_id = :catid OR p.subcategory_id = :catid OR c.parent_id = :catid)";
                $params['catid'] = (int) $categoryFilter;
            } else {
                $where[] = "(c.name LIKE :cat OR c.slug LIKE :cat OR p.name LIKE :cat OR p.tags LIKE :cat)";
                $params['cat'] = '%' . $categoryFilter . '%';
            }
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT f.product_id, f.variant_id, f.image_url, f.dhash, f.ahash, f.color_hist, f.dominant_colors,
                   p.name, p.slug, p.price, p.sale_price, p.moq, p.is_featured, p.is_new_arrival,
                   p.main_image, p.category_id, p.subcategory_id, c.name as category_name
            FROM product_image_features f
            JOIN products p ON f.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereSql}
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[VisualSearch] getAllIndexedFeatures: ' . $e->getMessage());
            return [];
        }
    }

    private function loadCategoryHierarchy(): void
    {
        if (!empty(self::$categoryHierarchyCache)) {
            return;
        }
        try {
            $rows = $this->db->query("SELECT id, name, parent_id FROM categories")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                self::$categoryHierarchyCache[(int) $row['id']] = [
                    'name' => $row['name'],
                    'parent_id' => $row['parent_id'] ? (int) $row['parent_id'] : 0,
                ];
            }
        } catch (\Throwable $e) {
            self::$categoryHierarchyCache = [];
        }
    }

    private function formatImageUrl(?string $imagePath): string
    {
        $path = !empty($imagePath) ? $imagePath : 'assets/images/placeholder.jpg';
        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }
        if (function_exists('asset')) {
            return asset($path);
        }
        return '/public/' . ltrim($path, '/');
    }

    private function ensureFeaturesTable(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;
        try {
            $this->db->query("SELECT 1 FROM product_image_features LIMIT 1");
        } catch (\Throwable $e) {
            try {
                $sqlFile = $this->rootPath . '/database/migrations/product_image_features.sql';
                if (is_file($sqlFile)) {
                    $this->db->exec(file_get_contents($sqlFile));
                }
            } catch (\Throwable $e2) {
                error_log('[VisualSearch] ensureFeaturesTable failed: ' . $e2->getMessage());
            }
        }
    }
}
