<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\RfqRequest;
use App\Models\RfqPhoto;

class RfqApiController extends BaseController
{
    private RfqRequest $rfqModel;
    private RfqPhoto   $photoModel;

    // Max 4 photos, 5MB each
    private const MAX_PHOTOS     = 4;
    private const MAX_FILE_SIZE  = 5 * 1024 * 1024; // 5MB in bytes
    private const ALLOWED_TYPES  = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    private const ALLOWED_EXTS   = ['jpg', 'jpeg', 'png', 'webp'];
    private const UPLOAD_DIR     = __DIR__ . '/../../../public/uploads/rfq/';
    private const UPLOAD_WEB_DIR = 'uploads/rfq/';

    public function __construct()
    {
        parent::__construct();
        $this->rfqModel   = new RfqRequest();
        $this->photoModel = new RfqPhoto();
    }

    /**
     * POST /api/rfq/submit
     * Accepts multipart/form-data with all 3 steps' fields + optional photos.
     */
    public function submit(): void
    {
        // Only POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        $errors = $this->validateAll($_POST, $_FILES);
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors, 'message' => 'Validation failed.'], 422);
            return;
        }

        // Sanitise data
        $data = $this->sanitise($_POST);

        // Create RFQ record
        try {
            $rfqId = $this->rfqModel->createRfq($data);
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Server error. Please try again.'], 500);
            return;
        }

        // Handle file uploads
        if (!empty($_FILES['reference_photos']) && is_array($_FILES['reference_photos']['name'])) {
            $this->handlePhotoUploads($rfqId, $_FILES['reference_photos']);
        }

        $this->jsonResponse([
            'success' => true,
            'rfq_id'  => $rfqId,
            'message' => 'Your RFQ has been submitted successfully! Our sourcing team will contact you shortly on WhatsApp/Email.',
        ]);
    }

    // ---------------------------------------------------------------
    // Private Helpers
    // ---------------------------------------------------------------

    private function validateAll(array $post, array $files): array
    {
        $errors = [];

        // Step 1
        if (empty(trim($post['product_name'] ?? ''))) {
            $errors['product_name'] = 'Product name is required.';
        }
        $qty = (int)($post['quantity'] ?? 0);
        if ($qty < 1) {
            $errors['quantity'] = 'Quantity must be at least 1.';
        }
        if (empty(trim($post['unit'] ?? ''))) {
            $errors['unit'] = 'Unit is required.';
        }
        if (!isset($post['target_price']) || $post['target_price'] === '') {
            $errors['target_price'] = 'Target price is required.';
        }
        if (empty(trim($post['overall_budget'] ?? ''))) {
            $errors['overall_budget'] = 'Overall budget is required.';
        }
        if (empty(trim($post['sourcing_purpose'] ?? ''))) {
            $errors['sourcing_purpose'] = 'Sourcing purpose is required.';
        }

        // Step 2
        if (empty(trim($post['full_name'] ?? ''))) {
            $errors['full_name'] = 'Full name is required.';
        }
        $phone = preg_replace('/\D/', '', $post['phone'] ?? '');
        if (strlen($phone) < 10 || strlen($phone) > 12) {
            $errors['phone'] = 'Enter a valid 10-digit WhatsApp/phone number.';
        }
        if (empty(trim($post['email'] ?? '')) || !filter_var(trim($post['email']), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        $pincode = trim($post['pincode'] ?? '');
        if (!preg_match('/^\d{6}$/', $pincode)) {
            $errors['pincode'] = 'Enter a valid 6-digit pincode.';
        }

        // Step 3
        if (empty(trim($post['business_type'] ?? ''))) {
            $errors['business_type'] = 'Business type is required.';
        }
        if (!isset($post['has_gst']) || !in_array($post['has_gst'], ['yes', 'no'], true)) {
            $errors['has_gst'] = 'Please select GST status.';
        }

        // File validation
        if (!empty($files['reference_photos']) && is_array($files['reference_photos']['name'])) {
            $photoFiles = $files['reference_photos'];
            $count = count(array_filter($photoFiles['name']));
            if ($count > self::MAX_PHOTOS) {
                $errors['reference_photos'] = 'Maximum ' . self::MAX_PHOTOS . ' photos allowed.';
            } else {
                foreach ($photoFiles['name'] as $i => $name) {
                    if (empty($name)) continue;
                    $size = $photoFiles['size'][$i] ?? 0;
                    $type = $photoFiles['type'][$i] ?? '';
                    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                    if ($size > self::MAX_FILE_SIZE) {
                        $errors['reference_photos'] = "Photo '{$name}' exceeds 5MB limit.";
                        break;
                    }
                    if (!in_array($ext, self::ALLOWED_EXTS, true) || !in_array($type, self::ALLOWED_TYPES, true)) {
                        $errors['reference_photos'] = "Photo '{$name}' must be PNG, JPG, or WEBP.";
                        break;
                    }
                    // Double-check MIME using finfo
                    $tmpPath = $photoFiles['tmp_name'][$i] ?? '';
                    if ($tmpPath && function_exists('finfo_open')) {
                        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                        $realMime = finfo_file($finfo, $tmpPath);
                        finfo_close($finfo);
                        if (!in_array($realMime, self::ALLOWED_TYPES, true)) {
                            $errors['reference_photos'] = "Photo '{$name}' has an invalid file type.";
                            break;
                        }
                    }
                }
            }
        }

        return $errors;
    }

    private function sanitise(array $post): array
    {
        // Strip phone to digits only, keep 10 digits
        $phone = preg_replace('/\D/', '', $post['phone'] ?? '');
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }

        return [
            'product_name'           => htmlspecialchars(trim($post['product_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'product_reference_link' => filter_var(trim($post['product_reference_link'] ?? ''), FILTER_SANITIZE_URL),
            'quantity'               => (int)($post['quantity'] ?? 0),
            'unit'                   => htmlspecialchars(trim($post['unit'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'target_price'           => (float)($post['target_price'] ?? 0),
            'overall_budget'         => htmlspecialchars(trim($post['overall_budget'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'sourcing_purpose'       => htmlspecialchars(trim($post['sourcing_purpose'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'specifications'         => htmlspecialchars(trim($post['specifications'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'full_name'              => htmlspecialchars(trim($post['full_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'phone'                  => $phone,
            'email'                  => strtolower(trim($post['email'] ?? '')),
            'pincode'                => trim($post['pincode'] ?? ''),
            'business_type'          => htmlspecialchars(trim($post['business_type'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'has_gst'                => trim($post['has_gst'] ?? 'no'),
            'additional_comments'    => htmlspecialchars(trim($post['additional_comments'] ?? ''), ENT_QUOTES, 'UTF-8'),
        ];
    }

    private function handlePhotoUploads(int $rfqId, array $photoFiles): void
    {
        // Ensure upload directory exists
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        foreach ($photoFiles['name'] as $i => $originalName) {
            if (empty($originalName) || $photoFiles['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $ext      = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $safe     = uniqid('rfq_' . $rfqId . '_', true) . '.' . $ext;
            $destPath = self::UPLOAD_DIR . $safe;
            $webPath  = self::UPLOAD_WEB_DIR . $safe;

            if (move_uploaded_file($photoFiles['tmp_name'][$i], $destPath)) {
                $this->photoModel->addPhoto(
                    $rfqId,
                    $webPath,
                    htmlspecialchars($originalName, ENT_QUOTES, 'UTF-8'),
                    (int)($photoFiles['size'][$i] ?? 0)
                );
            }
        }
    }

    /**
     * GET /api/rfq/product-details?product_id=X or ?slug=Y
     * Returns full product details + active variants for RFQ modal populating.
     */
    public function getProductDetails(): void
    {
        $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
        $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

        $db = \App\Core\Database::getInstance();
        $product = null;

        if ($productId > 0) {
            $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(\PDO::FETCH_ASSOC);
        } elseif (!empty($slug)) {
            $stmt = $db->prepare("SELECT * FROM products WHERE slug = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$slug]);
            $product = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$product && is_numeric($slug)) {
                $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND status = 'active' LIMIT 1");
                $stmt->execute([(int)$slug]);
                $product = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        }

        if (!$product) {
            $this->jsonResponse(['success' => false, 'message' => 'Product not found.'], 404);
            return;
        }

        $pId = (int)$product['id'];

        // Fetch active variants
        $variantModel = new \App\Models\ProductVariant();
        $rawVariants = $variantModel->getByProduct($pId, true);

        // Fetch gallery images
        $imageModel = new \App\Models\ProductImage();
        $dbImages = $imageModel->getByProduct($pId);

        $mainImg = !empty($product['main_image']) ? asset($product['main_image']) : asset('assets/images/placeholder.jpg');
        $gallery = [$mainImg];

        foreach ($dbImages as $img) {
            $u = asset($img['image_url'] ?: $img['image_path']);
            if ($u && !in_array($u, $gallery, true)) {
                $gallery[] = $u;
            }
        }

        if (empty($rawVariants)) {
            $rawVariants = [
                [
                    'id'              => null,
                    'variant_code'    => $product['sku'] ?? '',
                    'attribute_label' => 'Edition',
                    'attribute_value' => 'Standard (' . ($product['name'] ?? 'Main Item') . ')',
                    'stock_quantity'  => (int)($product['stock_quantity'] ?? 100),
                    'wholesale_price' => (float)($product['wholesale_price'] ?: $product['price']),
                    'one_piece_price' => (float)($product['one_piece_price'] ?: $product['price']),
                    'image_url'       => $product['main_image'],
                ]
            ];
        }

        $variantsFormatted = array_map(function ($v) use ($mainImg) {
            return [
                'id'              => (int)($v['id'] ?? 0),
                'code'            => $v['variant_code'] ?? '',
                'label'           => $v['attribute_label'] ?? 'Color',
                'value'           => $v['attribute_value'] ?? '',
                'stock'           => (int)($v['stock_quantity'] ?? 0),
                'wholesale_price' => (float)($v['wholesale_price'] ?? 0),
                'one_piece_price' => (float)($v['one_piece_price'] ?? 0),
                'image'           => !empty($v['image_url']) ? asset($v['image_url']) : $mainImg,
            ];
        }, $rawVariants);

        $baseUrl = url('product/' . ($product['slug'] ?? $product['id']));

        $this->jsonResponse([
            'success' => true,
            'product' => [
                'id'         => $pId,
                'name'       => $product['name'],
                'sku'        => $product['sku'] ?? 'N/A',
                'moq'        => (int)($product['moq'] ?? 1),
                'url'        => $baseUrl,
                'main_image' => $mainImg,
                'gallery'    => $gallery,
                'variants'   => $variantsFormatted
            ]
        ]);
    }

    /**
     * GET /api/rfq/search-products?q=query
     * Returns matching active products for Change Product search dropdown.
     */
    public function searchProducts(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 1) {
            $this->jsonResponse(['success' => true, 'items' => []]);
            return;
        }

        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare(
            "SELECT id, name, sku, slug, main_image, price, sale_price, moq
             FROM products
             WHERE status = 'active' AND (name LIKE :q OR sku LIKE :q)
             ORDER BY id DESC LIMIT 10"
        );
        $stmt->execute(['q' => '%' . $q . '%']);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $items = array_map(function ($p) {
            return [
                'id'         => (int)$p['id'],
                'name'       => $p['name'],
                'sku'        => $p['sku'] ?? '',
                'slug'       => $p['slug'],
                'main_image' => !empty($p['main_image']) ? asset($p['main_image']) : asset('assets/images/placeholder.jpg'),
                'price'      => (float)($p['price'] ?? 0),
                'sale_price' => (float)($p['sale_price'] ?? 0),
                'moq'        => (int)($p['moq'] ?? 1)
            ];
        }, $rows);

        $this->jsonResponse(['success' => true, 'items' => $items]);
    }
}
