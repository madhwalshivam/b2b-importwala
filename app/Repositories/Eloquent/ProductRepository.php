<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Infrastructure\Cache\CacheManager;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    protected string $table = 'products';

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->getReadDb()->prepare("SELECT * FROM `products` WHERE `slug` = :slug AND `status` = 'active' LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $product = $stmt->fetch();
        if (!$product) {
            return null;
        }
        return $this->getProductWithDetails($product['id']);
    }

    public function getProductWithDetails(int $id): ?array
    {
        $cacheKey = "catalog:product:{$id}";
        return CacheManager::getInstance()->remember($cacheKey, 3600, function () use ($id) {
            $stmt = $this->getReadDb()->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM `products` p JOIN `categories` c ON p.category_id = c.id WHERE p.id = :id AND p.status = 'active' LIMIT 1");
            $stmt->execute(['id' => $id]);
            $product = $stmt->fetch();

            if (!$product) {
                return null;
            }

            // Fetch variations
            $varStmt = $this->getReadDb()->prepare("SELECT * FROM `product_variations` WHERE `product_id` = :pid AND `status` = 'active'");
            $varStmt->execute(['pid' => $id]);
            $product['variations'] = $varStmt->fetchAll();

            // Fetch tiered prices
            $tierStmt = $this->getReadDb()->prepare("SELECT * FROM `tiered_prices` WHERE `product_id` = :pid ORDER BY `min_qty` ASC");
            $tierStmt->execute(['pid' => $id]);
            $product['tiered_prices'] = $tierStmt->fetchAll();

            if (!empty($product['gallery_images'])) {
                $product['gallery_images'] = is_string($product['gallery_images']) ? json_decode($product['gallery_images'], true) : $product['gallery_images'];
            }

            return $product;
        });
    }

    public function getFeatured(int $limit = 12): array
    {
        $cacheKey = "catalog:featured:limit_{$limit}";
        return CacheManager::getInstance()->remember($cacheKey, 1800, function () use ($limit) {
            $stmt = $this->getReadDb()->prepare("SELECT * FROM `products` WHERE `status` = 'active' AND `is_featured` = 1 ORDER BY `sales_count` DESC LIMIT {$limit}");
            $stmt->execute();
            return $stmt->fetchAll();
        });
    }

    public function getNewArrivals(int $limit = 12): array
    {
        $cacheKey = "catalog:new_arrivals:limit_{$limit}";
        return CacheManager::getInstance()->remember($cacheKey, 1800, function () use ($limit) {
            $stmt = $this->getReadDb()->prepare("SELECT * FROM `products` WHERE `status` = 'active' AND (`is_new` = 1 OR `is_new_arrival` = 1) ORDER BY `id` DESC LIMIT {$limit}");
            $stmt->execute();
            return $stmt->fetchAll();
        });
    }

    public function getBestSellers(int $limit = 12): array
    {
        $cacheKey = "catalog:bestsellers:limit_{$limit}";
        return CacheManager::getInstance()->remember($cacheKey, 1800, function () use ($limit) {
            $stmt = $this->getReadDb()->prepare("SELECT * FROM `products` WHERE `status` = 'active' AND `is_best_seller` = 1 ORDER BY `sales_count` DESC, `id` DESC LIMIT {$limit}");
            $stmt->execute();
            return $stmt->fetchAll();
        });
    }

    public function getByCategory(int $categoryId, int $limit = 24, int $offset = 0, string $sort = 'default'): array
    {
        $orderClause = match ($sort) {
            'price_low_high' => 'ORDER BY `base_price` ASC',
            'price_high_low' => 'ORDER BY `base_price` DESC',
            'newest'         => 'ORDER BY `id` DESC',
            'popular'        => 'ORDER BY `sales_count` DESC',
            default          => 'ORDER BY `is_featured` DESC, `id` DESC',
        };

        $sql = "SELECT * FROM `products` WHERE `category_id` = :cid AND `status` = 'active' {$orderClause} LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->getReadDb()->prepare($sql);
        $stmt->execute(['cid' => $categoryId]);
        return $stmt->fetchAll();
    }
}
