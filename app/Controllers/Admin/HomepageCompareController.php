<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;

class HomepageCompareController extends Controller {

    public function index(): string {
        $db = Database::getInstance();
        $productModel = new Product();

        // Get currently selected compare products
        $stmt = $db->query("
            SELECT hcp.id as compare_id, hcp.sort_order, p.* 
            FROM homepage_compare_products hcp
            JOIN products p ON hcp.product_id = p.id
            ORDER BY hcp.sort_order ASC
        ");
        $compareProducts = $stmt->fetchAll();

        // Get all active products for selection
        $allProducts = $productModel->getAllActiveProducts();

        return $this->render('admin/homepage_compare/index', [
            'compareProducts' => $compareProducts,
            'allProducts' => $allProducts
        ]);
    }

    public function add(): void {
        $productId = (int)$this->request->input('product_id');

        if ($productId > 0) {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT IGNORE INTO homepage_compare_products (product_id, sort_order) VALUES (?, (SELECT COALESCE(MAX(sort_order), 0) + 1 FROM homepage_compare_products h))");
            $stmt->execute([$productId]);
            $this->setFlash('success', 'Product added to Homepage Compare Section.');
        }

        $this->response->redirect(url('admin/homepage-compare'));
    }

    public function remove(): void {
        $compareId = (int)$this->request->input('compare_id');

        if ($compareId > 0) {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM homepage_compare_products WHERE id = ?");
            $stmt->execute([$compareId]);
            $this->setFlash('success', 'Product removed from Homepage Compare Section.');
        }

        $this->response->redirect(url('admin/homepage-compare'));
    }
}
