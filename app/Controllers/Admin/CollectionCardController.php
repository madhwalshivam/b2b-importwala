<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\CollectionCard;
use App\Models\Product;

class CollectionCardController extends Controller {

    private CollectionCard $cardModel;
    private Product $productModel;

    public function __construct() {
        parent::__construct();
        $this->cardModel   = new CollectionCard();
        $this->productModel = new Product();
    }

    /**
     * Admin index — sab cards dikhao
     */
    public function index(): string {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $cards = $this->cardModel->getAll();
        $cardProducts = [];
        foreach ($cards as $card) {
            $cardProducts[$card['id']] = $this->cardModel->getCardProducts((int)$card['id']);
        }

        $allProducts = $this->productModel->getAllActiveProducts();

        return $this->render('admin/collection_cards/index', [
            'cards'       => $cards,
            'cardProducts' => $cardProducts,
            'allProducts' => $allProducts,
        ]);
    }

    /**
     * Public storefront API — active cards with products
     */
    public function apiIndex(): void {
        $data = $this->cardModel->getActiveWithProducts(12);
        // Asset URLs fix karo
        foreach ($data as &$card) {
            foreach ($card['products'] as &$p) {
                $p['main_image'] = asset($p['main_image'] ?? 'assets/images/placeholder.jpg');
            }
        }
        unset($card, $p);
        $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * Naya card store karo
     */
    public function store(): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $title = trim($this->request->input('title', ''));
        if (empty($title)) {
            $this->setFlash('error', 'Card title is required.');
            $this->redirect(url('admin/collection-cards'));
            return;
        }

        $imagePath = $this->handleImageUpload('image_file', 'image_url');

        $cardId = $this->cardModel->createCard([
            'title'      => $title,
            'subtitle'   => trim($this->request->input('subtitle', '')),
            'image'      => $imagePath,
            'link_url'   => trim($this->request->input('link_url', '/catalog')) ?: '/catalog',
            'badge_text' => trim($this->request->input('badge_text', '')),
            'sort_order' => (int)$this->request->input('sort_order', 0),
            'is_active'  => $this->request->input('is_active') ? 1 : 0,
        ]);

        // Agar products selected hain to attach karo
        $productIds = $this->request->input('product_ids', []);
        if (!empty($productIds) && is_array($productIds)) {
            $this->cardModel->saveCardProducts($cardId, $productIds);
        }

        $this->setFlash('success', "Collection card \"{$title}\" created successfully.");
        $this->redirect(url('admin/collection-cards'));
    }

    /**
     * Card update karo
     */
    public function update(string $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $cardId = (int)$id;
        $card   = $this->cardModel->findById($cardId);
        if (!$card) {
            $this->setFlash('error', 'Collection card not found.');
            $this->redirect(url('admin/collection-cards'));
            return;
        }

        $title = trim($this->request->input('title', ''));
        if (empty($title)) {
            $this->setFlash('error', 'Card title is required.');
            $this->redirect(url('admin/collection-cards'));
            return;
        }

        $imagePath = $this->handleImageUpload('image_file', 'image_url') ?: $card['image'];

        $this->cardModel->updateCard($cardId, [
            'title'      => $title,
            'subtitle'   => trim($this->request->input('subtitle', '')),
            'image'      => $imagePath,
            'link_url'   => trim($this->request->input('link_url', '/catalog')) ?: '/catalog',
            'badge_text' => trim($this->request->input('badge_text', '')),
            'sort_order' => (int)$this->request->input('sort_order', 0),
            'is_active'  => $this->request->input('is_active') ? 1 : 0,
        ]);

        $this->setFlash('success', "Collection card updated successfully.");
        $this->redirect(url('admin/collection-cards'));
    }

    /**
     * Card delete karo
     */
    public function delete(string $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $cardId = (int)$id;
        $card   = $this->cardModel->findById($cardId);
        $title  = $card['title'] ?? 'Card';

        $this->cardModel->deleteCard($cardId);
        $this->setFlash('success', "\"{$title}\" deleted successfully.");
        $this->redirect(url('admin/collection-cards'));
    }

    /**
     * Card ke products update karo (AJAX)
     */
    public function updateProducts(string $id): void {
        if (!Auth::check()) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        // JSON body se data read karo
        $body = [];
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput)) {
            $body = json_decode($rawInput, true) ?: [];
        }

        // CSRF validate karo (header ya body se)
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $body['_csrf_token'] ?? $_POST['_csrf_token'] ?? null;
        $session = new \App\Core\Session();
        if (!$session->validateCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'CSRF verification failed.'], 403);
            return;
        }

        $cardId = (int)$id;
        $card   = $this->cardModel->findById($cardId);
        if (!$card) {
            $this->json(['success' => false, 'message' => 'Card not found'], 404);
            return;
        }

        $productIds = $body['product_ids'] ?? $this->request->input('product_ids', []);
        if (!is_array($productIds)) $productIds = [];

        $ok = $this->cardModel->saveCardProducts($cardId, $productIds);

        $this->json([
            'success' => $ok,
            'message' => $ok ? 'Products saved successfully.' : 'Failed to save products.',
            'count'   => count($productIds),
        ]);
    }

    /**
     * AJAX product search (modal ke liye)
     */
    public function searchProducts(): void {
        if (!Auth::check()) {
            $this->json(['success' => false, 'data' => []], 401);
            return;
        }

        $q    = trim($this->request->input('q', ''));
        $page = max(1, (int)$this->request->input('page', 1));

        $db  = \App\Core\Database::getInstance();
        $sql = "
            SELECT p.id, p.name, p.sku, p.price, p.sale_price, p.main_image,
                   c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
        ";
        $params = [];

        if (!empty($q)) {
            $sql   .= " AND (p.name LIKE ? OR p.sku LIKE ? OR c.name LIKE ?)";
            $like   = '%' . $q . '%';
            $params = [$like, $like, $like];
        }

        $sql .= " ORDER BY p.name ASC LIMIT 30 OFFSET " . (($page - 1) * 30);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        foreach ($products as &$p) {
            $p['main_image'] = asset($p['main_image'] ?? 'assets/images/placeholder.jpg');
        }

        $this->json(['success' => true, 'data' => $products]);
    }

    /**
     * AJAX reorder
     */
    public function reorder(): void {
        if (!Auth::check()) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }
        $order = $this->request->input('order', []);
        if (is_array($order)) {
            $this->cardModel->updateSortOrder($order);
        }
        $this->json(['success' => true]);
    }

    // -------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------

    private function handleImageUpload(string $fileInput, string $urlInput): string {
        $urlVal = trim($this->request->input($urlInput, ''));

        if (!empty($_FILES[$fileInput]['name']) && $_FILES[$fileInput]['error'] === UPLOAD_ERR_OK) {
            $file        = $_FILES[$fileInput];
            $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
            $dir         = __DIR__ . '/../../../public/uploads/collection_cards/';

            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            if (in_array($ext, $allowedExts) && $file['size'] <= 10 * 1024 * 1024) {
                $filename = 'cc_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
                    return '/uploads/collection_cards/' . $filename;
                }
            }
        }

        return $urlVal;
    }
}
