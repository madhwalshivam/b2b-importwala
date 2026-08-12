<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class CouponController extends Controller {

    public function index(): string {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT c.*, 
                   COUNT(u.id) as usage_count,
                   SUM(u.discount_applied) as total_discount_given
            FROM coupons c
            LEFT JOIN coupon_usage u ON c.id = u.coupon_id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        $coupons = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('admin/coupons/index', [
            'coupons' => $coupons
        ]);
    }

    public function create(): string {
        $db = Database::getInstance();
        $products = $db->query("SELECT id, name, sku FROM products WHERE status = 'active' ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $categories = $db->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('admin/coupons/form', [
            'coupon' => null,
            'products' => $products,
            'categories' => $categories,
            'selectedProductIds' => [],
            'selectedCategoryIds' => []
        ]);
    }

    public function store(): void {
        $db = Database::getInstance();
        $code = strtoupper(trim((string)$this->request->input('code')));
        $description = trim((string)$this->request->input('description'));
        $discountType = $this->request->input('discount_type') === 'percentage' ? 'percentage' : 'flat';
        $discountValue = (float)$this->request->input('discount_value', 0);
        $minOrderValue = (float)$this->request->input('min_order_value', 0);
        $maxDiscountCap = $discountType === 'percentage' && $this->request->input('max_discount_cap') !== '' ? (float)$this->request->input('max_discount_cap') : null;
        $usageLimitTotal = $this->request->input('usage_limit_total') !== '' ? (int)$this->request->input('usage_limit_total') : null;
        $usageLimitPerUser = $this->request->input('usage_limit_per_user') !== '' ? (int)$this->request->input('usage_limit_per_user') : 1;
        $scopeType = in_array($this->request->input('scope_type'), ['specific_products', 'specific_categories'], true) ? $this->request->input('scope_type') : 'all_products';
        $validFrom = $this->request->input('valid_from') ? date('Y-m-d H:i:s', strtotime($this->request->input('valid_from'))) : date('Y-m-d H:i:s');
        $validUntil = $this->request->input('valid_until') ? date('Y-m-d H:i:s', strtotime($this->request->input('valid_until'))) : null;
        $isActive = $this->request->input('is_active') ? 1 : 0;

        // Validation
        if (empty($code)) {
            $this->setFlash('error', 'Coupon code is required.');
            $this->redirect(url('admin/coupons/create'));
            return;
        }

        $checkStmt = $db->prepare("SELECT id FROM coupons WHERE code = ? LIMIT 1");
        $checkStmt->execute([$code]);
        if ($checkStmt->fetch()) {
            $this->setFlash('error', "Coupon code '{$code}' already exists.");
            $this->redirect(url('admin/coupons/create'));
            return;
        }

        if ($discountValue <= 0 || ($discountType === 'percentage' && $discountValue > 100)) {
            $this->setFlash('error', 'Please enter a valid discount value (1-100% for percentage).');
            $this->redirect(url('admin/coupons/create'));
            return;
        }

        $stmt = $db->prepare("
            INSERT INTO coupons (code, description, discount_type, discount_value, min_order_value, max_discount_cap, usage_limit_total, usage_limit_per_user, scope_type, valid_from, valid_until, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $code, $description, $discountType, $discountValue, $minOrderValue, $maxDiscountCap,
            $usageLimitTotal, $usageLimitPerUser, $scopeType, $validFrom, $validUntil, $isActive
        ]);
        $couponId = (int)$db->lastInsertId();

        // Save Scopes
        if ($scopeType === 'specific_products') {
            $prodIds = (array)$this->request->input('product_ids', []);
            $stmtP = $db->prepare("INSERT IGNORE INTO coupon_products (coupon_id, product_id) VALUES (?, ?)");
            foreach ($prodIds as $pid) {
                if ((int)$pid > 0) $stmtP->execute([$couponId, (int)$pid]);
            }
        } elseif ($scopeType === 'specific_categories') {
            $catIds = (array)$this->request->input('category_ids', []);
            $stmtC = $db->prepare("INSERT IGNORE INTO coupon_categories (coupon_id, category_id) VALUES (?, ?)");
            foreach ($catIds as $cid) {
                if ((int)$cid > 0) $stmtC->execute([$couponId, (int)$cid]);
            }
        }

        $this->setFlash('success', "Coupon '{$code}' created successfully!");
        $this->redirect(url('admin/coupons'));
    }

    public function edit(int $id): string {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        $coupon = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$coupon) {
            $this->setFlash('error', 'Coupon not found.');
            $this->redirect(url('admin/coupons'));
        }

        $products = $db->query("SELECT id, name, sku FROM products WHERE status = 'active' ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $categories = $db->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $stP = $db->prepare("SELECT product_id FROM coupon_products WHERE coupon_id = ?");
        $stP->execute([$id]);
        $selectedProductIds = array_map('intval', $stP->fetchAll(\PDO::FETCH_COLUMN));

        $stC = $db->prepare("SELECT category_id FROM coupon_categories WHERE coupon_id = ?");
        $stC->execute([$id]);
        $selectedCategoryIds = array_map('intval', $stC->fetchAll(\PDO::FETCH_COLUMN));

        return $this->render('admin/coupons/form', [
            'coupon' => $coupon,
            'products' => $products,
            'categories' => $categories,
            'selectedProductIds' => $selectedProductIds,
            'selectedCategoryIds' => $selectedCategoryIds
        ]);
    }

    public function update(int $id): void {
        $db = Database::getInstance();
        $code = strtoupper(trim((string)$this->request->input('code')));
        $description = trim((string)$this->request->input('description'));
        $discountType = $this->request->input('discount_type') === 'percentage' ? 'percentage' : 'flat';
        $discountValue = (float)$this->request->input('discount_value', 0);
        $minOrderValue = (float)$this->request->input('min_order_value', 0);
        $maxDiscountCap = $discountType === 'percentage' && $this->request->input('max_discount_cap') !== '' ? (float)$this->request->input('max_discount_cap') : null;
        $usageLimitTotal = $this->request->input('usage_limit_total') !== '' ? (int)$this->request->input('usage_limit_total') : null;
        $usageLimitPerUser = $this->request->input('usage_limit_per_user') !== '' ? (int)$this->request->input('usage_limit_per_user') : 1;
        $scopeType = in_array($this->request->input('scope_type'), ['specific_products', 'specific_categories'], true) ? $this->request->input('scope_type') : 'all_products';
        $validFrom = $this->request->input('valid_from') ? date('Y-m-d H:i:s', strtotime($this->request->input('valid_from'))) : date('Y-m-d H:i:s');
        $validUntil = $this->request->input('valid_until') ? date('Y-m-d H:i:s', strtotime($this->request->input('valid_until'))) : null;
        $isActive = $this->request->input('is_active') ? 1 : 0;

        $checkStmt = $db->prepare("SELECT id FROM coupons WHERE code = ? AND id != ? LIMIT 1");
        $checkStmt->execute([$code, $id]);
        if ($checkStmt->fetch()) {
            $this->setFlash('error', "Coupon code '{$code}' is already used by another coupon.");
            $this->redirect(url("admin/coupons/edit/{$id}"));
            return;
        }

        $stmt = $db->prepare("
            UPDATE coupons SET 
                code = ?, description = ?, discount_type = ?, discount_value = ?,
                min_order_value = ?, max_discount_cap = ?, usage_limit_total = ?, usage_limit_per_user = ?,
                scope_type = ?, valid_from = ?, valid_until = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $code, $description, $discountType, $discountValue,
            $minOrderValue, $maxDiscountCap, $usageLimitTotal, $usageLimitPerUser,
            $scopeType, $validFrom, $validUntil, $isActive, $id
        ]);

        // Reset and Update Scopes
        $db->prepare("DELETE FROM coupon_products WHERE coupon_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM coupon_categories WHERE coupon_id = ?")->execute([$id]);

        if ($scopeType === 'specific_products') {
            $prodIds = (array)$this->request->input('product_ids', []);
            $stmtP = $db->prepare("INSERT IGNORE INTO coupon_products (coupon_id, product_id) VALUES (?, ?)");
            foreach ($prodIds as $pid) {
                if ((int)$pid > 0) $stmtP->execute([$id, (int)$pid]);
            }
        } elseif ($scopeType === 'specific_categories') {
            $catIds = (array)$this->request->input('category_ids', []);
            $stmtC = $db->prepare("INSERT IGNORE INTO coupon_categories (coupon_id, category_id) VALUES (?, ?)");
            foreach ($catIds as $cid) {
                if ((int)$cid > 0) $stmtC->execute([$id, (int)$cid]);
            }
        }

        $this->setFlash('success', "Coupon '{$code}' updated successfully!");
        $this->redirect(url('admin/coupons'));
    }

    public function delete(int $id): void {
        $db = Database::getInstance();
        $db->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
        $this->setFlash('success', 'Coupon deleted successfully!');
        $this->redirect(url('admin/coupons'));
    }

    public function toggleStatus(int $id): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE coupons SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
        $stmt->execute([$id]);

        if ($this->request->isAjax()) {
            $st = $db->prepare("SELECT is_active FROM coupons WHERE id = ?");
            $st->execute([$id]);
            $newStatus = (int)$st->fetchColumn();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'is_active' => $newStatus]);
            exit;
        }

        $this->setFlash('success', 'Coupon status updated!');
        $this->redirect(url('admin/coupons'));
    }

    public function usage(int $id): string {
        $db = Database::getInstance();
        $stmtC = $db->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmtC->execute([$id]);
        $coupon = $stmtC->fetch(\PDO::FETCH_ASSOC);

        if (!$coupon) {
            $this->redirect(url('admin/coupons'));
        }

        $stmt = $db->prepare("
            SELECT u.*, 
                   o.order_number, o.customer_name, o.customer_email, o.total_amount
            FROM coupon_usage u
            LEFT JOIN orders o ON u.order_id = o.id
            WHERE u.coupon_id = ?
            ORDER BY u.used_at DESC
        ");
        $stmt->execute([$id]);
        $redemptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('admin/coupons/usage', [
            'coupon' => $coupon,
            'redemptions' => $redemptions
        ]);
    }
}
