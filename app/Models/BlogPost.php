<?php
namespace App\Models;

use App\Core\Model;

class BlogPost extends Model {
    protected string $table = 'blog_posts';
    protected string $primaryKey = 'id';

    /**
     * Converts a string into a clean, URL-friendly slug
     */
    public static function slugify(string $text): string {
        $slug = mb_strtolower($text, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug);
        $slug = trim($slug, '-');
        return $slug ?: 'n-a';
    }

    /**
     * Generates a unique URL slug for blog posts.
     */
    public function generateUniqueSlug(string $title, ?int $ignoreId = null, ?string $customSlug = null): string {
        $baseSlug = !empty($customSlug) ? self::slugify($customSlug) : self::slugify($title);
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $sql = "SELECT id FROM {$this->table} WHERE slug = ?";
            $params = [$slug];

            if ($ignoreId !== null) {
                $sql .= " AND id != ?";
                $params[] = $ignoreId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if (!$stmt->fetch()) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }

    /**
     * Get all blog categories
     */
    public function getAllCategories(): array {
        $stmt = $this->db->query("SELECT * FROM blog_categories ORDER BY name ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get recent published posts for homepage / widgets
     */
    public function getRecentPublished(int $limit = 4, array|int|null $excludeId = null): array {
        $where = "((bp.status = 'published' AND (bp.published_at IS NULL OR bp.published_at <= NOW())) OR (bp.status = 'scheduled' AND bp.published_at <= NOW()))";
        $params = [];

        if (!empty($excludeId)) {
            if (is_array($excludeId)) {
                $cleanIds = implode(',', array_map('intval', array_filter($excludeId)));
                if (!empty($cleanIds)) {
                    $where .= " AND bp.id NOT IN ({$cleanIds})";
                }
            } else {
                $where .= " AND bp.id != ?";
                $params[] = (int)$excludeId;
            }
        }

        $sql = "SELECT bp.*, c.name as category_name, c.slug as category_slug 
                FROM {$this->table} bp 
                LEFT JOIN blog_categories c ON bp.category_id = c.id 
                WHERE {$where} 
                ORDER BY bp.published_at DESC, bp.id DESC 
                LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Find a published post by slug
     */
    public function findPublishedBySlug(string $slug): ?array {
        $sql = "SELECT bp.*, c.name as category_name, c.slug as category_slug 
                FROM {$this->table} bp 
                LEFT JOIN blog_categories c ON bp.category_id = c.id 
                WHERE bp.slug = ? 
                AND ((bp.status = 'published' AND (bp.published_at IS NULL OR bp.published_at <= NOW())) OR (bp.status = 'scheduled' AND bp.published_at <= NOW())) 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get paginated published posts for storefront with category & search filter
     */
    public function getPublishedPostsFiltered(int $page = 1, int $perPage = 9, ?string $categorySlug = null, ?string $search = null): array {
        $where = "((bp.status = 'published' AND (bp.published_at IS NULL OR bp.published_at <= NOW())) OR (bp.status = 'scheduled' AND bp.published_at <= NOW()))";
        $params = [];

        if (!empty($categorySlug)) {
            $where .= " AND c.slug = ?";
            $params[] = $categorySlug;
        }

        if (!empty($search)) {
            $where .= " AND (bp.title LIKE ? OR bp.content LIKE ? OR bp.excerpt LIKE ? OR bp.author_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        // Count total matching
        $countSql = "SELECT COUNT(*) FROM {$this->table} bp LEFT JOIN blog_categories c ON bp.category_id = c.id WHERE {$where}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $lastPage = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT bp.*, c.name as category_name, c.slug as category_slug 
                FROM {$this->table} bp 
                LEFT JOIN blog_categories c ON bp.category_id = c.id 
                WHERE {$where} 
                ORDER BY bp.published_at DESC, bp.id DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'items' => $items,
            'current_page' => $page,
            'last_page' => $lastPage,
            'total' => $total,
            'per_page' => $perPage
        ];
    }

    /**
     * Fetch relevant related published articles for single blog page
     */
    public function getRelatedPosts(array $currentPost, int $limit = 3): array {
        $currentId = (int)$currentPost['id'];
        $categoryId = !empty($currentPost['category_id']) ? (int)$currentPost['category_id'] : null;

        $related = [];
        $foundIds = [$currentId];

        // 1. First try same category
        if ($categoryId) {
            $sql = "SELECT bp.*, c.name as category_name, c.slug as category_slug 
                    FROM {$this->table} bp 
                    LEFT JOIN blog_categories c ON bp.category_id = c.id 
                    WHERE bp.id != ? AND bp.category_id = ? 
                    AND ((bp.status = 'published' AND (bp.published_at IS NULL OR bp.published_at <= NOW())) OR (bp.status = 'scheduled' AND bp.published_at <= NOW())) 
                    ORDER BY bp.published_at DESC, bp.id DESC 
                    LIMIT {$limit}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$currentId, $categoryId]);
            $related = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($related as $r) {
                $foundIds[] = (int)$r['id'];
            }
        }

        // 2. Fill remaining slots with latest published posts
        $remaining = $limit - count($related);
        if ($remaining > 0) {
            $inClause = implode(',', $foundIds);
            $sql = "SELECT bp.*, c.name as category_name, c.slug as category_slug 
                    FROM {$this->table} bp 
                    LEFT JOIN blog_categories c ON bp.category_id = c.id 
                    WHERE bp.id NOT IN ({$inClause}) 
                    AND ((bp.status = 'published' AND (bp.published_at IS NULL OR bp.published_at <= NOW())) OR (bp.status = 'scheduled' AND bp.published_at <= NOW())) 
                    ORDER BY bp.published_at DESC, bp.id DESC 
                    LIMIT {$remaining}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $fallback = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $related = array_merge($related, $fallback);
        }

        return $related;
    }

    /**
     * Increment post view count
     */
    public function incrementViews(int $id): void {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
}
