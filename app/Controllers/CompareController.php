<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class CompareController extends Controller {

    // Toggle product in Compare Session Array
    public function toggle(): void {
        header('Content-Type: application/json');

        $productId = (int)$this->request->input('product_id', 0);
        if ($productId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
            exit;
        }

        if (!isset($_SESSION['compare']) || !is_array($_SESSION['compare'])) {
            $_SESSION['compare'] = [];
        }

        if (in_array($productId, $_SESSION['compare'])) {
            // Remove
            $_SESSION['compare'] = array_values(array_diff($_SESSION['compare'], [$productId]));
            $status = 'removed';
            $msg = 'Removed from comparison list.';
        } else {
            // Check max 4 items limit
            if (count($_SESSION['compare']) >= 4) {
                echo json_encode([
                    'status' => 'limit_reached',
                    'count' => count($_SESSION['compare']),
                    'message' => 'Maximum 4 products can be compared at once.'
                ]);
                exit;
            }

            $_SESSION['compare'][] = $productId;
            $status = 'added';
            $msg = 'Added to comparison list.';
        }

        echo json_encode([
            'status' => $status,
            'count' => count($_SESSION['compare']),
            'message' => $msg,
            'compare_ids' => $_SESSION['compare']
        ]);
        exit;
    }

    // Clear entire comparison list
    public function clear(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['compare'] = [];
        
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
               || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'count' => 0,
                'message' => 'Comparison list cleared successfully'
            ]);
            exit;
        }

        $this->redirect(url('compare'));
    }

    // Comparison Page View
    public function index(): string {
        $productModel = new Product();

        // Support URL param ?ids=1,2,3 or fallback to session
        $queryIds = $this->request->input('ids');
        if (!empty($queryIds)) {
            $compareIds = array_filter(array_map('intval', explode(',', $queryIds)));
            $_SESSION['compare'] = array_values($compareIds);
        } else {
            $compareIds = $_SESSION['compare'] ?? [];
        }

        $products = $productModel->getComparisonData($compareIds);
        $allProducts = $productModel->getAllActiveProducts();

        // Compute dynamic union of specification keys across compared products
        $specKeysMap = [];
        $standardSpecs = [
            'material' => 'Material',
            'finish' => 'Finish',
            'weight_grams' => 'Weight',
            'warranty_months' => 'Warranty',
            'hsn_code' => 'HSN Code',
            'tax_percent' => 'GST Tax Rate'
        ];

        foreach ($products as $p) {
            foreach ($standardSpecs as $col => $label) {
                if (!empty($p[$col])) {
                    $specKeysMap[$label] = true;
                }
            }
            if (!empty($p['specifications']) && is_array($p['specifications'])) {
                foreach ($p['specifications'] as $spec) {
                    if (!empty($spec['spec_key'])) {
                        $specKeysMap[trim($spec['spec_key'])] = true;
                    }
                }
            }
        }
        $specKeys = array_keys($specKeysMap);

        return $this->render('storefront/compare', [
            'products' => $products,
            'allProducts' => $allProducts,
            'compareIds' => $compareIds,
            'specKeys' => $specKeys
        ]);
    }
}
