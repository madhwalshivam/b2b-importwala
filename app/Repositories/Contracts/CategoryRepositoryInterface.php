<?php

namespace App\Repositories\Contracts;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getTree(): array;
    public function findBySlug(string $slug): ?array;
}
