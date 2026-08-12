<?php

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug): ?array;
    public function getProductWithDetails(int $id): ?array;
    public function getFeatured(int $limit = 12): array;
    public function getNewArrivals(int $limit = 12): array;
    public function getBestSellers(int $limit = 12): array;
    public function getByCategory(int $categoryId, int $limit = 24, int $offset = 0, string $sort = 'default'): array;
}
