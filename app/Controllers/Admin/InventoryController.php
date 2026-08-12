<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Product;

class InventoryController extends Controller {
    public function index(): string {
        if (!Auth::hasPermission('inventory.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $productModel = new Product();
        $filter = $this->request->input('filter', 'all');

        $where = '1=1';
        if ($filter === 'low') {
            $where = 'stock <= low_stock_threshold';
        } elseif ($filter === 'out') {
            $where = 'stock = 0';
        }

        $result = $productModel->paginate(1, 100, $where, [], 'stock ASC');

        return $this->render('admin/inventory/index', [
            'products' => $result['items'],
            'filter' => $filter
        ]);
    }

    public function updateStock(): void {
        if (!Auth::hasPermission('inventory.edit')) {
            $this->redirect(url('admin/inventory'));
        }

        $stocks = $_POST['stock'] ?? [];
        $productModel = new Product();

        foreach ($stocks as $productId => $stockValue) {
            $productModel->update((int)$productId, ['stock' => max(0, (int)$stockValue)]);
        }

        activity_log('Update Inventory', 'Inventory', null, "Updated bulk stock inventory levels");
        $this->setFlash('success', 'Stock inventory updated successfully.');
        $this->redirect(url('admin/inventory'));
    }
}
