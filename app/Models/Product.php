<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model {
    protected string $table = 'products';

    public function getAllActiveProducts(): array {
        $stmt = $this->db->query("SELECT p.*, c.name as category_name, b.name as brand_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id WHERE p.status = 'active' ORDER BY p.name ASC");
        $items = $stmt->fetchAll();
        foreach ($items as &$item) {
            $item['main_image'] = asset($item['main_image'] ?? 'assets/images/placeholder.jpg');
        }
        return $items;
    }

    public function getFilteredProducts(array $filters = [], int $page = 1, int $perPage = 12): array {
        $whereConditions = ["p.status = 'active'"];
        $params = [];

        // Scooter Brand Filter
        if (!empty($filters['brand_ids']) && is_array($filters['brand_ids'])) {
            $inClause = implode(',', array_fill(0, count($filters['brand_ids']), '?'));
            $whereConditions[] = "(p.brand_id IN ({$inClause}) OR p.id IN (SELECT product_id FROM product_brands WHERE brand_id IN ({$inClause})))";
            foreach ($filters['brand_ids'] as $bid) $params[] = (int)$bid;
            foreach ($filters['brand_ids'] as $bid) $params[] = (int)$bid;
        } elseif (!empty($filters['brand_id'])) {
            $whereConditions[] = "(p.brand_id = ? OR p.id IN (SELECT product_id FROM product_brands WHERE brand_id = ?))";
            $params[] = $filters['brand_id'];
            $params[] = $filters['brand_id'];
        } elseif (!empty($filters['brand_slug'])) {
            $whereConditions[] = "b.slug = ?";
            $params[] = $filters['brand_slug'];
        }

        // Scooter Model Filter (Crucial compatibility check!)
        if (!empty($filters['model_id'])) {
            $whereConditions[] = "p.id IN (SELECT product_id FROM product_scooter_compatibilities WHERE scooter_model_id = ?)";
            $params[] = $filters['model_id'];
        } elseif (!empty($filters['model_slug'])) {
            $whereConditions[] = "p.id IN (SELECT psc.product_id FROM product_scooter_compatibilities psc JOIN scooter_models sm ON psc.scooter_model_id = sm.id WHERE sm.slug = ?)";
            $params[] = $filters['model_slug'];
        }

        // Category Filter
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $inClause = implode(',', array_fill(0, count($filters['category_ids']), '?'));
            $whereConditions[] = "(p.category_id IN ({$inClause}) OR p.id IN (SELECT product_id FROM product_categories WHERE category_id IN ({$inClause})))";
            foreach ($filters['category_ids'] as $cid) $params[] = (int)$cid;
            foreach ($filters['category_ids'] as $cid) $params[] = (int)$cid;
        } elseif (!empty($filters['category_id'])) {
            $whereConditions[] = "(p.category_id = ? OR p.id IN (SELECT product_id FROM product_categories WHERE category_id = ?))";
            $params[] = $filters['category_id'];
            $params[] = $filters['category_id'];
        } elseif (!empty($filters['category_slug'])) {
            $whereConditions[] = "c.slug = ?";
            $params[] = $filters['category_slug'];
        }

        // Search Query
        if (!empty($filters['search'])) {
            $whereConditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ? OR p.tags LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Price Filter
        if (!empty($filters['min_price'])) {
            $whereConditions[] = "COALESCE(p.sale_price, p.price) >= ?";
            $params[] = (float)$filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $whereConditions[] = "COALESCE(p.sale_price, p.price) <= ?";
            $params[] = (float)$filters['max_price'];
        }

        // Tag Flags
        if (!empty($filters['featured'])) {
            $whereConditions[] = "p.is_featured = 1";
        }
        if (!empty($filters['best_seller'])) {
            $whereConditions[] = "p.is_best_seller = 1";
        }
        if (!empty($filters['new_arrival'])) {
            $whereConditions[] = "p.is_new_arrival = 1";
        }

        $whereSql = implode(' AND ', $whereConditions);

        // Sorting
        $orderBy = 'p.id DESC';
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_low':
                    $orderBy = 'COALESCE(p.sale_price, p.price) ASC';
                    break;
                case 'price_high':
                    $orderBy = 'COALESCE(p.sale_price, p.price) DESC';
                    break;
                case 'name_asc':
                    $orderBy = 'p.name ASC';
                    break;
                case 'oldest':
                    $orderBy = 'p.id ASC';
                    break;
                case 'popular':
                    $orderBy = 'p.views_count DESC';
                    break;
                case 'best_seller':
                    $orderBy = 'p.is_best_seller DESC, p.views_count DESC';
                    break;
                case 'offers':
                    $orderBy = '(p.price - COALESCE(p.sale_price, p.price)) DESC';
                    break;
            }
        }

        // Count Total
        $countSql = "
            SELECT COUNT(DISTINCT p.id) 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE {$whereSql}
        ";
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        $offset = ($page - 1) * $perPage;

        // Fetch Records
        $dataSql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name, b.slug as brand_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE {$whereSql}
            ORDER BY {$orderBy}
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $stmtData = $this->db->prepare($dataSql);
        $stmtData->execute($params);
        $items = $stmtData->fetchAll();

        foreach ($items as &$item) {
            $item['main_image'] = asset($item['main_image'] ?? 'assets/images/placeholder.jpg');
        }
        unset($item);

        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)ceil($total / $perPage)
        ];
    }

    public function getVehicleCompatibilities(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_vehicle_compatibility WHERE product_id = ? ORDER BY vehicle_name ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getIncludedItems(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_included_items WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getVehicleInstallationImages(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_vehicle_images WHERE product_id = ? ORDER BY vehicle_name ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getBadges(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_badges WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getComparisonData(array $productIds): array {
        if (empty($productIds)) return [];
        $cleanIds = array_slice(array_map('intval', $productIds), 0, 4);
        if (empty($cleanIds)) return [];
        $inClause = implode(',', array_fill(0, count($cleanIds), '?'));

        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, b.name as brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.id IN ({$inClause}) AND p.status = 'active'
        ");
        $stmt->execute($cleanIds);
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            $pid = (int)$p['id'];
            $p['main_image'] = asset(ltrim($p['main_image'] ?? '/assets/images/placeholder.jpg', '/'));
            $p['specifications'] = $this->getSpecifications($pid);
            
            $effectivePrice = $p['sale_price'] ?: $p['price'];
            $p['computed_effective_price'] = (float)$effectivePrice;
            $p['price'] = (float)$p['price'];
            $p['sale_price'] = $p['sale_price'] ? (float)$p['sale_price'] : null;
        }

        return $products;
    }

    public function syncProductCategories(int $productId, array $categoryIds): void {
        $stmtDel = $this->db->prepare("DELETE FROM product_categories WHERE product_id = ?");
        $stmtDel->execute([$productId]);
        if (empty($categoryIds)) return;

        $stmtIns = $this->db->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
        foreach ($categoryIds as $catId) {
            $stmtIns->execute([$productId, (int)$catId]);
        }
    }

    public function syncProductBrands(int $productId, array $brandIds): void {
        $stmtDel = $this->db->prepare("DELETE FROM product_brands WHERE product_id = ?");
        $stmtDel->execute([$productId]);
        if (empty($brandIds)) return;

        $stmtIns = $this->db->prepare("INSERT INTO product_brands (product_id, brand_id) VALUES (?, ?)");
        foreach ($brandIds as $brandId) {
            $stmtIns->execute([$productId, (int)$brandId]);
        }
    }

    public function getProductCategoryIds(int $productId): array {
        $stmt = $this->db->prepare("SELECT category_id FROM product_categories WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
    }

    public function getProductBrandIds(int $productId): array {
        $stmt = $this->db->prepare("SELECT brand_id FROM product_brands WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
    }

    public function getCompatibleScooters(int $productId): array {
        $stmt = $this->db->prepare("
            SELECT sm.*, b.name as brand_name, b.logo as brand_logo
            FROM product_scooter_compatibilities psc
            JOIN scooter_models sm ON psc.scooter_model_id = sm.id
            JOIN brands b ON psc.brand_id = b.id
            WHERE psc.product_id = ?
            ORDER BY b.sort_order ASC, sm.sort_order ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function syncCompatibleScooters(int $productId, array $scooterModelIds): void {
        // Delete existing
        $stmtDel = $this->db->prepare("DELETE FROM product_scooter_compatibilities WHERE product_id = ?");
        $stmtDel->execute([$productId]);

        if (empty($scooterModelIds)) return;

        $stmtModel = $this->db->prepare("SELECT id, brand_id FROM scooter_models WHERE id = ?");
        $stmtInsert = $this->db->prepare("INSERT INTO product_scooter_compatibilities (product_id, brand_id, scooter_model_id) VALUES (?, ?, ?)");

        foreach ($scooterModelIds as $modelId) {
            $stmtModel->execute([(int)$modelId]);
            $sm = $stmtModel->fetch();
            if ($sm) {
                $stmtInsert->execute([$productId, $sm['brand_id'], $sm['id']]);
            }
        }
    }

    public function getGalleryImages(int $productId): array {
        $stmt = $this->db->prepare(
            "SELECT *, COALESCE(image_url, image_path) as display_url FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC"
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getSpecifications(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getFaqs(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_faqs WHERE product_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function delete(mixed $id): bool {
        // Clean up linked records to prevent foreign key errors
        $this->db->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM product_categories WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM product_brands WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM product_scooter_compatibilities WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM homepage_compare_products WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM homepage_section_products WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM product_badges WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM product_included_items WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM product_vehicle_compatibility WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM product_vehicle_images WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM cart_items WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM coupon_products WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM reviews WHERE product_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM product_specifications WHERE product_id = ?")->execute([$id]);
        try {
            $this->db->prepare("DELETE FROM product_related WHERE product_id = ? OR related_product_id = ?")->execute([$id, $id]);
        } catch (\Throwable $e) {}

        return parent::delete($id);
    }

    /**
     * Fetch related or frequently bought products for a given product
     */
    public function getRelatedProducts(int $productId, string $type = 'related', int $limit = 20): array {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, pr.sort_order, c.name as category_name
                FROM product_related pr
                JOIN products p ON pr.related_product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE pr.product_id = ? AND pr.relation_type = ? AND p.status = 'active'
                ORDER BY pr.sort_order ASC, pr.id ASC
                LIMIT {$limit}
            ");
            $stmt->execute([$productId, $type]);
            $items = $stmt->fetchAll();
            foreach ($items as &$item) {
                $item['main_image'] = asset($item['main_image'] ?? 'assets/images/placeholder.jpg');
            }
            return $items;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Fetch array of related product IDs for editing
     */
    public function getRelatedProductIds(int $productId, string $type = 'related'): array {
        try {
            $stmt = $this->db->prepare("
                SELECT related_product_id
                FROM product_related
                WHERE product_id = ? AND relation_type = ?
                ORDER BY sort_order ASC
            ");
            $stmt->execute([$productId, $type]);
            return array_column($stmt->fetchAll(), 'related_product_id');
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Save/Sync related or frequently bought products for a product
     */
    public function saveRelatedProducts(int $productId, array $relatedProductIds, string $type = 'frequently_bought'): void {
        // Find the previous cluster for this product
        $stmtPrev = $this->db->prepare("SELECT related_product_id FROM product_related WHERE product_id = ? AND relation_type = ?");
        $stmtPrev->execute([$productId, $type]);
        $prevIds = $stmtPrev->fetchAll(\PDO::FETCH_COLUMN);

        $prevCluster = array_values(array_unique(array_merge([$productId], $prevIds)));
        
        // Find the new cluster
        $newIds = array_map('intval', $relatedProductIds);
        $newIds = array_filter($newIds, fn($id) => $id > 0);
        $newCluster = array_values(array_unique(array_merge([$productId], $newIds)));

        // Find removed items (items in previous cluster that are not in the new cluster)
        $removedItems = array_diff($prevCluster, $newCluster);

        // Delete relationships involving any removed items and any item from the prevCluster
        if (!empty($removedItems)) {
            $placeholdersRemoved = str_repeat('?,', count($removedItems) - 1) . '?';
            $placeholdersPrev = str_repeat('?,', count($prevCluster) - 1) . '?';
            
            $sqlDeleteRemoved = "DELETE FROM product_related 
                                 WHERE relation_type = ? 
                                 AND ((product_id IN ($placeholdersRemoved) AND related_product_id IN ($placeholdersPrev))
                                   OR (product_id IN ($placeholdersPrev) AND related_product_id IN ($placeholdersRemoved)))";
            
            $params = array_merge([$type], $removedItems, $prevCluster, $prevCluster, $removedItems);
            $stmtDelRemoved = $this->db->prepare($sqlDeleteRemoved);
            $stmtDelRemoved->execute($params);
        }

        // Now clear and rebuild strictly among the new cluster
        if (!empty($newCluster)) {
            $placeholdersNew = str_repeat('?,', count($newCluster) - 1) . '?';
            $sqlDeleteNew = "DELETE FROM product_related 
                             WHERE relation_type = ? 
                             AND product_id IN ($placeholdersNew) AND related_product_id IN ($placeholdersNew)";
            $paramsNew = array_merge([$type], $newCluster, $newCluster);
            $stmtDelNew = $this->db->prepare($sqlDeleteNew);
            $stmtDelNew->execute($paramsNew);

            // Rebuild cartesian product for new cluster
            $stmtIns = $this->db->prepare("INSERT IGNORE INTO product_related (product_id, related_product_id, relation_type, sort_order) VALUES (?, ?, ?, ?)");
            foreach ($newCluster as $sourceId) {
                $sort = 1;
                foreach ($newCluster as $targetId) {
                    if ($sourceId !== $targetId) {
                        $stmtIns->execute([$sourceId, $targetId, $type, $sort++]);
                    }
                }
            }
        }
    }

    /**
     * Save variation types / variants for a product.
     */
    public function saveVariationTypes(int $productId, array $types): void {
        if (empty($types)) return;

        $variantModel = new ProductVariant();

        foreach ($types as $index => $typeData) {
            if (!is_array($typeData)) continue;

            $variantData = [
                'product_id'      => $productId,
                'variant_code'    => $typeData['variant_code'] ?? $typeData['sku'] ?? null,
                'image_url'       => $typeData['image_url'] ?? $typeData['image'] ?? null,
                'attribute_label' => $typeData['attribute_label'] ?? $typeData['name'] ?? $typeData['label'] ?? 'Variant',
                'attribute_value' => $typeData['attribute_value'] ?? $typeData['value'] ?? 'Default',
                'weight'          => $typeData['weight'] ?? null,
                'dimensions'      => $typeData['dimensions'] ?? null,
                'stock_quantity'  => (int)($typeData['stock_quantity'] ?? $typeData['stock'] ?? 0),
                'wholesale_price' => (float)($typeData['wholesale_price'] ?? $typeData['price'] ?? 0),
                'one_piece_price' => (float)($typeData['one_piece_price'] ?? $typeData['price'] ?? 0),
                'sort_order'      => (int)($typeData['sort_order'] ?? $index),
                'is_active'       => isset($typeData['is_active']) ? (int)$typeData['is_active'] : 1,
            ];

            $variantModel->createVariant($variantData);
        }
    }

}


