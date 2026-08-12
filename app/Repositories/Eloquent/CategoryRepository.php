<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Infrastructure\Cache\CacheManager;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    protected string $table = 'categories';

    public function getTree(): array
    {
        $cacheKey = 'catalog:categories:tree';
        return CacheManager::getInstance()->remember($cacheKey, 86400, function () {
            $categories = $this->findAll(['is_active' => 1], ['sort_order' => 'ASC']);
            return $this->buildTree($categories);
        });
    }

    public function findBySlug(string $slug): ?array
    {
        $cacheKey = "catalog:category:slug:{$slug}";
        return CacheManager::getInstance()->remember($cacheKey, 3600, function () use ($slug) {
            $stmt = $this->getReadDb()->prepare("SELECT * FROM `categories` WHERE `slug` = :slug AND `is_active` = 1 LIMIT 1");
            $stmt->execute(['slug' => $slug]);
            $res = $stmt->fetch();
            return $res ?: null;
        });
    }

    private function buildTree(array $elements, ?int $parentId = null): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
}
