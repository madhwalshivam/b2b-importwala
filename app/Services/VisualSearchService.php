<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class VisualSearchService
{
    /**
     * Minimum similarity score percentage required to count as a match.
     * Items below this threshold are excluded entirely (not just ranked lower).
     */
    public const MIN_SIMILARITY_THRESHOLD = 55.0;

    private PDO $db;
    private string $publicDir;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->publicDir = rtrim(realpath(__DIR__ . '/../../public'), '/\\');
    }

    /**
     * Compute visual signature (dHash + color signature) for an image file path or URL.
     */
    public function computeSignature(string $imagePath): ?array
    {
        if (!function_exists('imagecreatefromstring')) {
            // Fallback if GD extension is not enabled in the current PHP environment
            $content = @file_get_contents($imagePath);
            $hash = md5($content ?: $imagePath);
            $dhash = substr($hash, 0, 16);
            $colorSig = array_fill(0, 48, 0.5);
            return [
                'dhash' => $dhash,
                'color_sig' => json_encode($colorSig),
            ];
        }

        $gdImg = $this->loadGdImage($imagePath);
        if (!$gdImg) {
            $hash = md5($imagePath);
            return [
                'dhash' => substr($hash, 0, 16),
                'color_sig' => json_encode(array_fill(0, 48, 0.5)),
            ];
        }

        $dhash = $this->computeDHash($gdImg);
        $colorSig = $this->computeColorSig($gdImg);

        @\imagedestroy($gdImg);

        return [
            'dhash' => $dhash,
            'color_sig' => json_encode($colorSig),
        ];
    }

    /**
     * Index a single product by ID.
     */
    public function indexProduct(int $productId): bool
    {
        $stmt = $this->db->prepare("SELECT id, main_image FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product || empty($product['main_image'])) {
            return false;
        }

        $sig = $this->computeSignature($product['main_image']);
        if (!$sig) {
            return false;
        }

        $stmtSave = $this->db->prepare("
            INSERT INTO product_visual_signatures (product_id, image_path, dhash, color_sig, updated_at)
            VALUES (:pid, :img, :dhash, :color_sig, NOW())
            ON DUPLICATE KEY UPDATE
                image_path = VALUES(image_path),
                dhash = VALUES(dhash),
                color_sig = VALUES(color_sig),
                updated_at = NOW()
        ");

        return $stmtSave->execute([
            'pid' => $productId,
            'img' => $product['main_image'],
            'dhash' => $sig['dhash'],
            'color_sig' => $sig['color_sig'],
        ]);
    }

    /**
     * Index all active products in database.
     */
    public function indexAllProducts(bool $forceReindex = false): array
    {
        $query = "SELECT id, name, main_image FROM products WHERE status = 'active'";
        if (!$forceReindex) {
            $query .= " AND id NOT IN (SELECT product_id FROM product_visual_signatures)";
        }

        $stmt = $this->db->query($query);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $successCount = 0;
        $failCount = 0;

        foreach ($products as $p) {
            if (empty($p['main_image'])) {
                $failCount++;
                continue;
            }
            if ($this->indexProduct((int)$p['id'])) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return [
            'total' => count($products),
            'indexed' => $successCount,
            'failed' => $failCount,
        ];
    }

    /**
     * Search products visually by uploaded image path.
     */
    public function searchByImage(string $uploadedImagePath, ?string $categoryFilter = null, int $limit = 12): array
    {
        $this->ensureCatalogIndexed();

        $querySig = $this->computeSignature($uploadedImagePath);
        if (!$querySig) {
            return [];
        }

        return $this->rankProductsBySignature($querySig['dhash'], json_decode($querySig['color_sig'], true) ?? [], $categoryFilter, $limit);
    }

    /**
     * Search products visually similar to an existing product ID.
     */
    public function searchByProductId(int $productId, int $limit = 12): array
    {
        $this->ensureCatalogIndexed();

        $stmt = $this->db->prepare("SELECT dhash, color_sig FROM product_visual_signatures WHERE product_id = ?");
        $stmt->execute([$productId]);
        $sig = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sig) {
            $this->indexProduct($productId);
            $stmt->execute([$productId]);
            $sig = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$sig) {
            return [];
        }

        $matches = $this->rankProductsBySignature($sig['dhash'], json_decode($sig['color_sig'], true) ?? [], null, $limit + 1);

        // Filter out current product
        return array_values(array_filter($matches, function ($item) use ($productId) {
            return (int)$item['id'] !== $productId;
        }));
    }

    /**
     * Search visual matches by preset category keyword (e.g. 'jewelry', 'watches', 'sunglasses', 'accessories').
     */
    public function searchByCategoryPreset(string $categoryKey, int $limit = 12): array
    {
        $categoryKey = strtolower(trim($categoryKey));
        $where = ["p.status = 'active'"];
        $params = [];

        if (!empty($categoryKey) && $categoryKey !== 'all') {
            $where[] = "(c.name LIKE :kw OR c.slug LIKE :kw OR p.name LIKE :kw OR p.tags LIKE :kw)";
            $params['kw'] = '%' . $categoryKey . '%';
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.main_image, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereSql}
            ORDER BY p.is_featured DESC, p.id DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            $item['image_url'] = $this->formatImageUrl($item['main_image']);
            $item['similarity_score'] = 90.0;
            $item['match_badge'] = 'Category Match';
        }

        return $items;
    }

    // =========================================================================
    //  PRIVATE IMPLEMENTATION & ALGORITHMS
    // =========================================================================

    /**
     * Rank products by dHash & Color similarity and enforce MIN_SIMILARITY_THRESHOLD.
     */
    private function rankProductsBySignature(string $qDHash, array $qColorSig, ?string $categoryFilter = null, int $limit = 12): array
    {
        $where = ["p.status = 'active'"];
        $params = [];

        if (!empty($categoryFilter) && $categoryFilter !== 'all') {
            $where[] = "(c.name LIKE :cat OR c.slug LIKE :cat OR p.name LIKE :cat OR p.tags LIKE :cat)";
            $params['cat'] = '%' . $categoryFilter . '%';
        }

        $whereSql = implode(' AND ', $where);
        $sql = "
            SELECT s.product_id, s.dhash, s.color_sig, p.name, p.slug, p.price, p.sale_price, p.main_image, c.name as category_name
            FROM product_visual_signatures s
            JOIN products p ON s.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereSql}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return [];
        }

        $ranked = [];
        foreach ($rows as $row) {
            $shapeSim = $this->calculateDHashSimilarity($qDHash, $row['dhash']);
            $rowColorSig = json_decode($row['color_sig'], true) ?? [];
            $colorSim = $this->calculateColorSimilarity($qColorSig, $rowColorSig);

            // Combined similarity: 55% shape + 45% color
            $score = ($shapeSim * 0.55) + ($colorSim * 0.45);
            $scorePercent = round($score * 100, 1);

            // Enforce minimum threshold filter!
            if ($scorePercent < self::MIN_SIMILARITY_THRESHOLD) {
                continue;
            }

            $ranked[] = [
                'id' => (int)$row['product_id'],
                'name' => $row['name'],
                'slug' => $row['slug'],
                'price' => number_format((float)($row['sale_price'] > 0 ? $row['sale_price'] : $row['price']), 2, '.', ''),
                'main_image' => $row['main_image'],
                'image_url' => $this->formatImageUrl($row['main_image']),
                'category_name' => $row['category_name'] ?? 'Wholesale',
                'similarity_score' => $scorePercent,
                'match_badge' => $scorePercent . '% Match',
            ];
        }

        usort($ranked, function ($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });

        return array_slice($ranked, 0, $limit);
    }

    /**
     * Compute 64-bit dHash (Difference Hash) as hex string.
     */
    private function computeDHash($gdImg): string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return str_pad(dechex(crc32('fallback')), 16, '0', STR_PAD_LEFT);
        }

        $w = 9;
        $h = 8;
        $resized = \imagecreatetruecolor($w, $h);
        \imagecopyresampled($resized, $gdImg, 0, 0, 0, 0, $w, $h, \imagesx($gdImg), \imagesy($gdImg));

        $gray = [];
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgb = \imagecolorat($resized, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $gray[$y][$x] = (int)(0.299 * $r + 0.587 * $g + 0.114 * $b);
            }
        }
        @\imagedestroy($resized);

        $bits = '';
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $bits .= ($gray[$y][$x] > $gray[$y][$x + 1]) ? '1' : '0';
            }
        }

        $hex = '';
        for ($i = 0; $i < 64; $i += 4) {
            $hex .= dechex(bindec(substr($bits, $i, 4)));
        }

        return str_pad($hex, 16, '0', STR_PAD_LEFT);
    }

    /**
     * Compute 4x4 Grid Color Signature (48 normalized RGB values).
     */
    private function computeColorSig($gdImg): array
    {
        if (!function_exists('imagecreatetruecolor')) {
            return array_fill(0, 48, 0.5);
        }

        $w = 4;
        $h = 4;
        $resized = \imagecreatetruecolor($w, $h);
        \imagecopyresampled($resized, $gdImg, 0, 0, 0, 0, $w, $h, \imagesx($gdImg), \imagesy($gdImg));

        $sig = [];
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgb = \imagecolorat($resized, $x, $y);
                $r = (($rgb >> 16) & 0xFF) / 255.0;
                $g = (($rgb >> 8) & 0xFF) / 255.0;
                $b = ($rgb & 0xFF) / 255.0;
                $sig[] = round($r, 4);
                $sig[] = round($g, 4);
                $sig[] = round($b, 4);
            }
        }
        @\imagedestroy($resized);

        return $sig;
    }

    /**
     * Calculate similarity between two 16-hex dHashes using Hamming distance.
     */
    private function calculateDHashSimilarity(string $hex1, string $hex2): float
    {
        if (strlen($hex1) !== 16 || strlen($hex2) !== 16) {
            return 0.5;
        }

        $bin1 = '';
        $bin2 = '';
        for ($i = 0; $i < 16; $i++) {
            $bin1 .= str_pad(base_convert($hex1[$i], 16, 2), 4, '0', STR_PAD_LEFT);
            $bin2 .= str_pad(base_convert($hex2[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }

        $dist = 0;
        for ($i = 0; $i < 64; $i++) {
            if ($bin1[$i] !== $bin2[$i]) {
                $dist++;
            }
        }

        return max(0.0, 1.0 - ($dist / 64.0));
    }

    /**
     * Calculate Euclidean color similarity between two color signature arrays.
     */
    private function calculateColorSimilarity(array $c1, array $c2): float
    {
        $count = min(count($c1), count($c2));
        if ($count === 0) {
            return 0.5;
        }

        $sumSq = 0.0;
        for ($i = 0; $i < $count; $i++) {
            $diff = (float)$c1[$i] - (float)$c2[$i];
            $sumSq += $diff * $diff;
        }

        $dist = sqrt($sumSq);
        $maxPossibleDist = sqrt($count * 1.0);

        return max(0.0, 1.0 - ($dist / $maxPossibleDist));
    }

    /**
     * Load GD image resource from file path or web URL.
     */
    private function loadGdImage(string $path)
    {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }

        $realPath = $path;

        if (!preg_match('~^https?://~i', $path) && !file_exists($path)) {
            $clean = ltrim(parse_url($path, PHP_URL_PATH), '/');
            $clean = preg_replace('~^importwala/~i', '', $clean);
            $candidate = $this->publicDir . '/' . ltrim($clean, '/');
            if (file_exists($candidate)) {
                $realPath = $candidate;
            }
        }

        $content = @file_get_contents($realPath);
        if (!$content) {
            return null;
        }

        return @\imagecreatefromstring($content) ?: null;
    }

    /**
     * Format image URL for JSON output.
     */
    private function formatImageUrl(?string $imagePath): string
    {
        if (file_exists(__DIR__ . '/../Helpers/Functions.php')) {
            require_once __DIR__ . '/../Helpers/Functions.php';
        }

        $path = !empty($imagePath) ? $imagePath : 'assets/images/placeholder.jpg';
        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        if (function_exists('asset')) {
            return asset($path);
        }

        return '/importwala/public/' . ltrim($path, '/');
    }

    /**
     * Ensure catalog is indexed if index table count is lower than active products.
     */
    private function ensureCatalogIndexed(): void
    {
        try {
            $totalP = (int)$this->db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
            $totalSig = (int)$this->db->query("SELECT COUNT(*) FROM product_visual_signatures")->fetchColumn();

            if ($totalP > 0 && $totalSig < ($totalP * 0.5)) {
                $this->indexAllProducts(false);
            }
        } catch (\Throwable $e) {
            // Silent fallback
        }
    }
}
