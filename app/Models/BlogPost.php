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
        // Convert to lowercase
        $slug = mb_strtolower($text, 'UTF-8');
        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug);
        // Trim leading and trailing hyphens
        $slug = trim($slug, '-');
        return $slug ?: 'n-a';
    }

    /**
     * Generates a unique URL slug for blog posts.
     * Auto-appends -1, -2, etc. if duplicate exists.
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
     * Get paginated published posts for storefront with optional ID exclusion
     */
    public function getPublishedPosts(int $page = 1, int $perPage = 9, array $excludeIds = []): array {
        $where = "status = 'published'";
        $params = [];

        if (!empty($excludeIds)) {
            $cleanIds = implode(',', array_map('intval', array_filter($excludeIds)));
            if (!empty($cleanIds)) {
                $where .= " AND id NOT IN ({$cleanIds})";
            }
        }

        return $this->paginate(
            $page,
            $perPage,
            $where,
            $params,
            "published_at DESC, id DESC"
        );
    }

    /**
     * Find a published post by slug
     */
    public function findPublishedBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = ? AND status = 'published' LIMIT 1");
        $stmt->execute([$slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Fetch relevant related published articles for single blog page
     */
    public function getRelatedPosts(array $currentPost, int $limit = 3): array {
        $currentId = (int)$currentPost['id'];
        $focusKeyword = trim($currentPost['focus_keyword'] ?? '');
        $title = trim($currentPost['title'] ?? '');

        // Extract key search terms from title / focus keyword
        $searchTerms = [];
        if (!empty($focusKeyword)) {
            $searchTerms[] = $focusKeyword;
        }
        
        $words = array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9\s]/', '', $title)), fn($w) => strlen($w) > 3);
        $searchTerms = array_merge($searchTerms, array_slice($words, 0, 3));

        $related = [];
        $foundIds = [$currentId];

        if (!empty($searchTerms)) {
            $likeConditions = [];
            $params = [];
            foreach ($searchTerms as $term) {
                $likeConditions[] = "(title LIKE ? OR content LIKE ? OR focus_keyword LIKE ?)";
                $params[] = "%{$term}%";
                $params[] = "%{$term}%";
                $params[] = "%{$term}%";
            }

            $sql = "SELECT DISTINCT * FROM {$this->table} WHERE status = 'published' AND id != ? AND (" . implode(' OR ', $likeConditions) . ") ORDER BY published_at DESC, id DESC LIMIT {$limit}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge([$currentId], $params));
            $related = $stmt->fetchAll();

            foreach ($related as $r) {
                $foundIds[] = (int)$r['id'];
            }
        }

        // Fill remaining slots with recent published posts if needed
        $remaining = $limit - count($related);
        if ($remaining > 0) {
            $inClause = implode(',', $foundIds);
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = 'published' AND id NOT IN ({$inClause}) ORDER BY published_at DESC, id DESC LIMIT {$remaining}");
            $stmt->execute();
            $fallback = $stmt->fetchAll();
            $related = array_merge($related, $fallback);
        }

        return $related;
    }

    /**
     * Fetch recent published articles for sidebar/footer widgets with array support for excluded IDs
     */
    public function getRecentPublished(int $limit = 5, array|int|null $excludeId = null): array {
        $where = "status = 'published'";
        $params = [];

        if (!empty($excludeId)) {
            if (is_array($excludeId)) {
                $cleanIds = implode(',', array_map('intval', array_filter($excludeId)));
                if (!empty($cleanIds)) {
                    $where .= " AND id NOT IN ({$cleanIds})";
                }
            } else {
                $where .= " AND id != ?";
                $params[] = (int)$excludeId;
            }
        }

        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$where} ORDER BY published_at DESC, id DESC LIMIT {$limit}");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Increment post view count
     */
    public function incrementViews(int $id): void {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
}
