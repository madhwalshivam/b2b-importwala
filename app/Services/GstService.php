<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Loads GST settings and resolves each product's rate, then delegates math to GstCalculator.
 */
class GstService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: Database::getInstance();
    }

    public function settings(): array
    {
        $defaults = [
            'seller_state' => 'Delhi',
            'seller_gstin' => '',
            'default_gst_rate' => 18.0,
            'price_mode' => 'exclusive',
        ];
        if (!$this->tableExists('gst_settings')) {
            return $defaults;
        }
        $row = $this->db->query('SELECT * FROM gst_settings WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return $defaults;
        }
        $mode = ($row['price_mode'] ?? '') === 'inclusive' ? 'inclusive' : 'exclusive';
        return [
            'seller_state' => GstCalculator::normalizeState($row['seller_state'] ?? 'Delhi'),
            'seller_gstin' => (string) ($row['seller_gstin'] ?? ''),
            'default_gst_rate' => GstCalculator::roundMoney((float) ($row['default_gst_rate'] ?? 18)),
            'price_mode' => $mode,
        ];
    }

    public function saveSettings(array $input, ?int $adminId): void
    {
        $state = GstCalculator::normalizeState($input['seller_state'] ?? '');
        if ($state === '' || !in_array($state, GstCalculator::STATES, true)) {
            throw new \InvalidArgumentException('Choose a seller state from the list.');
        }
        $gstin = GstCalculator::parseGstin($input['seller_gstin'] ?? '');
        $rate = GstCalculator::parseRate($input['default_gst_rate'] ?? '', $input['default_gst_rate_custom'] ?? null);
        if ($rate === null) {
            throw new \InvalidArgumentException('Default GST rate is required.');
        }
        $mode = ($input['price_mode'] ?? '') === 'inclusive' ? 'inclusive' : 'exclusive';

        $current = $this->settings();
        $this->db->prepare(
            'INSERT INTO gst_settings (id, seller_state, seller_gstin, default_gst_rate, price_mode)
             VALUES (1, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE seller_state = VALUES(seller_state), seller_gstin = VALUES(seller_gstin),
               default_gst_rate = VALUES(default_gst_rate), price_mode = VALUES(price_mode)'
        )->execute([$state, $gstin, $rate, $mode]);

        $this->audit(
            'settings',
            1,
            $current['seller_gstin'] !== '' ? null : null,
            null,
            $current['default_gst_rate'],
            $rate,
            $adminId,
            $current['seller_state'] . '|' . $current['seller_gstin'] . '|' . $current['price_mode'],
            $state . '|' . (string) $gstin . '|' . $mode
        );
    }

    /**
     * @param array<int, array{product_id?: int, unit_price?: float, quantity?: int, name?: string, sku?: string}> $items
     */
    public function quote(array $items, ?string $shippingState): array
    {
        $settings = $this->settings();
        $resolved = $this->resolveMany(array_map(static fn ($item) => (int) ($item['product_id'] ?? 0), $items));
        $lines = [];
        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $meta = $resolved[$productId] ?? [
                'gst_rate' => $settings['default_gst_rate'],
                'hsn_code' => '',
                'rate_source' => 'default',
            ];
            $lines[] = [
                'product_id' => $productId,
                'name' => (string) ($item['name'] ?? ''),
                'sku' => (string) ($item['sku'] ?? ''),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'quantity' => (int) ($item['quantity'] ?? 0),
                'rate' => (float) $meta['gst_rate'],
                'hsn_code' => (string) $meta['hsn_code'],
                'rate_source' => $meta['rate_source'],
            ];
        }
        return GstCalculator::summarize($lines, $settings['price_mode'], $shippingState, $settings['seller_state']);
    }

    /**
     * Inherited HSN and rate for the product form helper text.
     *
     * @return array{hsn_code: string, gst_rate: float, source: string}
     */
    public function inheritanceForProduct(array $product): array
    {
        $copy = $product;
        $copy['gst_rate'] = null;
        $copy['hsn_code'] = null;
        $resolved = $this->resolveProductRow($copy, $this->categoryMap(), $this->subcategoryMap(), $this->settings());
        return [
            'hsn_code' => $resolved['hsn_code'],
            'gst_rate' => $resolved['gst_rate'],
            'source' => $resolved['rate_source'],
        ];
    }

    /**
     * @param array<int, int> $productIds
     * @return array<int, array{gst_rate: float, hsn_code: string, rate_source: string}>
     */
    public function resolveMany(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
        if (!$productIds) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $gstCol = $this->columnExists('products', 'gst_rate') ? 'p.gst_rate' : 'NULL AS gst_rate';
        $stmt = $this->db->prepare("SELECT p.id, p.hsn_code, {$gstCol}, p.category_id, p.subcategory_id FROM products p WHERE p.id IN ($placeholders)");
        $stmt->execute($productIds);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $categories = $this->categoryMap();
        $subcategories = $this->subcategoryMap();
        $settings = $this->settings();
        $out = [];
        foreach ($products as $product) {
            $out[(int) $product['id']] = $this->resolveProductRow($product, $categories, $subcategories, $settings);
        }
        return $out;
    }

    public function warnings(): array
    {
        $categories = [];
        $products = [];
        if ($this->columnExists('categories', 'hsn_code')) {
            $categories = $this->db->query(
                "SELECT id, name, parent_id, hsn_code FROM categories
                 WHERE hsn_code IS NULL OR hsn_code = ''
                 ORDER BY name ASC"
            )->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        if ($this->columnExists('products', 'hsn_code')) {
            $rows = $this->db->query(
                "SELECT id, name, sku, hsn_code, category_id, subcategory_id" .
                ($this->columnExists('products', 'gst_rate') ? ', gst_rate' : ', NULL AS gst_rate') .
                " FROM products ORDER BY name ASC"
            )->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $categoriesMap = $this->categoryMap();
            $subcategories = $this->subcategoryMap();
            $settings = $this->settings();
            foreach ($rows as $row) {
                $resolved = $this->resolveProductRow($row, $categoriesMap, $subcategories, $settings);
                if ($resolved['hsn_code'] === '') {
                    $products[] = [
                        'id' => (int) $row['id'],
                        'name' => $row['name'],
                        'sku' => $row['sku'],
                    ];
                }
            }
        }
        return ['categories' => $categories, 'products' => $products];
    }

    public function audit(
        string $entityType,
        int $entityId,
        ?string $oldHsn,
        ?string $newHsn,
        $oldRate,
        $newRate,
        ?int $adminId,
        ?string $oldNote = null,
        ?string $newNote = null
    ): void {
        if (!$this->tableExists('gst_rate_audit_log')) {
            return;
        }
        $oldHsn = $oldHsn !== null && $oldHsn !== '' ? $oldHsn : null;
        $newHsn = $newHsn !== null && $newHsn !== '' ? $newHsn : null;
        $oldRateNorm = $oldRate === null || $oldRate === '' ? null : GstCalculator::roundMoney((float) $oldRate);
        $newRateNorm = $newRate === null || $newRate === '' ? null : GstCalculator::roundMoney((float) $newRate);
        $noteChanged = ($oldNote ?? '') !== ($newNote ?? '');
        if ($oldHsn === $newHsn && $oldRateNorm === $newRateNorm && !$noteChanged) {
            return;
        }
        $this->db->prepare(
            'INSERT INTO gst_rate_audit_log (entity_type, entity_id, old_hsn, new_hsn, old_rate, new_rate, changed_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $entityType,
            $entityId,
            $oldNote !== null ? mb_substr($oldNote, 0, 30) : $oldHsn,
            $newNote !== null ? mb_substr($newNote, 0, 30) : $newHsn,
            $oldRateNorm,
            $newRateNorm,
            $adminId,
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $categories
     * @param array<int, array<string, mixed>> $subcategories
     * @return array{gst_rate: float, hsn_code: string, rate_source: string}
     */
    private function resolveProductRow(array $product, array $categories, array $subcategories, array $settings): array
    {
        $rate = null;
        $rateSource = 'default';
        if (array_key_exists('gst_rate', $product) && $product['gst_rate'] !== null && $product['gst_rate'] !== '') {
            $rate = (float) $product['gst_rate'];
            $rateSource = 'product';
        }

        $hsn = trim((string) ($product['hsn_code'] ?? ''));
        $hsnSource = $hsn !== '' ? 'product' : '';

        $subId = (int) ($product['subcategory_id'] ?? 0);
        if ($subId && isset($subcategories[$subId])) {
            $sub = $subcategories[$subId];
            if ($rate === null && $this->rowRateActive($sub)) {
                $rate = (float) $sub['gst_rate'];
                $rateSource = 'subcategory';
            }
            if ($hsn === '') {
                $subHsn = trim((string) ($sub['hsn_code'] ?? ''));
                if ($subHsn !== '') {
                    $hsn = $subHsn;
                    $hsnSource = 'subcategory';
                }
            }
        }

        $categoryId = (int) ($product['category_id'] ?? 0);
        $guard = 0;
        while ($categoryId && isset($categories[$categoryId]) && $guard < 6) {
            $cat = $categories[$categoryId];
            if ($rate === null && $this->rowRateActive($cat)) {
                $rate = (float) $cat['gst_rate'];
                $rateSource = $guard === 0 ? 'category' : 'parent';
            }
            if ($hsn === '') {
                $catHsn = trim((string) ($cat['hsn_code'] ?? ''));
                if ($catHsn !== '') {
                    $hsn = $catHsn;
                }
            }
            if ($rate !== null && $hsn !== '') {
                break;
            }
            $categoryId = (int) ($cat['parent_id'] ?? 0);
            $guard++;
        }

        if ($rate === null) {
            $rate = (float) $settings['default_gst_rate'];
            $rateSource = 'default';
        }

        return [
            'gst_rate' => GstCalculator::roundMoney($rate),
            'hsn_code' => $hsn,
            'rate_source' => $rateSource,
            'hsn_source' => $hsnSource,
        ];
    }

    private function rowRateActive(array $row): bool
    {
        if (!array_key_exists('gst_rate', $row) || $row['gst_rate'] === null || $row['gst_rate'] === '') {
            return false;
        }
        if (array_key_exists('is_gst_active', $row) && (int) $row['is_gst_active'] !== 1) {
            return false;
        }
        return true;
    }

    private function categoryMap(): array
    {
        if (!$this->columnExists('categories', 'gst_rate')) {
            $rows = $this->db->query('SELECT id, parent_id, NULL AS hsn_code, NULL AS gst_rate, 1 AS is_gst_active FROM categories')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } else {
            $rows = $this->db->query('SELECT id, parent_id, hsn_code, gst_rate, is_gst_active FROM categories')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id']] = $row;
        }
        return $map;
    }

    private function subcategoryMap(): array
    {
        if (!$this->tableExists('subcategories') || !$this->columnExists('subcategories', 'gst_rate')) {
            return [];
        }
        $rows = $this->db->query('SELECT id, category_id, hsn_code, gst_rate, is_gst_active FROM subcategories')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id']] = $row;
        }
        return $map;
    }

    private function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
        $stmt->execute([$table, $column]);
        $cache[$key] = ((int) $stmt->fetchColumn()) > 0;
        return $cache[$key];
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (array_key_exists($table, $cache)) {
            return $cache[$table];
        }
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        $stmt->execute([$table]);
        $cache[$table] = ((int) $stmt->fetchColumn()) > 0;
        return $cache[$table];
    }
}
