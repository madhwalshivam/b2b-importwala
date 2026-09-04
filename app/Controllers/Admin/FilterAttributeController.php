<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Response;
use PDO;

class FilterAttributeController extends BaseController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * List all filter attributes with option counts & assigned categories
     */
    public function index(): void
    {
        $stmt = $this->db->query("
            SELECT fa.*, 
                   (SELECT COUNT(*) FROM filter_attribute_options fao WHERE fao.attribute_id = fa.id) as options_count
            FROM filter_attributes fa
            ORDER BY fa.sort_order ASC, fa.id ASC
        ");
        $attributes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($attributes as &$attr) {
            if ($attr['is_global']) {
                $attr['category_names'] = ['All Categories (Global)'];
            } else {
                $catStmt = $this->db->prepare("
                    SELECT c.name 
                    FROM filter_attribute_categories fac 
                    JOIN categories c ON fac.category_id = c.id 
                    WHERE fac.attribute_id = ?
                ");
                $catStmt->execute([$attr['id']]);
                $attr['category_names'] = $catStmt->fetchAll(PDO::FETCH_COLUMN) ?: ['No categories assigned'];
            }
        }
        unset($attr);

        $this->renderView('admin/filters/index', [
            'attributes' => $attributes,
            'title' => 'Manage Filters & Attributes | Admin'
        ]);
    }

    /**
     * Show form to create new filter attribute
     */
    public function create(): void
    {
        $categories = $this->db->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->renderView('admin/filters/edit', [
            'attribute' => null,
            'options' => [],
            'assignedCategoryIds' => [],
            'categories' => $categories,
            'title' => 'Create New Filter Attribute'
        ]);
    }

    /**
     * Save new filter attribute
     */
    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'multi_select';
        $isGlobal = isset($_POST['is_global']) ? (int)$_POST['is_global'] : 1;
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $categoryIds = $_POST['category_ids'] ?? [];
        $rawOptions = array_filter(array_map('trim', explode("\n", $_POST['options_text'] ?? '')));

        if (empty($name)) {
            (new Response())->redirect(url('admin/filters/create?error=Name+is+required'));
            return;
        }

        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($name));

        // Prevent duplicate slug
        $chk = $this->db->prepare("SELECT COUNT(*) FROM filter_attributes WHERE slug = ?");
        $chk->execute([$slug]);
        if ($chk->fetchColumn() > 0) {
            $slug .= '_' . rand(100, 999);
        }

        $stmt = $this->db->prepare("INSERT INTO filter_attributes (name, slug, type, is_global, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $type, $isGlobal, $isActive, $sortOrder]);
        $attrId = $this->db->lastInsertId();

        // Assign categories if not global
        if (!$isGlobal && !empty($categoryIds)) {
            $catStmt = $this->db->prepare("INSERT INTO filter_attribute_categories (attribute_id, category_id) VALUES (?, ?)");
            foreach ($categoryIds as $catId) {
                $catStmt->execute([$attrId, (int)$catId]);
            }
        }

        // Add option values
        if (!empty($rawOptions)) {
            $optStmt = $this->db->prepare("INSERT INTO filter_attribute_options (attribute_id, value, slug, sort_order) VALUES (?, ?, ?, ?)");
            foreach ($rawOptions as $idx => $optVal) {
                $optSlug = preg_replace('/[^a-z0-9]+/', '-', strtolower($optVal));
                $optStmt->execute([$attrId, $optVal, $optSlug, $idx + 1]);
            }
        }

        (new Response())->redirect(url('admin/filters?success=Filter+attribute+created+successfully'));
    }

    /**
     * Edit filter attribute
     */
    public function edit(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM filter_attributes WHERE id = ?");
        $stmt->execute([$id]);
        $attribute = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$attribute) {
            (new Response())->redirect(url('admin/filters?error=Attribute+not+found'));
            return;
        }

        // Fetch options
        $optStmt = $this->db->prepare("SELECT * FROM filter_attribute_options WHERE attribute_id = ? ORDER BY sort_order ASC, id ASC");
        $optStmt->execute([$id]);
        $options = $optStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch assigned categories
        $catStmt = $this->db->prepare("SELECT category_id FROM filter_attribute_categories WHERE attribute_id = ?");
        $catStmt->execute([$id]);
        $assignedCategoryIds = $catStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $categories = $this->db->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->renderView('admin/filters/edit', [
            'attribute' => $attribute,
            'options' => $options,
            'assignedCategoryIds' => $assignedCategoryIds,
            'categories' => $categories,
            'title' => 'Edit Filter: ' . htmlspecialchars($attribute['name'])
        ]);
    }

    /**
     * Update filter attribute
     */
    public function update(int $id): void
    {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'multi_select';
        $isGlobal = isset($_POST['is_global']) ? (int)$_POST['is_global'] : 1;
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $categoryIds = $_POST['category_ids'] ?? [];

        if (empty($name)) {
            (new Response())->redirect(url("admin/filters/edit/{$id}?error=Name+is+required"));
            return;
        }

        $stmt = $this->db->prepare("UPDATE filter_attributes SET name = ?, type = ?, is_global = ?, is_active = ?, sort_order = ? WHERE id = ?");
        $stmt->execute([$name, $type, $isGlobal, $isActive, $sortOrder, $id]);

        // Sync category assignments
        $this->db->prepare("DELETE FROM filter_attribute_categories WHERE attribute_id = ?")->execute([$id]);
        if (!$isGlobal && !empty($categoryIds)) {
            $catStmt = $this->db->prepare("INSERT INTO filter_attribute_categories (attribute_id, category_id) VALUES (?, ?)");
            foreach ($categoryIds as $catId) {
                $catStmt->execute([$id, (int)$catId]);
            }
        }

        // Update existing option values if passed
        if (!empty($_POST['existing_options']) && is_array($_POST['existing_options'])) {
            $upOpt = $this->db->prepare("UPDATE filter_attribute_options SET value = ?, sort_order = ? WHERE id = ? AND attribute_id = ?");
            foreach ($_POST['existing_options'] as $optId => $optVal) {
                $optVal = trim($optVal);
                $optOrder = (int)($_POST['existing_option_sort'][$optId] ?? 0);
                if (!empty($optVal)) {
                    $upOpt->execute([$optVal, $optOrder, (int)$optId, $id]);
                }
            }
        }

        // Add new option if filled
        $newOpt = trim($_POST['new_option_value'] ?? '');
        if (!empty($newOpt)) {
            $optSlug = preg_replace('/[^a-z0-9]+/', '-', strtolower($newOpt));
            $addOpt = $this->db->prepare("INSERT INTO filter_attribute_options (attribute_id, value, slug, sort_order) VALUES (?, ?, ?, ?)");
            $addOpt->execute([$id, $newOpt, $optSlug, 99]);
        }

        (new Response())->redirect(url("admin/filters/edit/{$id}?success=Filter+attribute+updated+successfully"));
    }

    /**
     * Delete filter attribute
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM filter_attributes WHERE id = ?");
        $stmt->execute([$id]);

        (new Response())->redirect(url('admin/filters?success=Attribute+deleted+successfully'));
    }

    /**
     * AJAX toggle active/inactive status
     */
    public function toggleStatus(): void
    {
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        $status = (int)($_POST['is_active'] ?? 0);

        if ($id > 0) {
            $stmt = $this->db->prepare("UPDATE filter_attributes SET is_active = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true]);
            return;
        }

        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    }

    /**
     * AJAX Delete Option
     */
    public function deleteOption(): void
    {
        header('Content-Type: application/json');
        $optionId = (int)($_POST['option_id'] ?? 0);

        if ($optionId > 0) {
            $stmt = $this->db->prepare("DELETE FROM filter_attribute_options WHERE id = ?");
            $stmt->execute([$optionId]);
            echo json_encode(['success' => true]);
            return;
        }

        echo json_encode(['success' => false, 'error' => 'Invalid Option ID']);
    }
}
