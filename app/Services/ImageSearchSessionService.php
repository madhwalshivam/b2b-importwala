<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use PDO;

/**
 * Stores ranked image-search results in the PHP session for the product-page
 * "Similar to your photo" rail. Public-safe fields only.
 */
class ImageSearchSessionService
{
    public const SESSION_KEY = 'image_search_results';
    public const MAX_SEARCHES = 5;
    public const TTL_SECONDS = 1800; // 30 minutes

    public function __construct()
    {
        Session::init();
    }

    /**
     * Persist a ranked result list. Returns a short random token (sid).
     *
     * @param array $items Raw search items (will be sanitized)
     */
    public function store(array $items): string
    {
        $this->pruneExpired();

        $token = bin2hex(random_bytes(8));
        $safeItems = [];
        foreach ($items as $item) {
            $safe = $this->sanitizeItem($item);
            if ($safe !== null) {
                $safeItems[] = $safe;
            }
        }

        $bag = $_SESSION[self::SESSION_KEY] ?? [];
        if (!is_array($bag)) {
            $bag = [];
        }

        $bag[$token] = [
            'created_at' => time(),
            'items' => $safeItems,
        ];

        // Keep only the newest MAX_SEARCHES entries
        if (count($bag) > self::MAX_SEARCHES) {
            uasort($bag, static fn($a, $b) => ((int) ($b['created_at'] ?? 0)) <=> ((int) ($a['created_at'] ?? 0)));
            $bag = array_slice($bag, 0, self::MAX_SEARCHES, true);
        }

        $_SESSION[self::SESSION_KEY] = $bag;
        return $token;
    }

    /**
     * Load stored items for a token. Returns null if missing/expired.
     * Re-filters to active products and refreshes display fields from DB.
     *
     * @return array<int, array>|null
     */
    public function getActiveProducts(string $token, ?int $excludeProductId = null, int $limit = 12): ?array
    {
        $this->pruneExpired();
        $token = preg_replace('/[^a-f0-9]/', '', strtolower($token)) ?? '';
        if ($token === '' || empty($_SESSION[self::SESSION_KEY][$token])) {
            return null;
        }

        $entry = $_SESSION[self::SESSION_KEY][$token];
        $items = $entry['items'] ?? [];
        if (!is_array($items) || empty($items)) {
            return null;
        }

        $ids = [];
        foreach ($items as $item) {
            $id = (int) ($item['id'] ?? 0);
            if ($id > 0 && ($excludeProductId === null || $id !== $excludeProductId)) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT id, name, slug, price, sale_price, main_image, moq, is_featured, is_new_arrival, status
            FROM products
            WHERE id IN ($placeholders) AND status = 'active'
        ");
        $stmt->execute($ids);
        $byId = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $byId[(int) $row['id']] = $row;
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (!isset($byId[$id])) {
                continue; // deactivated / deleted since search
            }
            $row = $byId[$id];
            $row['image_url'] = $this->formatImageUrl($row['main_image'] ?? '');
            $ordered[] = $row;
            if (count($ordered) >= $limit) {
                break;
            }
        }

        return $ordered;
    }

    public function pruneExpired(): void
    {
        $bag = $_SESSION[self::SESSION_KEY] ?? [];
        if (!is_array($bag) || empty($bag)) {
            return;
        }
        $now = time();
        foreach ($bag as $token => $entry) {
            $created = (int) ($entry['created_at'] ?? 0);
            if ($created <= 0 || ($now - $created) > self::TTL_SECONDS) {
                unset($bag[$token]);
            }
        }
        $_SESSION[self::SESSION_KEY] = $bag;
    }

    private function sanitizeItem(array $item): ?array
    {
        $id = (int) ($item['id'] ?? 0);
        $slug = trim((string) ($item['slug'] ?? ''));
        if ($id <= 0 || $slug === '') {
            return null;
        }

        return [
            'id' => $id,
            'name' => (string) ($item['name'] ?? ''),
            'slug' => $slug,
            'price' => $item['price'] ?? null,
            'main_image' => (string) ($item['main_image'] ?? ''),
            'image_url' => (string) ($item['image_url'] ?? ''),
            'matched_variant_id' => !empty($item['matched_variant_id']) ? (int) $item['matched_variant_id'] : null,
            'score' => isset($item['raw_score']) ? (float) $item['raw_score'] : (isset($item['score']) ? (float) $item['score'] : null),
        ];
    }

    private function formatImageUrl(?string $path): string
    {
        $path = $path ?: 'assets/images/placeholder.jpg';
        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }
        return function_exists('asset') ? asset($path) : '/' . ltrim($path, '/');
    }
}
