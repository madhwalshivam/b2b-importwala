<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Factory;
use App\Helpers\Paginator;

class FactoryController extends Controller
{
    protected Factory $factoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->factoryModel = new Factory();
    }

    /**
     * Factory List View.
     */
    public function index(): string
    {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
        }

        $page = (int)($this->request->input('page', 1));
        $perPage = (int)($this->request->input('per_page', 20));
        $search = trim($this->request->input('search', ''));
        $status = trim($this->request->input('status', ''));

        $data = $this->factoryModel->getPaginatedFactories($search, $status, $page, $perPage);
        $paginator = new Paginator($data['total'], $perPage, $page, url('admin/factories'), $_GET);

        return $this->render('admin/factories/index', [
            'factories' => $data['items'],
            'paginator' => $paginator,
            'search'    => $search,
            'status'    => $status,
            'total'     => $data['total'],
        ]);
    }

    /**
     * Show Manual Add Factory Form.
     */
    public function create(): string
    {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/factories'));
        }

        $nextCode = $this->factoryModel->generateNextCode();

        return $this->render('admin/factories/create', [
            'nextCode' => $nextCode,
        ]);
    }

    /**
     * Store New Factory.
     */
    public function store(): void
    {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/factories'));
        }

        $name = trim($this->request->input('name'));
        if (empty($name)) {
            $this->setFlash('error', 'Factory/Manufacturer name is required.');
            $this->redirect(url('admin/factories/create'));
            return;
        }

        $factoryCode = strtoupper(trim($this->request->input('factory_code')));
        if (empty($factoryCode)) {
            $factoryCode = $this->factoryModel->generateNextCode();
        } else {
            // Check uniqueness
            $existing = $this->factoryModel->findBy('factory_code', $factoryCode);
            if ($existing) {
                $this->setFlash('error', "Factory code '{$factoryCode}' is already in use.");
                $this->redirect(url('admin/factories/create'));
                return;
            }
        }

        $factoryId = $this->factoryModel->insert([
            'factory_code'    => $factoryCode,
            'name'            => $name,
            'contact_person'  => trim($this->request->input('contact_person')) ?: null,
            'phone'           => trim($this->request->input('phone')) ?: null,
            'whatsapp'        => trim($this->request->input('whatsapp')) ?: null,
            'email'           => trim($this->request->input('email')) ?: null,
            'store_url'       => trim($this->request->input('store_url')) ?: null,
            'source_platform' => trim($this->request->input('source_platform')) ?: null,
            'status'          => $this->request->input('status', 'active'),
            'notes'           => trim($_POST['notes'] ?? '') ?: null,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        activity_log('Create Factory', 'Factories', $factoryId, "Created factory: {$name} ({$factoryCode})");
        $this->setFlash('success', "Factory {$factoryCode} created successfully.");
        $this->redirect(url('admin/factories/show/' . $factoryId));
    }

    /**
     * Show Factory Edit Form.
     */
    public function edit(int $id): string
    {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/factories'));
        }

        $factory = $this->factoryModel->find($id);
        if (!$factory) {
            $this->setFlash('error', 'Factory record not found.');
            $this->redirect(url('admin/factories'));
        }

        return $this->render('admin/factories/edit', [
            'factory' => $factory,
        ]);
    }

    /**
     * Update Factory.
     */
    public function update(int $id): void
    {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/factories'));
        }

        $factory = $this->factoryModel->find($id);
        if (!$factory) {
            $this->setFlash('error', 'Factory record not found.');
            $this->redirect(url('admin/factories'));
            return;
        }

        $name = trim($this->request->input('name'));
        if (empty($name)) {
            $this->setFlash('error', 'Factory name is required.');
            $this->redirect(url('admin/factories/edit/' . $id));
            return;
        }

        $this->factoryModel->update($id, [
            'name'            => $name,
            'contact_person'  => trim($this->request->input('contact_person')) ?: null,
            'phone'           => trim($this->request->input('phone')) ?: null,
            'whatsapp'        => trim($this->request->input('whatsapp')) ?: null,
            'email'           => trim($this->request->input('email')) ?: null,
            'store_url'       => trim($this->request->input('store_url')) ?: null,
            'source_platform' => trim($this->request->input('source_platform')) ?: null,
            'status'          => $this->request->input('status', 'active'),
            'notes'           => trim($_POST['notes'] ?? '') ?: null,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        activity_log('Update Factory', 'Factories', $id, "Updated factory details for {$factory['factory_code']}");
        $this->setFlash('success', 'Factory details updated successfully.');
        $this->redirect(url('admin/factories/show/' . $id));
    }

    /**
     * Show Dedicated Factory Details & Isolated Products Page.
     */
    public function show(int $id): string
    {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/factories'));
        }

        $factory = $this->factoryModel->find($id);
        if (!$factory) {
            $this->setFlash('error', 'Factory not found.');
            $this->redirect(url('admin/factories'));
        }

        $page = (int)($this->request->input('page', 1));
        $perPage = (int)($this->request->input('per_page', 20));
        $search = trim($this->request->input('search', ''));
        $status = trim($this->request->input('status', ''));

        $productData = $this->factoryModel->getFactoryProducts($id, $search, $status, $page, $perPage);
        $paginator = new Paginator($productData['total'], $perPage, $page, url('admin/factories/show/' . $id), $_GET);

        // Fetch assignable products (not currently assigned to this factory)
        $db = \App\Core\Database::getInstance();
        $assignableStmt = $db->prepare("SELECT id, name, sku, price, main_image, factory_id FROM products WHERE factory_id IS NULL OR factory_id != ? ORDER BY name ASC LIMIT 300");
        $assignableStmt->execute([$id]);
        $assignableProducts = $assignableStmt->fetchAll();

        return $this->render('admin/factories/show', [
            'factory'            => $factory,
            'products'           => $productData['items'],
            'assignableProducts' => $assignableProducts,
            'paginator'          => $paginator,
            'search'             => $search,
            'status'             => $status,
            'totalProduct'       => $productData['total'],
        ]);
    }

    /**
     * Assign existing product(s) to this Factory.
     */
    public function assignProducts(int $id): void
    {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/factories'));
        }

        $factory = $this->factoryModel->find($id);
        if (!$factory) {
            $this->setFlash('error', 'Factory not found.');
            $this->redirect(url('admin/factories'));
            return;
        }

        $productIds = $_POST['product_ids'] ?? [];
        if (!is_array($productIds)) {
            $productIds = array_filter(array_map('intval', explode(',', (string)$productIds)));
        }

        if (empty($productIds)) {
            $this->setFlash('error', 'Please select at least one product to link to this factory.');
            $this->redirect(url('admin/factories/show/' . $id));
            return;
        }

        $db = \App\Core\Database::getInstance();
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        
        $stmt = $db->prepare("UPDATE products SET factory_id = ?, manufacturer_id_code = COALESCE(NULLIF(manufacturer_id_code, ''), ?) WHERE id IN ({$placeholders})");
        $params = array_merge([$id, $factory['factory_code']], array_map('intval', $productIds));
        $stmt->execute($params);

        $count = count($productIds);
        activity_log('Assign Factory Products', 'Factories', $id, "Linked {$count} products to factory {$factory['factory_code']}");
        $this->setFlash('success', "Successfully linked {$count} product(s) to {$factory['name']} ({$factory['factory_code']}).");
        $this->redirect(url('admin/factories/show/' . $id));
    }

    /**
     * Remove / Unlink product from Factory.
     */
    public function removeProduct(int $id): void
    {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/factories'));
        }

        $productId = (int)($this->request->input('product_id', 0));
        if ($productId > 0) {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("UPDATE products SET factory_id = NULL WHERE id = ? AND factory_id = ?");
            $stmt->execute([$productId, $id]);
            activity_log('Unlink Factory Product', 'Factories', $id, "Unlinked product #{$productId} from factory #{$id}");
            $this->setFlash('success', 'Product unlinked from factory successfully.');
        }

        $this->redirect(url('admin/factories/show/' . $id));
    }

    /**
     * Delete / Archive Factory.
     */
    public function delete(int $id): void
    {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/factories'));
        }

        $factory = $this->factoryModel->find($id);
        if ($factory) {
            // Reassign products linked to this factory to NULL (Unassigned)
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("UPDATE products SET factory_id = NULL WHERE factory_id = ?");
            $stmt->execute([$id]);

            $this->factoryModel->delete($id);
            activity_log('Delete Factory', 'Factories', $id, "Deleted factory {$factory['factory_code']} ({$factory['name']})");
            $this->setFlash('success', "Factory {$factory['factory_code']} deleted. Linked products set to Unassigned.");
        }

        $this->redirect(url('admin/factories'));
    }
}
