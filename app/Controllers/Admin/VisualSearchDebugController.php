<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Services\VisualSearchService;
use App\Core\Database;
use PDO;

class VisualSearchDebugController extends Controller
{
    private VisualSearchService $visualService;

    public function __construct()
    {
        parent::__construct();
        $this->visualService = new VisualSearchService();
    }

    public function index(): string
    {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $db = Database::getInstance();
        $products = $db->query("SELECT id, name, main_image FROM products WHERE status = 'active' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        $queryImagePath = null;
        $selectedProductId = null;
        $message = null;

        // 1. File Upload Handler
        $file = $_FILES['photo'] ?? $_FILES['image'] ?? null;
        if ($file && !empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            $tempDir = ROOT_PATH . '/storage/temp_visual_search';
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'jpg';
            $tempFile = $tempDir . '/debug_upload_' . time() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $tempFile)) {
                $queryImagePath = $tempFile;
            } else {
                $queryImagePath = $file['tmp_name'];
            }
        }
        // 2. URL Input Handler
        elseif (!empty($_REQUEST['image_url'])) {
            $queryImagePath = trim($_REQUEST['image_url']);
        }
        // 3. Product Dropdown Selector Handler
        elseif (!empty($_REQUEST['product_id'])) {
            $selectedProductId = (int)$_REQUEST['product_id'];
            $stmt = $db->prepare("SELECT main_image FROM products WHERE id = ?");
            $stmt->execute([$selectedProductId]);
            $queryImagePath = $stmt->fetchColumn() ?: null;
        }

        // Default to first active product if no query image selected
        if (!$queryImagePath && !empty($products)) {
            $selectedProductId = (int)$products[0]['id'];
            $queryImagePath = $products[0]['main_image'];
        }

        $analysis = $queryImagePath ? $this->visualService->getDebugAnalysis($queryImagePath) : null;

        return $this->render('admin/visual_search/debug', [
            'products'          => $products,
            'selectedProductId' => $selectedProductId,
            'queryImagePath'    => $queryImagePath,
            'analysis'          => $analysis,
            'message'           => $message,
            'user'              => Auth::user()
        ]);
    }

    public function reindex(): void
    {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
        }

        $stats = $this->visualService->indexAllProducts(true);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $this->jsonResponse([
                'success' => true,
                'message' => "Reindex complete! Indexed {$stats['total_images_indexed']} images across {$stats['indexed']} of {$stats['total']} products.",
                'stats'   => $stats
            ]);
            return;
        }

        $this->redirect(url('admin/visual-search/debug?reindexed=1'));
    }
}
