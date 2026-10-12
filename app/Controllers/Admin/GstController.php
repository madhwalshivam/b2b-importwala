<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Services\GstCalculator;
use App\Services\GstService;

class GstController extends Controller
{
    public function settings(): string
    {
        if (!Auth::hasPermission('gst.view') && !Auth::hasPermission('gst.edit')) {
            $this->redirect(url('admin/dashboard'));
        }
        return $this->render('admin/gst/settings', [
            'settings' => (new GstService())->settings(),
            'states' => GstCalculator::STATES,
            'canEdit' => Auth::hasPermission('gst.edit'),
        ]);
    }

    public function saveSettings(): void
    {
        if (!Auth::hasPermission('gst.edit')) {
            $this->setFlash('error', 'You do not have permission to edit GST settings.');
            $this->redirect(url('admin/gst/settings'));
            return;
        }
        try {
            (new GstService())->saveSettings($_POST, $this->adminId());
            if (function_exists('activity_log')) {
                activity_log('Update GST Settings', 'GST', 1, 'Updated seller GST settings');
            }
            $this->setFlash('success', 'GST settings saved.');
        } catch (\InvalidArgumentException $e) {
            $this->setFlash('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->setFlash('error', 'GST settings could not be saved.');
        }
        $this->redirect(url('admin/gst/settings'));
    }

    public function bulk(): string
    {
        if (!Auth::hasPermission('gst.view') && !Auth::hasPermission('categories.edit')) {
            $this->redirect(url('admin/dashboard'));
        }
        $db = Database::getInstance();
        $categories = $db->query('SELECT c.id, c.name, c.parent_id, p.name AS parent_name, c.hsn_code, c.gst_rate, c.is_gst_active FROM categories c LEFT JOIN categories p ON p.id = c.parent_id ORDER BY c.name ASC')->fetchAll() ?: [];
        $subcategories = [];
        try {
            $subcategories = $db->query('SELECT s.id, s.name, s.hsn_code, s.gst_rate, s.is_gst_active, c.name AS category_name FROM subcategories s LEFT JOIN categories c ON c.id = s.category_id ORDER BY s.name ASC')->fetchAll() ?: [];
        } catch (\Throwable $e) {
            $subcategories = [];
        }
        return $this->render('admin/gst/bulk', [
            'categories' => $categories,
            'subcategories' => $subcategories,
            'canEdit' => Auth::hasPermission('gst.edit') || Auth::hasPermission('categories.edit'),
        ]);
    }

    public function bulkUpdate(): void
    {
        if (!Auth::hasPermission('gst.edit') && !Auth::hasPermission('categories.edit')) {
            $this->setFlash('error', 'You do not have permission to edit GST rates.');
            $this->redirect(url('admin/gst/bulk'));
            return;
        }
        $ids = array_values(array_filter(array_map('intval', (array) ($_POST['category_ids'] ?? []))));
        $subIds = array_values(array_filter(array_map('intval', (array) ($_POST['subcategory_ids'] ?? []))));
        if (!$ids && !$subIds) {
            $this->setFlash('error', 'Select at least one category.');
            $this->redirect(url('admin/gst/bulk'));
            return;
        }

        $setRate = isset($_POST['apply_rate']) && $_POST['apply_rate'] === '1';
        $setHsn = isset($_POST['apply_hsn']) && $_POST['apply_hsn'] === '1';
        if (!$setRate && !$setHsn) {
            $this->setFlash('error', 'Choose whether to update the GST rate, the HSN code, or both.');
            $this->redirect(url('admin/gst/bulk'));
            return;
        }

        try {
            $rate = $setRate ? GstCalculator::parseRate($_POST['gst_rate'] ?? '', $_POST['gst_rate_custom'] ?? '') : null;
            $hsn = $setHsn ? GstCalculator::parseHsn($_POST['hsn_code'] ?? '') : null;
        } catch (\InvalidArgumentException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect(url('admin/gst/bulk'));
            return;
        }
        if ($setRate && $rate === null) {
            $this->setFlash('error', 'Enter the GST rate to apply.');
            $this->redirect(url('admin/gst/bulk'));
            return;
        }

        $service = new GstService();
        $db = Database::getInstance();
        $adminId = $this->adminId();
        $count = 0;
        $count += $this->applyBulk($db, $service, 'categories', 'category', $ids, $setRate, $rate, $setHsn, $hsn, $adminId);
        $count += $this->applyBulk($db, $service, 'subcategories', 'category', $subIds, $setRate, $rate, $setHsn, $hsn, $adminId);

        $this->setFlash('success', 'Updated GST details on ' . $count . ' row(s). New carts pick this up on the next page load.');
        $this->redirect(url('admin/gst/bulk'));
    }

    public function exportCsv(): void
    {
        if (!Auth::hasPermission('gst.view') && !Auth::hasPermission('categories.view')) {
            $this->redirect(url('admin/dashboard'));
            return;
        }
        $rows = Database::getInstance()->query('SELECT id, name, hsn_code, gst_rate FROM categories ORDER BY id ASC')->fetchAll() ?: [];
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="category-gst.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id', 'name', 'hsn_code', 'gst_rate']);
        foreach ($rows as $row) {
            fputcsv($out, [$row['id'], $row['name'], $row['hsn_code'], $row['gst_rate']]);
        }
        fclose($out);
        exit;
    }

    public function importCsv(): void
    {
        if (!Auth::hasPermission('gst.edit') && !Auth::hasPermission('categories.edit')) {
            $this->setFlash('error', 'You do not have permission to import GST rates.');
            $this->redirect(url('admin/gst/bulk'));
            return;
        }
        $file = $_FILES['csv'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Choose a CSV file to import.');
            $this->redirect(url('admin/gst/bulk'));
            return;
        }
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $this->setFlash('error', 'The CSV file could not be read.');
            $this->redirect(url('admin/gst/bulk'));
            return;
        }
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            $this->setFlash('error', 'The CSV file is empty.');
            $this->redirect(url('admin/gst/bulk'));
            return;
        }
        $header = array_map(static fn ($col) => strtolower(trim((string) $col)), $header);
        $index = array_flip($header);
        foreach (['id', 'hsn_code', 'gst_rate'] as $required) {
            if (!isset($index[$required])) {
                fclose($handle);
                $this->setFlash('error', 'CSV must include id, hsn_code and gst_rate columns.');
                $this->redirect(url('admin/gst/bulk'));
                return;
            }
        }

        $db = Database::getInstance();
        $service = new GstService();
        $find = $db->prepare('SELECT id, hsn_code, gst_rate FROM categories WHERE id = ?');
        $update = $db->prepare('UPDATE categories SET hsn_code = ?, gst_rate = ? WHERE id = ?');
        $updated = 0;
        $errors = [];
        $lineNo = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNo++;
            if (count(array_filter($row, static fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $id = (int) ($row[$index['id']] ?? 0);
            if ($id < 1) {
                $errors[] = "Row {$lineNo}: missing category id.";
                continue;
            }
            try {
                $hsn = GstCalculator::parseHsn($row[$index['hsn_code']] ?? '');
                $rateRaw = trim((string) ($row[$index['gst_rate']] ?? ''));
                $rate = $rateRaw === '' ? null : GstCalculator::parseRate($rateRaw);
            } catch (\InvalidArgumentException $e) {
                $errors[] = "Row {$lineNo}: " . $e->getMessage();
                continue;
            }
            $find->execute([$id]);
            $existing = $find->fetch();
            if (!$existing) {
                $errors[] = "Row {$lineNo}: category {$id} was not found.";
                continue;
            }
            $update->execute([$hsn, $rate, $id]);
            $service->audit('category', $id, $existing['hsn_code'] ?? null, $hsn, $existing['gst_rate'] ?? null, $rate, $this->adminId());
            $updated++;
        }
        fclose($handle);
        $message = "Imported GST details for {$updated} category(s).";
        if ($errors) {
            $message .= ' ' . count($errors) . ' row(s) skipped. ' . $errors[0];
        }
        $this->setFlash($errors && $updated === 0 ? 'error' : 'success', $message);
        $this->redirect(url('admin/gst/bulk'));
    }

    public function warnings(): string
    {
        if (!Auth::hasPermission('gst.view') && !Auth::hasPermission('categories.view')) {
            $this->redirect(url('admin/dashboard'));
        }
        return $this->render('admin/gst/warnings', (new GstService())->warnings());
    }

    private function applyBulk(\PDO $db, GstService $service, string $table, string $entityType, array $ids, bool $setRate, ?float $rate, bool $setHsn, ?string $hsn, ?int $adminId): int
    {
        if (!$ids) {
            return 0;
        }
        $find = $db->prepare("SELECT id, hsn_code, gst_rate FROM {$table} WHERE id = ?");
        $count = 0;
        foreach ($ids as $id) {
            $find->execute([$id]);
            $existing = $find->fetch();
            if (!$existing) {
                continue;
            }
            $newHsn = $setHsn ? $hsn : ($existing['hsn_code'] ?? null);
            $newRate = $setRate ? $rate : ($existing['gst_rate'] ?? null);
            $db->prepare("UPDATE {$table} SET hsn_code = ?, gst_rate = ? WHERE id = ?")->execute([
                $newHsn !== '' ? $newHsn : null,
                $newRate,
                $id,
            ]);
            $service->audit($entityType, $id, $existing['hsn_code'] ?? null, $newHsn, $existing['gst_rate'] ?? null, $newRate, $adminId);
            $count++;
        }
        return $count;
    }

    private function adminId(): ?int
    {
        $id = $_SESSION['admin_user_id'] ?? null;
        return $id !== null && $id !== '' ? (int) $id : null;
    }
}
