<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Inquiry;
use App\Repositories\Eloquent\ProductRepository;

class InquiryApiController extends BaseController
{
    private ProductRepository $productRepo;
    private Inquiry $inquiryModel;

    public function __construct()
    {
        $this->productRepo = new ProductRepository();
        $this->inquiryModel = new Inquiry();
    }

    private function getSessionKey(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['inquiry_list'])) {
            $_SESSION['inquiry_list'] = [];
        }
        return 'inquiry_list';
    }

    /**
     * GET /api/inquiry — Fetch active inquiry list items & totals
     */
    public function getInquiry(): void
    {
        header('Content-Type: application/json');
        $this->getSessionKey();

        $items = [];
        $totalProducts = 0;
        $totalQuantity = 0;

        foreach ($_SESSION['inquiry_list'] as $itemKey => $itemData) {
            $product = $this->productRepo->getProductWithDetails($itemData['product_id']);
            if (!$product) {
                unset($_SESSION['inquiry_list'][$itemKey]);
                continue;
            }

            $variation = null;
            if (!empty($product['variations']) && !empty($itemData['variation_id'])) {
                foreach ($product['variations'] as $v) {
                    if ($v['id'] == $itemData['variation_id']) {
                        $variation = $v;
                        break;
                    }
                }
            }

            $qty = max(1, (int)($itemData['quantity'] ?? 1));
            $mainImg = $product['main_image'] ?? asset('assets/images/placeholder.jpg');

            $items[] = [
                'item_key' => $itemKey,
                'product_id' => $product['id'],
                'variation_id' => $variation['id'] ?? null,
                'product_name' => $product['title'] ?? $product['name'] ?? 'Wholesale Product',
                'sku' => $variation['sku'] ?? $product['sku'] ?? 'SKU-' . $product['id'],
                'variation_name' => $variation['color_name'] ?? $variation['name'] ?? null,
                'image' => $mainImg,
                'price' => (float)($product['sale_price'] ?: ($product['base_price'] ?: ($product['price'] ?? 0))),
                'moq' => (int)($product['moq'] ?? 1),
                'quantity' => $qty,
                'slug' => $product['slug'] ?? $product['id'],
            ];

            $totalProducts++;
            $totalQuantity += $qty;
        }

        echo json_encode([
            'success' => true,
            'items' => $items,
            'total_products' => $totalProducts,
            'total_quantity' => $totalQuantity
        ]);
    }

    /**
     * POST /api/inquiry/add — Add product to inquiry list
     */
    public function addItem(): void
    {
        header('Content-Type: application/json');
        $this->getSessionKey();

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $variationId = (int)($input['variation_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 0);

        if (!$productId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid product parameter.']);
            return;
        }

        $product = $this->productRepo->getProductWithDetails($productId);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            return;
        }

        $moq = (int)($product['moq'] ?? 1);
        if ($quantity > 0 && $quantity < $moq) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Minimum order quantity for this product is {$moq} units."
            ]);
            return;
        }
        if ($quantity <= 0) {
            $quantity = $moq;
        }

        $itemKey = "{$productId}_{$variationId}";

        if (isset($_SESSION['inquiry_list'][$itemKey])) {
            $_SESSION['inquiry_list'][$itemKey]['quantity'] += $quantity;
        } else {
            $_SESSION['inquiry_list'][$itemKey] = [
                'product_id' => $productId,
                'variation_id' => $variationId,
                'quantity' => $quantity,
                'added_at' => time(),
            ];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Product added to Inquiry List successfully.',
            'total_products' => count($_SESSION['inquiry_list']),
        ]);
    }

    /**
     * POST /api/inquiry/toggle — Toggle (add/remove) product in inquiry list
     */
    public function toggleItem(): void
    {
        header('Content-Type: application/json');
        $this->getSessionKey();

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $variationId = (int)($input['variation_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 0);

        if (!$productId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid product parameter.']);
            return;
        }

        $foundKey = null;
        foreach ($_SESSION['inquiry_list'] as $key => $item) {
            if ((int)($item['product_id'] ?? 0) === $productId) {
                $foundKey = $key;
                break;
            }
        }

        if ($foundKey !== null) {
            unset($_SESSION['inquiry_list'][$foundKey]);
            echo json_encode([
                'success' => true,
                'status' => 'removed',
                'message' => 'Removed from Inquiry List.',
                'total_products' => count($_SESSION['inquiry_list']),
            ]);
            return;
        }

        $product = $this->productRepo->getProductWithDetails($productId);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            return;
        }

        $moq = (int)($product['moq'] ?? 1);
        if ($quantity > 0 && $quantity < $moq) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Minimum order quantity for this product is {$moq} units."
            ]);
            return;
        }
        if ($quantity <= 0) {
            $quantity = $moq;
        }

        $itemKey = "{$productId}_{$variationId}";
        $_SESSION['inquiry_list'][$itemKey] = [
            'product_id' => $productId,
            'variation_id' => $variationId,
            'quantity' => $quantity,
            'added_at' => time(),
        ];

        echo json_encode([
            'success' => true,
            'status' => 'added',
            'message' => 'Added to Inquiry List!',
            'total_products' => count($_SESSION['inquiry_list']),
        ]);
    }

    /**
     * POST /api/inquiry/update — Update quantity of an inquiry item
     */
    public function updateItem(): void
    {
        header('Content-Type: application/json');
        $this->getSessionKey();

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $itemKey = trim($input['item_key'] ?? '');
        $quantity = (int)($input['quantity'] ?? 0);

        if (empty($itemKey) || !isset($_SESSION['inquiry_list'][$itemKey])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Inquiry item not found.']);
            return;
        }

        if ($quantity <= 0) {
            unset($_SESSION['inquiry_list'][$itemKey]);
        } else {
            $productId = (int)($_SESSION['inquiry_list'][$itemKey]['product_id'] ?? 0);
            $product = $this->productRepo->getProductWithDetails($productId);
            $moq = (int)($product['moq'] ?? 1);

            if ($quantity < $moq) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Minimum order quantity for this product is {$moq} units."
                ]);
                return;
            }

            $_SESSION['inquiry_list'][$itemKey]['quantity'] = $quantity;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Quantity updated successfully.',
        ]);
    }

    /**
     * POST /api/inquiry/remove — Remove an item from inquiry list
     */
    public function removeItem(): void
    {
        header('Content-Type: application/json');
        $this->getSessionKey();

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $itemKey = trim($input['item_key'] ?? '');

        if (!empty($itemKey) && isset($_SESSION['inquiry_list'][$itemKey])) {
            unset($_SESSION['inquiry_list'][$itemKey]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Item removed from inquiry list.',
        ]);
    }

    /**
     * POST /api/inquiry/submit — Save complete multi-product inquiry in DB
     */
    public function submit(): void
    {
        header('Content-Type: application/json');
        $this->getSessionKey();

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $customerName = trim($input['customer_name'] ?? '');
        $phone = trim($input['phone'] ?? '');

        if (empty($customerName) || empty($phone)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please provide Full Name and Mobile Number.'
            ]);
            return;
        }

        if (empty($_SESSION['inquiry_list'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Your inquiry list is empty. Please add products before submitting.'
            ]);
            return;
        }

        // Validate MOQ for all session items before saving
        foreach ($_SESSION['inquiry_list'] as $itemKey => $itemData) {
            $product = $this->productRepo->getProductWithDetails($itemData['product_id']);
            if (!$product) continue;
            $moq = (int)($product['moq'] ?? 1);
            $qty = (int)($itemData['quantity'] ?? 0);
            if ($qty < $moq) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Minimum order quantity for " . htmlspecialchars($product['name'] ?? 'product') . " is {$moq} units."
                ]);
                return;
            }
        }

        // Build item snapshots
        $snapshotItems = [];
        foreach ($_SESSION['inquiry_list'] as $itemKey => $itemData) {
            $product = $this->productRepo->getProductWithDetails($itemData['product_id']);
            if (!$product) continue;

            $variation = null;
            if (!empty($product['variations']) && !empty($itemData['variation_id'])) {
                foreach ($product['variations'] as $v) {
                    if ($v['id'] == $itemData['variation_id']) {
                        $variation = $v;
                        break;
                    }
                }
            }

            $snapshotItems[] = [
                'product_id' => $product['id'],
                'product_name_snapshot' => $product['title'] ?? $product['name'],
                'sku_snapshot' => $variation['sku'] ?? $product['sku'] ?? ('SKU-' . $product['id']),
                'product_image_snapshot' => $product['main_image'] ?? '',
                'variation_id' => $variation['id'] ?? null,
                'variation_name' => $variation['color_name'] ?? $variation['name'] ?? null,
                'quantity' => max(1, (int)($itemData['quantity'] ?? 1)),
                'price_snapshot' => (float)($product['sale_price'] ?: ($product['base_price'] ?: ($product['price'] ?? 0))),
            ];
        }

        if (empty($snapshotItems)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No valid products found in inquiry.']);
            return;
        }

        try {
            $result = $this->inquiryModel->createInquiry([
                'customer_name' => $customerName,
                'phone' => $phone,
                'email' => trim($input['email'] ?? ''),
                'company_name' => trim($input['company_name'] ?? ''),
                'city' => trim($input['city'] ?? ''),
                'state' => trim($input['state'] ?? ''),
                'gst_number' => trim($input['gst_number'] ?? ''),
                'business_type' => trim($input['business_type'] ?? ''),
                'customer_message' => trim($input['customer_message'] ?? ''),
                'delivery_timeline' => trim($input['delivery_timeline'] ?? ''),
            ], $snapshotItems);

            // Clear Session Inquiry List on Success
            $_SESSION['inquiry_list'] = [];

            echo json_encode([
                'success' => true,
                'message' => 'Inquiry submitted successfully!',
                'inquiry_number' => $result['inquiry_number'],
                'total_products' => $result['total_products'],
                'total_quantity' => $result['total_quantity'],
            ]);

        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to submit inquiry: ' . $e->getMessage()
            ]);
        }
    }
}
