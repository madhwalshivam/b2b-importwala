<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Services\ImageSearchStoreService;
use App\Services\VisualSearchService;
use PDO;

class VisualSearchController extends BaseController
{
    private VisualSearchService $visualService;

    public function __construct()
    {
        parent::__construct();
        $this->visualService = new VisualSearchService();
    }

    /**
     * POST / GET /api/visual-search
     */
    public function search(): void
    {
        $category = trim($_REQUEST['category'] ?? $_REQUEST['q'] ?? $_REQUEST['preset'] ?? '');
        $productId = isset($_REQUEST['product_id']) ? (int) $_REQUEST['product_id'] : 0;
        $limit = max(1, min(96, (int) ($_REQUEST['limit'] ?? 96)));

        $file = $_FILES['photo'] ?? $_FILES['image'] ?? $_FILES['file'] ?? null;

        // Diagnostic logging (safe to keep — helps Hostinger debugging)
        $gdLoaded = extension_loaded('gd') ? 'yes' : 'no';
        $imagickLoaded = extension_loaded('imagick') ? 'yes' : 'no';
        if ($file) {
            error_log(sprintf(
                '[VisualSearch] upload received=yes error=%s size=%s mime=%s name=%s gd=%s imagick=%s upload_max=%s post_max=%s',
                $file['error'] ?? 'n/a',
                $file['size'] ?? 'n/a',
                $file['type'] ?? 'n/a',
                $file['name'] ?? 'n/a',
                $gdLoaded,
                $imagickLoaded,
                ini_get('upload_max_filesize'),
                ini_get('post_max_size')
            ));
        } elseif ($productId <= 0) {
            error_log(sprintf(
                '[VisualSearch] upload received=no FILES_keys=%s gd=%s imagick=%s',
                implode(',', array_keys($_FILES ?: [])),
                $gdLoaded,
                $imagickLoaded
            ));
        }

        if ($file) {
            $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

            if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
                $this->jsonResponse([
                    'success' => false,
                    'error_code' => 'file_too_large',
                    'message' => 'Image file too large. Please use a photo under 10MB (or let the browser resize it).',
                ], 400);
                return;
            }

            if ($uploadError !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error_code' => 'upload_failed',
                    'message' => 'Upload failed. Please try again with a JPG, PNG, WEBP or GIF image.',
                ], 400);
                return;
            }

            if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
                $this->jsonResponse([
                    'success' => false,
                    'error_code' => 'file_too_large',
                    'message' => 'Image file too large (max 10MB).',
                ], 400);
                return;
            }

            $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $finfoMime = function_exists('mime_content_type') ? @mime_content_type($file['tmp_name']) : ($file['type'] ?? '');
            $mimeOk = preg_match('~^image/(jpeg|png|webp|gif)~i', (string) $finfoMime);

            if ($ext && !in_array($ext, $allowed, true) && !$mimeOk) {
                $this->jsonResponse([
                    'success' => false,
                    'error_code' => 'unsupported_format',
                    'message' => 'Unsupported image format. Use JPG, PNG, WEBP or GIF.',
                ], 400);
                return;
            }

            if (!$ext || !in_array($ext, $allowed, true)) {
                $ext = 'jpg';
            }

            if (!extension_loaded('gd')) {
                error_log('[VisualSearch] GD extension not loaded — cannot parse images');
                $this->jsonResponse([
                    'success' => false,
                    'error_code' => 'gd_missing',
                    'message' => 'Image processing is not available on this server (PHP GD missing). Enable extension=gd in php.ini.',
                ], 500);
                return;
            }

            $tempDir = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 3)) . '/storage/temp_visual_search';
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }

            $tempFile = $tempDir . '/vs_query_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            if (!@move_uploaded_file($file['tmp_name'], $tempFile)) {
                $tempFile = $file['tmp_name'];
            }

            // Persistable copy BEFORE search (search deletes temp_visual_search files)
            $persistSource = $tempDir . '/vs_persist_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            @copy($tempFile, $persistSource);

            try {
                $result = $this->visualService->searchByUploadedImage($tempFile, $category ?: null, $limit);
            } catch (\Throwable $e) {
                @unlink($persistSource);
                error_log('[VisualSearch] search exception: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
                $this->jsonResponse([
                    'success' => false,
                    'error_code' => 'search_failed',
                    'message' => 'Image search failed unexpectedly. Please try another photo.',
                ], 500);
                return;
            }

            if (!empty($result['image_parse_error'])) {
                @unlink($persistSource);
                error_log('[VisualSearch] parse branch: features null for uploaded file ext=' . $ext . ' mime=' . $finfoMime);
                $this->jsonResponse([
                    'success' => false,
                    'matched_by' => 'uploaded_image',
                    'image_parse_error' => true,
                    'error_code' => $result['error_code'] ?? 'unsupported_or_corrupt',
                    'message' => $result['headline'] ?? 'Could not read this image. Try JPG, PNG, WEBP or GIF under 10MB.',
                    'items' => [],
                ], 422);
                return;
            }

            // Persist results + image → dedicated results page (never auto-open a product)
            $post = $this->persistSearchForResultsPage($result, $persistSource);
            @unlink($persistSource);

            $this->jsonResponse([
                'success' => true,
                'matched_by' => 'uploaded_image',
                'query_category' => $category,
                'has_matches' => $result['has_matches'] ?? false,
                'auto_redirect' => !empty($post['results_url']),
                'redirect_url' => $post['results_url'],
                'results_url' => $post['results_url'],
                'sid' => $post['sid'],
                'has_confident_match' => $post['has_confident_match'],
                'is_fallback' => $result['is_fallback'] ?? false,
                'image_parse_error' => false,
                'error_code' => null,
                'message' => $result['headline'] ?? null,
                'headline' => $result['headline'] ?? 'Visual Search Results',
                'total' => $result['total'] ?? count($result['items'] ?? []),
                'items' => [], // results render on /image-search page
            ]);
            return;
        }

        if ($productId > 0) {
            $result = $this->visualService->searchByProductId($productId, $limit);
            $this->jsonResponse([
                'success' => true,
                'matched_by' => 'product_id',
                'product_id' => $productId,
                'has_matches' => $result['has_matches'] ?? false,
                'headline' => $result['headline'] ?? 'Visually Similar Products',
                'total' => $result['total'] ?? 0,
                'items' => $result['items'] ?? [],
            ]);
            return;
        }

        if ($category !== '') {
            $items = $this->categoryBrowse($category, $limit);
            $this->jsonResponse([
                'success' => true,
                'matched_by' => 'category',
                'query_category' => $category,
                'has_matches' => !empty($items),
                'is_fallback' => false,
                'headline' => 'Products in this category',
                'total' => count($items),
                'items' => $items,
            ]);
            return;
        }

        $result = $this->visualService->getFallbackTrendingProducts($limit, 'Popular wholesale products');
        $this->jsonResponse([
            'success' => true,
            'matched_by' => 'trending_fallback',
            'query_category' => 'all',
            'has_matches' => false,
            'is_fallback' => true,
            'headline' => $result['headline'],
            'total' => $result['total'],
            'items' => $result['items'],
        ]);
    }

    /**
     * GET /api/visual-search/reindex
     */
    public function reindex(): void
    {
        $force = !empty($_GET['force']);
        $batch = max(10, min(100, (int) ($_GET['batch'] ?? 50)));
        $stats = $this->visualService->indexAllProducts($force, $batch);

        $this->jsonResponse([
            'success' => true,
            'message' => "Image search index complete. Indexed {$stats['total_images_indexed']} images across {$stats['indexed']} of {$stats['total']} products.",
            'stats' => $stats,
            'diagnostics' => $this->visualService->getIndexStats(),
        ]);
    }

    /**
     * Persist ranked results + query image for /image-search?sid=TOKEN.
     * Does not alter scores; only reads them. Never redirects to a product page.
     *
     * @return array{sid:?string,results_url:?string,has_confident_match:bool}
     */
    private function persistSearchForResultsPage(array $result, string $persistSourcePath): array
    {
        $empty = [
            'sid' => null,
            'results_url' => null,
            'has_confident_match' => false,
        ];

        $items = $result['items'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        $activeItems = $this->filterActiveSearchItems($items);
        if (empty($activeItems) && empty($result['is_fallback'])) {
            // Still open page with empty/closest if we somehow have nothing — use raw items
            $activeItems = $items;
        }

        $topScore = !empty($activeItems)
            ? (float) ($activeItems[0]['raw_score'] ?? $activeItems[0]['score'] ?? 0)
            : 0.0;
        $secondScore = isset($activeItems[1])
            ? (float) ($activeItems[1]['raw_score'] ?? $activeItems[1]['score'] ?? 0)
            : 0.0;
        // Confident = strong top hit. Near-duplicates (same catalog photo) usually score ≥0.93
        // with a wide gap; relax gap when the top score itself is extremely high.
        $gapOk = $secondScore <= 0
            || ($topScore - $secondScore) >= VisualSearchService::EXACT_SCORE_GAP
            || $topScore >= 0.95;
        $hasConfident = empty($result['is_fallback'])
            && $topScore >= VisualSearchService::EXACT_THRESHOLD
            && $gapOk;

        if (!is_file($persistSourcePath)) {
            error_log('[VisualSearch] persist source missing');
            return $empty;
        }

        try {
            $sid = (new ImageSearchStoreService())->store($persistSourcePath, $activeItems, $hasConfident);
        } catch (\Throwable $e) {
            error_log('[VisualSearch] persist failed: ' . $e->getMessage());
            return $empty;
        }

        $resultsUrl = function_exists('url')
            ? url('image-search?sid=' . urlencode($sid))
            : '/image-search?sid=' . urlencode($sid);

        return [
            'sid' => $sid,
            'results_url' => $resultsUrl,
            'has_confident_match' => $hasConfident,
        ];
    }

    /**
     * Keep only products that are still active in the DB (in original rank order).
     */
    private function filterActiveSearchItems(array $items): array
    {
        $ids = [];
        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));
        if (empty($ids)) {
            return [];
        }

        try {
            $db = Database::getInstance();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("SELECT id FROM products WHERE id IN ($placeholders) AND status = 'active'");
            $stmt->execute($ids);
            $activeIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
            $activeSet = array_flip($activeIds);
        } catch (\Throwable $e) {
            return $items;
        }

        $out = [];
        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            if ($id > 0 && isset($activeSet[$id])) {
                $out[] = $item;
            }
        }
        return $out;
    }

    private function categoryBrowse(string $category, int $limit): array
    {
        $db = \App\Core\Database::getInstance();
        $where = ["p.status = 'active'"];
        $params = [];
        if (ctype_digit($category)) {
            $where[] = '(p.category_id = :cat OR p.subcategory_id = :cat)';
            $params['cat'] = (int) $category;
        } else {
            $where[] = '(c.name LIKE :cat OR c.slug LIKE :cat)';
            $params['cat'] = '%' . $category . '%';
        }
        $sql = 'SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.main_image, c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY p.is_featured DESC, p.views_count DESC, p.id DESC
                LIMIT ' . (int) $limit;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$item) {
            $path = $item['main_image'] ?? '';
            $item['image_url'] = preg_match('~^https?://~i', $path)
                ? $path
                : (function_exists('asset') ? asset($path ?: 'assets/images/placeholder.jpg') : $path);
            $item['product_url'] = function_exists('url') ? url('product/' . $item['slug']) : '/product/' . $item['slug'];
            $item['price'] = number_format((float) (($item['sale_price'] ?? 0) > 0 ? $item['sale_price'] : $item['price']), 2, '.', '');
            $item['match_badge'] = 'Category';
            $item['similarity_score'] = 0;
            $item['raw_score'] = 0;
            $item['is_fallback'] = false;
        }
        return $rows;
    }
}
