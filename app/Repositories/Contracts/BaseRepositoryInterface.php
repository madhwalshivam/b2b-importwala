<?php

namespace App\Repositories\Contracts;

interface BaseRepositoryInterface
{
    public function findById(int|string $id): ?array;
    public function findAll(array $criteria = [], array $orderBy = [], int $limit = 50, int $offset = 0): array;
    public function count(array $criteria = []): int;
    public function create(array $data): int|string;
    public function update(int|string $id, array $data): bool;
    public function delete(int|string $id): bool;
}
