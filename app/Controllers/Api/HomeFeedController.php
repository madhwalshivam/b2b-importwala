<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Core\Database;
use PDO;

class HomeFeedController extends BaseController
{
    private const PER_PAGE = 24;

    /**
     * Share of each batch taken from categories/subcategories the visitor viewed.
     * 0.55 => 13 of 24 while both pools still have products. The rest of the batch
     * comes from other categories. Tweak this between about 0.50 and 0.60.
     */
    private const INTEREST_SHARE = 0.55;

    /**
     * Most products of one subcategory allowed in a batch while some other
     * subcategory can still fill the slot. Raised only when nothing else is left,
     * so a one-subcategory catalog still paginates through every product.
     */
    private const MAX_PER_SUBCATEGORY = 7;

    /**
     * Longest run of the same subcategory before the next slot must be something else
     * (again, only while an alternative exists).
     */
    private const MAX_RUN = 3;

    /**
     * Weight added to a subcategory for the newest view. Older views in the
     * 20-id cookie scale linearly down to 1/n of this. Three recent ring views
     * outweigh one older necklace view.
     */
    private const SUBCATEGORY_WEIGHT = 12.0;

    /**
     * Weight added to the parent category for the newest view. Kept well below
     * SUBCATEGORY_WEIGHT so "Rings" beats "other jewelry".
     */
    private const CATEGORY_WEIGHT = 3.0;

    public function index(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: private, no-store, no-cache, must-revalidate');
        header('X-Robots-Tag: noindex');

        $page = (int) ($_GET['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        if ($page > 500) {
            $page = 500;
        }

        $seed = (int) ($_GET['seed'] ?? 0);
        if ($seed < 1) {
            $seed = 1;
        }
        if ($seed > 2147483647) {
            $seed = $seed % 2147483647;
            if ($seed < 1) {
                $seed = 1;
            }
        }

        $recentIds = $this->recentProductIds();
        $views = $this->loadViews($recentIds);
        $products = $this->loadCandidates();
        $pageResult = $this->arrangePage($products, $views, $recentIds, $seed, $page);
        $rows = $this->loadCards($pageResult['ids']);

        header('X-Feed-Has-More: ' . ($pageResult['has_more'] ? '1' : '0'));

        $card = ROOT_PATH . '/views/web/partials/product_card.php';
        $render = static function (array $product) use ($card): void {
            require $card;
        };

        foreach ($rows as $product) {
            if (!is_array($product) || empty($product['id'])) {
                continue;
            }
            $render($product);
        }

        $flag = $pageResult['has_more'] ? '1' : '0';
        echo '<div class="iw-feed-meta" data-has-more="' . $flag . '" hidden></div>';
    }

    /**
     * @return int[] most recent first, max 20
     */
    private function recentProductIds(): array
    {
        $raw = (string) ($_COOKIE['iw_recent'] ?? '');
        if ($raw === '') {
            return [];
        }
        $raw = substr($raw, 0, 400);
        $parts = preg_split('/[^0-9]+/', $raw) ?: [];
        $ids = [];
        foreach ($parts as $part) {
            $id = (int) $part;
            if ($id < 1 || isset($ids[$id])) {
                continue;
            }
            $ids[$id] = $id;
            if (count($ids) >= 20) {
                break;
            }
        }
        return array_values($ids);
    }

    /**
     * @param int[] $productIds most recent first
     * @return array<int, array{id:int, category_id:int, subcategory_id:int}>
     */
    private function loadViews(array $productIds): array
    {
        $productIds = $this->positiveInts($productIds);
        if ($productIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = Database::getInstance()->prepare(
            "SELECT id, category_id, subcategory_id FROM products WHERE id IN ({$placeholders})"
        );
        $stmt->execute($productIds);
        $byId = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            $byId[$id] = [
                'id' => $id,
                'category_id' => (int) ($row['category_id'] ?? 0),
                'subcategory_id' => (int) ($row['subcategory_id'] ?? 0),
            ];
        }

        $ordered = [];
        foreach ($productIds as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }
        return $ordered;
    }

    /**
     * @return array<int, array{id:int, category_id:int, subcategory_id:int}>
     */
    private function loadCandidates(): array
    {
        $stmt = Database::getInstance()->query(
            "SELECT id, category_id, subcategory_id
             FROM products
             WHERE status = 'active'"
        );
        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            $rows[] = [
                'id' => $id,
                'category_id' => (int) ($row['category_id'] ?? 0),
                'subcategory_id' => (int) ($row['subcategory_id'] ?? 0),
            ];
        }
        return $rows;
    }

    /**
     * @param int[] $ids
     * @return array<int, array<string, mixed>>
     */
    private function loadCards(array $ids): array
    {
        $ids = $this->positiveInts($ids);
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "SELECT id, slug, name, price, sale_price, main_image, is_new_arrival
             FROM products
             WHERE id IN ({$placeholders})"
        );
        $stmt->execute($ids);
        $byId = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $byId[(int) $row['id']] = $row;
        }
        $ordered = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }
        return $ordered;
    }

    /**
     * Deterministic mixed order for one page.
     * Recently viewed product IDs are left out. The same seed always yields the same sequence.
     *
     * @param array<int, array{id:int, category_id:int, subcategory_id:int}> $products
     * @param array<int, array{id:int, category_id:int, subcategory_id:int}> $views
     * @param int[] $excludeIds
     * @return array{ids: int[], has_more: bool}
     */
    private function arrangePage(array $products, array $views, array $excludeIds, int $seed, int $page): array
    {
        $exclude = [];
        foreach ($excludeIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $exclude[$id] = true;
            }
        }

        $weights = $this->interestWeights($views);
        $interest = [];
        $other = [];
        foreach ($products as $product) {
            if (isset($exclude[$product['id']])) {
                continue;
            }
            $subWeight = $weights['sub'][$product['subcategory_id']] ?? 0.0;
            $catWeight = $weights['cat'][$product['category_id']] ?? 0.0;
            if ($subWeight > 0 || $catWeight > 0) {
                // Exact subcategory carries the recency weight. A parent-category
                // match is included, but only at the weaker category weight.
                $product['weight'] = 1.0 + ($subWeight > 0 ? $subWeight : $catWeight);
                $interest[] = $product;
            } else {
                $other[] = $product;
            }
        }

        $buckets = $this->interestBuckets($interest, $seed);
        $otherQueue = $this->diversifiedQueue($other, $seed);
        $state = [
            'buckets' => $buckets,
            'other' => $otherQueue,
            'other_idx' => 0,
            'deferred' => [],
        ];

        $eligible = count($interest) + count($other);
        $offset = ($page - 1) * self::PER_PAGE;
        if ($offset >= $eligible) {
            return ['ids' => [], 'has_more' => false];
        }

        for ($i = 1; $i < $page; $i++) {
            $this->fillBatch($state);
        }
        $batch = $this->fillBatch($state);

        return [
            'ids' => array_map(static fn(array $row): int => (int) $row['id'], $batch),
            'has_more' => ($offset + count($batch)) < $eligible,
        ];
    }

    /**
     * @param array<int, array{id:int, category_id:int, subcategory_id:int}> $views
     * @return array{sub: array<int, float>, cat: array<int, float>}
     */
    private function interestWeights(array $views): array
    {
        $sub = [];
        $cat = [];
        $total = count($views);
        if ($total === 0) {
            return ['sub' => $sub, 'cat' => $cat];
        }
        foreach ($views as $index => $view) {
            $factor = ($total - $index) / $total;
            $subId = (int) $view['subcategory_id'];
            $catId = (int) $view['category_id'];
            if ($subId > 0) {
                $sub[$subId] = ($sub[$subId] ?? 0.0) + (self::SUBCATEGORY_WEIGHT * $factor);
            }
            if ($catId > 0) {
                $cat[$catId] = ($cat[$catId] ?? 0.0) + (self::CATEGORY_WEIGHT * $factor);
            }
        }
        return ['sub' => $sub, 'cat' => $cat];
    }

    /**
     * @param array<int, array{id:int, category_id:int, subcategory_id:int, weight:float}> $interest
     * @return array<int, array{i:int, items: array<int, array<string, mixed>>}>
     */
    private function interestBuckets(array $interest, int $seed): array
    {
        $buckets = [];
        foreach ($interest as $product) {
            $product['key'] = $this->rankKey($seed, (int) $product['id'], (float) $product['weight']);
            $buckets[$product['subcategory_id']]['items'][] = $product;
        }
        foreach ($buckets as $subId => $bucket) {
            usort($bucket['items'], static function (array $a, array $b): int {
                if ($a['key'] == $b['key']) {
                    return $a['id'] <=> $b['id'];
                }
                return $a['key'] <=> $b['key'];
            });
            $buckets[$subId] = ['i' => 0, 'items' => $bucket['items']];
        }
        return $buckets;
    }

    /**
     * Round-robin across categories, and across subcategories inside each category,
     * so a large category cannot occupy the "other" slots by itself.
     *
     * @param array<int, array{id:int, category_id:int, subcategory_id:int}> $items
     * @return array<int, array{id:int, category_id:int, subcategory_id:int}>
     */
    private function diversifiedQueue(array $items, int $seed): array
    {
        $tree = [];
        foreach ($items as $item) {
            $tree[$item['category_id']][$item['subcategory_id']][] = $item;
        }
        $catIds = array_keys($tree);
        usort($catIds, function ($a, $b) use ($seed): int {
            return $this->unit($seed, (int) $a, 'cat') <=> $this->unit($seed, (int) $b, 'cat');
        });

        $subIds = [];
        $pointers = [];
        foreach ($catIds as $catId) {
            $subs = array_keys($tree[$catId]);
            usort($subs, function ($a, $b) use ($seed): int {
                return $this->unit($seed, (int) $a, 'sub') <=> $this->unit($seed, (int) $b, 'sub');
            });
            foreach ($subs as $subId) {
                usort($tree[$catId][$subId], function (array $a, array $b) use ($seed): int {
                    $left = $this->unit($seed, (int) $a['id'], 'o');
                    $right = $this->unit($seed, (int) $b['id'], 'o');
                    if ($left == $right) {
                        return $a['id'] <=> $b['id'];
                    }
                    return $left <=> $right;
                });
            }
            $subIds[$catId] = $subs;
            $pointers[$catId] = ['sub' => 0, 'item' => []];
            foreach ($subs as $subId) {
                $pointers[$catId]['item'][$subId] = 0;
            }
        }

        $queue = [];
        $remaining = count($items);
        $catCount = count($catIds);
        $catCursor = 0;
        while ($remaining > 0 && $catCount > 0) {
            $picked = false;
            for ($step = 0; $step < $catCount; $step++) {
                $catId = $catIds[$catCursor];
                $catCursor = ($catCursor + 1) % $catCount;
                $item = $this->pullNextInCategory($tree, $subIds, $pointers, $catId);
                if ($item !== null) {
                    $queue[] = $item;
                    $remaining--;
                    $picked = true;
                    break;
                }
            }
            if (!$picked) {
                break;
            }
        }
        return $queue;
    }

    /**
     * @param array<int, array<int, array<int, array<string, mixed>>>> $tree
     * @param array<int, int[]> $subIds
     * @param array<int, array{sub:int, item: array<int, int>}> $pointers
     * @return array<string, mixed>|null
     */
    private function pullNextInCategory(array &$tree, array &$subIds, array &$pointers, int $catId): ?array
    {
        $subs = $subIds[$catId] ?? [];
        $count = count($subs);
        if ($count === 0) {
            return null;
        }
        for ($step = 0; $step < $count; $step++) {
            $index = ($pointers[$catId]['sub'] + $step) % $count;
            $subId = $subs[$index];
            $itemIndex = $pointers[$catId]['item'][$subId] ?? 0;
            if ($itemIndex < count($tree[$catId][$subId])) {
                $item = $tree[$catId][$subId][$itemIndex];
                $pointers[$catId]['item'][$subId] = $itemIndex + 1;
                $pointers[$catId]['sub'] = ($index + 1) % $count;
                return $item;
            }
        }
        return null;
    }

    /**
     * @param array{buckets: array, other: array, other_idx: int, deferred: array} $state
     * @return array<int, array<string, mixed>>
     */
    private function fillBatch(array &$state): array
    {
        $interestLeft = 0;
        foreach ($state['buckets'] as $bucket) {
            $interestLeft += count($bucket['items']) - $bucket['i'];
        }
        $otherLeft = (count($state['other']) - $state['other_idx']) + count($state['deferred']);
        $available = $interestLeft + $otherLeft;
        if ($available < 1) {
            return [];
        }

        $take = min(self::PER_PAGE, $available);
        $wantInterest = (int) round($take * self::INTEREST_SHARE);
        if ($interestLeft === 0) {
            $wantInterest = 0;
        }
        $wantInterest = min($wantInterest, $interestLeft, $take);
        $wantOther = min($take - $wantInterest, $otherLeft);
        if ($wantInterest + $wantOther < $take) {
            $extra = $take - $wantInterest - $wantOther;
            $addInterest = min($extra, $interestLeft - $wantInterest);
            $wantInterest += $addInterest;
            $wantOther += min($extra - $addInterest, $otherLeft - $wantOther);
        }

        $slots = $this->mixSlots($wantInterest, $wantOther);
        $batch = [];
        $counts = [];
        $recentSubs = [];
        foreach ($slots as $slot) {
            $item = $this->takeForSlot($state, $slot, $counts, $recentSubs);
            if ($item === null) {
                break;
            }
            $subId = (int) $item['subcategory_id'];
            $counts[$subId] = ($counts[$subId] ?? 0) + 1;
            $recentSubs[] = $subId;
            $batch[] = $item;
        }
        return $batch;
    }

    /**
     * @param array{buckets: array, other: array, other_idx: int, deferred: array} $state
     * @param array<int, int> $counts
     * @param int[] $recentSubs
     * @return array<string, mixed>|null
     */
    private function takeForSlot(array &$state, string $slot, array $counts, array $recentSubs): ?array
    {
        $order = $slot === 'i' ? ['i', 'o'] : ['o', 'i'];
        // Keep the subcategory cap until no capped alternative exists. Drop the
        // run limit first so a different subcategory can break a streak.
        $passes = [
            [true, true],
            [false, true],
            [false, false],
        ];
        foreach ($passes as [$respectRun, $respectCap]) {
            foreach ($order as $source) {
                $item = $source === 'i'
                    ? $this->takeInterest($state['buckets'], $counts, $recentSubs, $respectCap, $respectRun)
                    : $this->takeOther($state, $counts, $recentSubs, $respectCap, $respectRun);
                if ($item !== null) {
                    return $item;
                }
            }
        }
        return null;
    }

    /**
     * @param array<int, array{i:int, items: array<int, array<string, mixed>>}> $buckets
     * @param array<int, int> $counts
     * @param int[] $recentSubs
     * @return array<string, mixed>|null
     */
    private function takeInterest(array &$buckets, array $counts, array $recentSubs, bool $respectCap, bool $respectRun): ?array
    {
        $bestSub = null;
        $bestKey = INF;
        foreach ($buckets as $subId => $bucket) {
            $subId = (int) $subId;
            if ($bucket['i'] >= count($bucket['items'])) {
                continue;
            }
            if ($respectCap && ($counts[$subId] ?? 0) >= self::MAX_PER_SUBCATEGORY) {
                continue;
            }
            if ($respectRun && $this->runTooLong($recentSubs, $subId)) {
                continue;
            }
            $key = (float) $bucket['items'][$bucket['i']]['key'];
            if ($key < $bestKey) {
                $bestKey = $key;
                $bestSub = $subId;
            }
        }
        if ($bestSub === null) {
            return null;
        }
        $item = $buckets[$bestSub]['items'][$buckets[$bestSub]['i']];
        $buckets[$bestSub]['i']++;
        return $item;
    }

    /**
     * @param array{buckets: array, other: array, other_idx: int, deferred: array} $state
     * @param array<int, int> $counts
     * @param int[] $recentSubs
     * @return array<string, mixed>|null
     */
    private function takeOther(array &$state, array $counts, array $recentSubs, bool $respectCap, bool $respectRun): ?array
    {
        foreach ($state['deferred'] as $index => $item) {
            $subId = (int) $item['subcategory_id'];
            if ($respectCap && ($counts[$subId] ?? 0) >= self::MAX_PER_SUBCATEGORY) {
                continue;
            }
            if ($respectRun && $this->runTooLong($recentSubs, $subId)) {
                continue;
            }
            array_splice($state['deferred'], $index, 1);
            return $item;
        }

        $count = count($state['other']);
        $skipped = [];
        while ($state['other_idx'] < $count) {
            $item = $state['other'][$state['other_idx']];
            $state['other_idx']++;
            $subId = (int) $item['subcategory_id'];
            $blocked = ($respectCap && ($counts[$subId] ?? 0) >= self::MAX_PER_SUBCATEGORY)
                || ($respectRun && $this->runTooLong($recentSubs, $subId));
            if ($blocked) {
                $skipped[] = $item;
                continue;
            }
            if ($skipped !== []) {
                $state['deferred'] = array_merge($state['deferred'], $skipped);
            }
            return $item;
        }
        if ($skipped !== []) {
            $state['deferred'] = array_merge($state['deferred'], $skipped);
        }
        return null;
    }

    /**
     * @param int[] $recentSubs
     */
    private function runTooLong(array $recentSubs, int $subId): bool
    {
        $count = count($recentSubs);
        if ($count < self::MAX_RUN) {
            return false;
        }
        for ($k = 1; $k <= self::MAX_RUN; $k++) {
            if ($recentSubs[$count - $k] !== $subId) {
                return false;
            }
        }
        return true;
    }

    /**
     * Spread the two pools through the batch instead of emitting one pool and then the other.
     *
     * @return string[] 'i' or 'o'
     */
    private function mixSlots(int $interestSlots, int $otherSlots): array
    {
        $total = $interestSlots + $otherSlots;
        $slots = [];
        $interestLeft = $interestSlots;
        $otherLeft = $otherSlots;
        $interestErr = 0;
        $otherErr = 0;
        for ($n = 0; $n < $total; $n++) {
            if ($interestLeft > 0 && ($otherLeft === 0 || $interestErr <= $otherErr)) {
                $slots[] = 'i';
                $interestLeft--;
                $interestErr += $otherSlots;
            } else {
                $slots[] = 'o';
                $otherLeft--;
                $otherErr += $interestSlots;
            }
        }
        return $slots;
    }

    private function rankKey(int $seed, int $id, float $weight): float
    {
        if ($weight <= 0) {
            $weight = 1.0;
        }
        return -log($this->unit($seed, $id, 'w')) / $weight;
    }

    /**
     * Stable unit float in (0, 1] for this seed and id.
     */
    private function unit(int $seed, int $id, string $salt): float
    {
        $hash = sprintf('%u', crc32($salt . $seed . ':' . $id));
        return ((float) $hash + 1.0) / 4294967297.0;
    }

    /**
     * @param int[] $ids
     * @return int[]
     */
    private function positiveInts(array $ids): array
    {
        $clean = [];
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $clean[$id] = $id;
            }
        }
        return array_values($clean);
    }
}
