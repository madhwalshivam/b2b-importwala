<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;

class ProductCompatibilityController extends Controller {

    public function index(): string {
        $db = Database::getInstance();
        $productId = (int)$this->request->input('product_id', 0);
        
        $products = $db->query("SELECT id, name, sku FROM products ORDER BY name ASC")->fetchAll();

        if ($productId <= 0 && !empty($products)) {
            $productId = (int)$products[0]['id'];
        }

        $selectedProduct = null;
        $compatibilities = [];
        $includedItems = [];
        $badges = [];
        $vehicleImages = [];

        if ($productId > 0) {
            $stmtP = $db->prepare("SELECT * FROM products WHERE id = ?");
            $stmtP->execute([$productId]);
            $selectedProduct = $stmtP->fetch();

            if ($selectedProduct) {
                $productModel = new Product();
                $compatibilities = $productModel->getVehicleCompatibilities($productId);
                $includedItems = $productModel->getIncludedItems($productId);
                $badges = $productModel->getBadges($productId);
                $vehicleImages = $productModel->getVehicleInstallationImages($productId);
            }
        }

        return $this->render('admin/product_compatibility', [
            'products' => $products,
            'productId' => $productId,
            'product' => $selectedProduct,
            'compatibilities' => $compatibilities,
            'includedItems' => $includedItems,
            'badges' => $badges,
            'vehicleImages' => $vehicleImages,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error')
        ]);
    }

    public function update(int $productId): void {
        $db = Database::getInstance();

        // 1. Update Product Specs & OEM Comparison Fields
        $material = trim($this->request->input('material', ''));
        $finish = trim($this->request->input('finish', ''));
        $weightGrams = (int)$this->request->input('weight_grams', 850);
        $warrantyMonths = (int)$this->request->input('warranty_months', 12);
        $installationDifficulty = $this->request->input('installation_difficulty', 'easy');
        $installationTime = (int)$this->request->input('installation_time_minutes', 15);

        $oemPrice = (float)$this->request->input('oem_price', 0);
        $oemMaterial = trim($this->request->input('oem_material', ''));
        $oemFinish = trim($this->request->input('oem_finish', ''));
        $oemFitment = trim($this->request->input('oem_fitment', ''));
        $oemWarrantyMonths = (int)$this->request->input('oem_warranty_months', 6);

        $matQualityPct = (int)$this->request->input('material_quality_pct', 95);
        $paintFinishPct = (int)$this->request->input('paint_finish_pct', 90);
        $fitmentPct = (int)$this->request->input('fitment_pct', 98);
        $durabilityPct = (int)$this->request->input('durability_pct', 92);

        $stmtUpd = $db->prepare("
            UPDATE products SET 
                material = ?, finish = ?, weight_grams = ?, warranty_months = ?,
                installation_difficulty = ?, installation_time_minutes = ?,
                oem_price = ?, oem_material = ?, oem_finish = ?, oem_fitment = ?, oem_warranty_months = ?,
                material_quality_pct = ?, paint_finish_pct = ?, fitment_pct = ?, durability_pct = ?
            WHERE id = ?
        ");
        $stmtUpd->execute([
            $material, $finish, $weightGrams, $warrantyMonths,
            $installationDifficulty, $installationTime,
            $oemPrice, $oemMaterial, $oemFinish, $oemFitment, $oemWarrantyMonths,
            $matQualityPct, $paintFinishPct, $fitmentPct, $durabilityPct,
            $productId
        ]);

        // 2. Sync Vehicle Compatibility Checklist
        $vehicles = $this->request->input('vehicles', []);
        $db->prepare("DELETE FROM product_vehicle_compatibility WHERE product_id = ?")->execute([$productId]);
        $stmtComp = $db->prepare("INSERT INTO product_vehicle_compatibility (product_id, vehicle_name, is_compatible) VALUES (?, ?, 1)");
        if (is_array($vehicles)) {
            foreach ($vehicles as $vName) {
                if (!empty($vName)) {
                    $stmtComp->execute([$productId, trim($vName)]);
                }
            }
        }

        // 3. Sync What's Included Checklist
        $included = $this->request->input('included_items', []);
        $db->prepare("DELETE FROM product_included_items WHERE product_id = ?")->execute([$productId]);
        $stmtInc = $db->prepare("INSERT INTO product_included_items (product_id, item_name, is_included, sort_order) VALUES (?, ?, 1, ?)");
        if (is_array($included)) {
            foreach ($included as $idx => $itemText) {
                if (!empty(trim($itemText))) {
                    $stmtInc->execute([$productId, trim($itemText), $idx]);
                }
            }
        }

        // 4. Sync Badges
        $badgeTexts = $this->request->input('badge_texts', []);
        $badgeIcons = $this->request->input('badge_icons', []);
        $db->prepare("DELETE FROM product_badges WHERE product_id = ?")->execute([$productId]);
        $stmtBadge = $db->prepare("INSERT INTO product_badges (product_id, badge_text, badge_icon, sort_order) VALUES (?, ?, ?, ?)");
        if (is_array($badgeTexts)) {
            foreach ($badgeTexts as $idx => $bText) {
                if (!empty(trim($bText))) {
                    $icon = $badgeIcons[$idx] ?? 'check';
                    $stmtBadge->execute([$productId, trim($bText), trim($icon), $idx]);
                }
            }
        }

        $this->setFlash('success', 'Product compatibility & comparison specs updated successfully!');
        $this->response->redirect(url('admin/product-compatibility?product_id=' . $productId));
    }
}
