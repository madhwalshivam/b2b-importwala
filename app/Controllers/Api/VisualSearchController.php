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
     * End-to-end Visual Match search endpoint.
     */
    public function search(): void
    {
        $category  = trim($_REQUEST['category'] ?? $_REQUEST['q'] ?? $_REQUEST['preset'] ?? '');
        $productId = isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0;
        $limit     = max(1, min(24, (int)($_REQUEST['limit'] ?? 12)));

        // 1. Check if an image file was uploaded
        $file = $_FILES['photo'] ?? $_FILES['image'] ?? $_FILES['file'] ?? null;

        if ($file && !empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            // Validate file size & extension
            if ($file['size'] > 10 * 1024 * 1024) {
                $this->jsonResponse(['success' => false, 'message' => 'Image file too large (max 10MB).'], 400);
                return;
            }

            $matches = $this->visualService->searchByImage($file['tmp_name'], $category ?: null, $limit);

            $this->jsonResponse([
                'success'        => true,
                'matched_by'     => 'uploaded_image',
                'query_category' => $category,
                'total'          => count($matches),
                'items'          => $matches,
            ]);
            return;
        }

        // 2. Check if searching by product_id
        if ($productId > 0) {
            $matches = $this->visualService->searchByProductId($productId, $limit);

            $this->jsonResponse([
                'success'        => true,
                'matched_by'     => 'product_id',
                'product_id'     => $productId,
                'total'          => count($matches),
                'items'          => $matches,
            ]);
            return;
        }

        // 3. Preset Category / Visual match fallback
        $matches = $this->visualService->searchByCategoryPreset($category ?: 'all', $limit);

        $this->jsonResponse([
            'success'        => true,
            'matched_by'     => 'category_preset',
            'query_category' => $category ?: 'all',
            'total'          => count($matches),
            'items'          => $matches,
        ]);
    }

    /**
     * GET /api/visual-search/reindex
     * Administrative / CLI trigger to index catalog.
     */
    public function reindex(): void
    {
        $force = !empty($_GET['force']);
        $stats = $this->visualService->indexAllProducts($force);

        $this->jsonResponse([
            'success' => true,
            'message' => "Visual index complete. Indexed {$stats['indexed']} of {$stats['total']} products.",
            'stats'   => $stats,
        ]);
    }
}
