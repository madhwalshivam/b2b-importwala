<?php
/**
 * Database Migration & Clean URL Converter Script for ImportWale
 * Updates stored query-string URLs across database tables to clean SEO-friendly URLs.
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

use App\Core\Database;

echo "=== Starting Clean URL Migration ===\n\n";

try {
    $db = Database::getInstance();

    // Helper to convert legacy /catalog?q=... or /catalog?category=... to clean URL path
    $cleaner = function (string $url) use ($db): string {
        $url = trim($url);
        if (empty($url)) return $url;

        // Convert query string parameters
        if (str_contains($url, 'catalog?')) {
            $parsed = parse_url($url);
            parse_str($parsed['query'] ?? '', $query);

            if (!empty($query['category'])) {
                return '/category/' . slugify($query['category']);
            }

            if (!empty($query['category_id'])) {
                $st = $db->prepare("SELECT slug FROM categories WHERE id = ?");
                $st->execute([(int)$query['category_id']]);
                $cSlug = $st->fetchColumn();
                if ($cSlug) return '/category/' . $cSlug;
            }

            if (!empty($query['collection_id'])) {
                return '/collection/' . (int)$query['collection_id'];
            }

            if (!empty($query['q'])) {
                $qClean = str_replace('&', 'and', $query['q']);
                $slug = slugify($qClean);

                // Check if slug matches category or subcategory
                $st = $db->prepare("SELECT slug FROM categories WHERE slug = ?");
                $st->execute([$slug]);
                $cSlug = $st->fetchColumn();
                if ($cSlug) return '/category/' . $cSlug;

                $stSub = $db->prepare("SELECT s.slug as sub_slug, c.slug as cat_slug FROM subcategories s JOIN categories c ON s.category_id = c.id WHERE s.slug = ?");
                $stSub->execute([$slug]);
                $subRow = $stSub->fetch();
                if ($subRow) return '/category/' . $subRow['cat_slug'] . '/' . $subRow['sub_slug'];

                return '/search/' . $slug;
            }
        }

        return $url;
    };

    // 1. Update featured_subcategories link_url
    try {
        $stmt = $db->query("SELECT id, name, slug, link_url FROM featured_subcategories");
        $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countSub = 0;
        foreach ($subs as $sub) {
            $newUrl = $cleaner($sub['link_url'] ?? '');
            if (empty($newUrl) || $newUrl === '/catalog') {
                $newUrl = '/category/' . slugify($sub['slug'] ?? $sub['name']);
            }
            $up = $db->prepare("UPDATE featured_subcategories SET link_url = ? WHERE id = ?");
            $up->execute([$newUrl, $sub['id']]);
            $countSub++;
        }
        echo "Updated {$countSub} featured_subcategories link URLs.\n";
    } catch (\Throwable $e) {
        echo "Note on featured_subcategories: " . $e->getMessage() . "\n";
    }

    // 2. Update navigation_links url
    try {
        $stmt = $db->query("SELECT id, label, url FROM navigation_links");
        $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countNav = 0;
        foreach ($links as $nav) {
            $newUrl = $cleaner($nav['url'] ?? '');
            if ($newUrl !== $nav['url']) {
                $up = $db->prepare("UPDATE navigation_links SET url = ? WHERE id = ?");
                $up->execute([$newUrl, $nav['id']]);
                $countNav++;
            }
        }
        echo "Updated {$countNav} navigation_links URLs.\n";
    } catch (\Throwable $e) {
        echo "Note on navigation_links: " . $e->getMessage() . "\n";
    }

    // 3. Update collection_cards link_url
    try {
        $stmt = $db->query("SELECT id, title, link_url FROM collection_cards");
        $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countCards = 0;
        foreach ($cards as $card) {
            $newUrl = $cleaner($card['link_url'] ?? '');
            if (empty($newUrl) || $newUrl === '/catalog') {
                $newUrl = '/collection/' . $card['id'];
            }
            if ($newUrl !== $card['link_url']) {
                $up = $db->prepare("UPDATE collection_cards SET link_url = ? WHERE id = ?");
                $up->execute([$newUrl, $card['id']]);
                $countCards++;
            }
        }
        echo "Updated {$countCards} collection_cards link URLs.\n";
    } catch (\Throwable $e) {
        echo "Note on collection_cards: " . $e->getMessage() . "\n";
    }

    // 4. Update banners link_url
    try {
        $stmt = $db->query("SELECT id, link_url FROM banners");
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countBanners = 0;
        foreach ($banners as $b) {
            $newUrl = $cleaner($b['link_url'] ?? '');
            if ($newUrl !== $b['link_url']) {
                $up = $db->prepare("UPDATE banners SET link_url = ? WHERE id = ?");
                $up->execute([$newUrl, $b['id']]);
                $countBanners++;
            }
        }
        echo "Updated {$countBanners} banners link URLs.\n";
    } catch (\Throwable $e) {
        echo "Note on banners: " . $e->getMessage() . "\n";
    }

    echo "\n=== Migration Finished Successfully! ===\n";

} catch (\Throwable $e) {
    echo "Migration Exception: " . $e->getMessage() . "\n";
}
