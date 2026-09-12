<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class TopDealSection extends Model {
    protected string $table = 'top_deals_settings';

    /**
     * Fetch Top Deals section settings
     */
    public function getSettings(): array {
        $stmt = $this->db->query("SELECT * FROM top_deals_settings WHERE id = 1 LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$settings) {
            return [
                'id'         => 1,
                'title'      => 'Top Deals',
                'subtitle'   => 'Limited time offers — grab them before they sell out!',
                'slug'       => 'top-deals',
                'custom_url' => '',
                'status'     => 'active',
                'sort_order' => 1
            ];
        }
        return $settings;
    }

    /**
     * Update settings
     */
    public function updateSettings(array $data): bool {
        $title     = trim($data['title'] ?? 'Top Deals');
        $subtitle  = trim($data['subtitle'] ?? '');
        $slug      = !empty($data['slug']) ? slugify($data['slug']) : 'top-deals';
        $customUrl = trim($data['custom_url'] ?? '');
        $status    = in_array(strtolower((string)($data['status'] ?? '')), ['active', 'enabled', '1', 'true']) ? 'active' : 'inactive';
        $sortOrder = (int)($data['sort_order'] ?? 1);

        $stmt = $this->db->prepare("
            INSERT INTO top_deals_settings (id, title, subtitle, slug, custom_url, status, sort_order)
            VALUES (1, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                subtitle = VALUES(subtitle),
                slug = VALUES(slug),
                custom_url = VALUES(custom_url),
                status = VALUES(status),
                sort_order = VALUES(sort_order)
        ");
        return $stmt->execute([$title, $subtitle, $slug, $customUrl, $status, $sortOrder]);
    }

    /**
     * Fetch products assigned to Top Deals section
     */
    public function getProducts(?int $limit = null): array {
        $limitClause = ($limit !== null && $limit > 0) ? "LIMIT {$limit}" : "";
        $sql = "
            SELECT p.*, c.name as category_name, b.name as brand_name, tdp.display_order
            FROM top_deals_products tdp
            JOIN products p ON tdp.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.status = 'active'
            ORDER BY tdp.display_order ASC, tdp.id ASC
            {$limitClause}
        ";
        $stmt = $this->db->query($sql);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($products as &$p) {
            $p['main_image'] = asset($p['main_image'] ?? 'assets/images/placeholder.jpg');
        }
        unset($p);

        return $products;
    }

    /**
     * Save product IDs for Top Deals section in order
     */
    public function saveProducts(array $productIds): bool {
        $this->db->beginTransaction();
        try {
            $this->db->exec("DELETE FROM top_deals_products");
            if (!empty($productIds)) {
                $stmt = $this->db->prepare("INSERT INTO top_deals_products (product_id, display_order) VALUES (?, ?)");
                $order = 1;
                foreach ($productIds as $pid) {
                    $pid = (int)$pid;
                    if ($pid > 0) {
                        $stmt->execute([$pid, $order++]);
                    }
                }
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
