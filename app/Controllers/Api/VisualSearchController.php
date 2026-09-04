<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\VisualSearchService;

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
     * Handles image upload, feature vector generation, Cosine Similarity matching, and result formatting.
     */
    public function search(): void
    {
        $category  = trim($_REQUEST['category'] ?? $_REQUEST['q'] ?? $_REQUEST['preset'] ?? '');
        $productId = isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0;
        $limit     = max(1, min(24, (int)($_REQUEST['limit'] ?? 12)));

        // 1. Check if an image file was uploaded
        $file = $_FILES['photo'] ?? $_FILES['image'] ?? $_FILES['file'] ?? null;

        if ($file && !empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            // Validate file size (max 10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                $this->jsonResponse(['success' => false, 'message' => 'Image file too large (max 10MB).'], 400);
                return;
            }

            // Validate file extension / mime type
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid image format. Supported: JPG, PNG, WEBP.'], 400);
                return;
            }

            // Save temporarily in storage/temp_visual_search/
            $tempDir = ROOT_PATH . '/storage/temp_visual_search';
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }

            $tempFile = $tempDir . '/vs_query_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $tempFile)) {
                $tempFile = $file['tmp_name'];
            }

            $result = $this->visualService->searchByUploadedImage($tempFile, $category ?: null, $limit);

            $this->jsonResponse([
                'success'           => true,
                'matched_by'        => 'uploaded_image',
                'query_category'    => $category,
                'has_matches'       => $result['has_matches'] ?? false,
                'auto_redirect'     => $result['auto_redirect'] ?? false,
                'redirect_url'      => $result['redirect_url'] ?? null,
                'top_match'         => $result['top_match'] ?? null,
                'is_fallback'       => $result['is_fallback'] ?? false,
                'image_parse_error' => $result['image_parse_error'] ?? false,
                'headline'          => $result['headline'] ?? 'Visual Search Results',
                'total'             => $result['total'] ?? 0,
                'items'             => $result['items'] ?? [],
            ]);
            return;
        }

        // 2. Check if searching by existing product_id
        if ($productId > 0) {
            $result = $this->visualService->searchByProductId($productId, $limit);

            $this->jsonResponse([
                'success'        => true,
                'matched_by'     => 'product_id',
                'product_id'     => $productId,
                'has_matches'    => $result['has_matches'] ?? false,
                'headline'       => $result['headline'] ?? 'Visually Similar Products',
                'total'          => $result['total'] ?? 0,
                'items'          => $result['items'] ?? [],
            ]);
            return;
        }

        // 3. Fallback Trending Products
        $result = $this->visualService->getFallbackTrendingProducts($limit, 'Trending Wholesale Catalog');

        $this->jsonResponse([
            'success'        => true,
            'matched_by'     => 'trending_fallback',
            'query_category' => $category ?: 'all',
            'has_matches'    => false,
            'is_fallback'    => true,
            'headline'       => $result['headline'],
            'total'          => $result['total'],
            'items'          => $result['items'],
        ]);
    }

    /**
     * GET /api/visual-search/reindex
     * Administrative trigger to index catalog product embeddings.
     */
    public function reindex(): void
    {
        $force = !empty($_GET['force']);
        $stats = $this->visualService->indexAllProducts($force);

        $this->jsonResponse([
            'success' => true,
            'message' => "Visual embedding index complete. Indexed {$stats['total_images_indexed']} images across {$stats['indexed']} of {$stats['total']} products.",
            'stats'   => $stats,
        ]);
    }
}
