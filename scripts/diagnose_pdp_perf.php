<?php
/**
 * Temporary PDP performance diagnostic — times each ProductDetailController block
 * and counts SQL queries. Run: php scripts/diagnose_pdp_perf.php [slug]
 */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
date_default_timezone_set('Asia/Kolkata');

require_once ROOT_PATH . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_PATH);
require_once ROOT_PATH . '/app/Helpers/Functions.php';

if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

// ---- Query logger via PDO subclass wrapper ----
class TimingPDOStatement
{
    public function __construct(private \PDOStatement $stmt) {}

    public function execute(?array $params = null): bool
    {
        $t0 = microtime(true);
        $ok = $params === null ? $this->stmt->execute() : $this->stmt->execute($params);
        $ms = (microtime(true) - $t0) * 1000;
        $sql = $this->stmt->queryString ?? '';
        $GLOBALS['__pdp_queries'][] = [
            'sql' => preg_replace('/\s+/', ' ', trim($sql)),
            'ms' => round($ms, 2),
        ];
        $GLOBALS['__pdp_query_total_ms'] += $ms;
        return $ok;
    }

    public function __call(string $name, array $args)
    {
        return $this->stmt->$name(...$args);
    }

    public function __get(string $name)
    {
        return $this->stmt->$name;
    }
}

class TimingPDO extends \PDO
{
    public function prepare(string $query, array $options = []): \PDOStatement|false
    {
        $stmt = parent::prepare($query, $options);
        if ($stmt === false) {
            return false;
        }
        // Wrap execute via anonymous subclass is hard; monkey-patch via attribute
        return $stmt;
    }
}

$GLOBALS['__pdp_queries'] = [];
$GLOBALS['__pdp_query_total_ms'] = 0.0;
$GLOBALS['__pdp_blocks'] = [];

function pdp_mark(string $label, float $t0): void
{
    $ms = (microtime(true) - $t0) * 1000;
    $GLOBALS['__pdp_blocks'][] = ['label' => $label, 'ms' => round($ms, 2)];
    echo sprintf("[%7.1f ms] %s\n", $ms, $label);
}

function wrap_pdo_execute(\PDO $pdo): void
{
    // Install a temporary execute wrapper by subclassing isn't possible on live PDO.
    // Instead patch via event: use ATTR and a helper prepare logger.
}

// Monkey-patch: decorate Database::getInstance by wrapping prepare via a proxy object stored globally.
// We'll instrument at call sites below instead for accuracy.

$db = \App\Core\Database::getInstance();

// Install query logging by wrapping prepare/execute on a decorator
final class QueryLogPDO
{
    public function __construct(private \PDO $inner) {}

    public function prepare(string $query, array $options = []): mixed
    {
        $stmt = $this->inner->prepare($query, $options);
        return new class($stmt) {
            public function __construct(private \PDOStatement $stmt) {}
            public function execute(?array $params = null): bool
            {
                $t0 = microtime(true);
                $ok = $params === null ? $this->stmt->execute() : $this->stmt->execute($params);
                $ms = (microtime(true) - $t0) * 1000;
                $GLOBALS['__pdp_queries'][] = [
                    'sql' => preg_replace('/\s+/', ' ', trim($this->stmt->queryString ?? '')),
                    'ms' => round($ms, 2),
                ];
                $GLOBALS['__pdp_query_total_ms'] += $ms;
                return $ok;
            }
            public function __call(string $n, array $a) { return $this->stmt->$n(...$a); }
            public function __get(string $n) { return $this->stmt->$n; }
        };
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): mixed
    {
        $t0 = microtime(true);
        $result = $fetchMode === null
            ? $this->inner->query($query)
            : $this->inner->query($query, $fetchMode, ...$fetchModeArgs);
        $ms = (microtime(true) - $t0) * 1000;
        $GLOBALS['__pdp_queries'][] = [
            'sql' => preg_replace('/\s+/', ' ', trim($query)),
            'ms' => round($ms, 2),
        ];
        $GLOBALS['__pdp_query_total_ms'] += $ms;
        return $result;
    }

    public function __call(string $n, array $a) { return $this->inner->$n(...$a); }
}

// Pick product
$slugArg = $argv[1] ?? null;
if ($slugArg) {
    $slug = $slugArg;
} else {
    $row = $db->query("SELECT slug FROM products WHERE status='active' AND main_image IS NOT NULL AND main_image <> '' ORDER BY id DESC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
    $slug = $row['slug'] ?? null;
}
if (!$slug) {
    fwrite(STDERR, "No active product found.\n");
    exit(1);
}

echo "=== PDP PERF DIAGNOSTIC ===\n";
echo "Product slug: {$slug}\n\n";

$productRepo = new \App\Repositories\Eloquent\ProductRepository();
$categoryRepo = new \App\Repositories\Eloquent\CategoryRepository();
$variantModel = new \App\Models\ProductVariant();
$specModel = new \App\Models\ProductSpecification();
$imageModel = new \App\Models\ProductImage();

$totalT0 = microtime(true);

$t0 = microtime(true);
$product = $productRepo->findBySlug($slug);
pdp_mark('1. findBySlug (+ getProductWithDetails)', $t0);
if (!$product) {
    fwrite(STDERR, "Product not found\n");
    exit(1);
}
$productId = (int) $product['id'];
echo "    product_id={$productId} name=" . substr($product['name'] ?? '', 0, 60) . "\n";

$t0 = microtime(true);
$variants = $variantModel->getByProduct($productId, true);
pdp_mark('2. variants getByProduct (N+1 attrs?) count=' . count($variants), $t0);

$t0 = microtime(true);
$specifications = $specModel->getByProduct($productId);
pdp_mark('3. specifications count=' . count($specifications), $t0);

$t0 = microtime(true);
$dbImages = $imageModel->getByProduct($productId);
pdp_mark('4. product images count=' . count($dbImages), $t0);

$t0 = microtime(true);
$visualService = new \App\Services\VisualSearchService();
$visuallySimilar = $visualService->searchByProductId($productId, 8);
pdp_mark('5. VisualSearch searchByProductId items=' . count($visuallySimilar['items'] ?? []), $t0);

$t0 = microtime(true);
$categories = $categoryRepo->getTree();
pdp_mark('6. category getTree', $t0);

$t0 = microtime(true);
$relatedProducts = $productRepo->getByCategory((int) ($product['category_id'] ?? 0), 12);
pdp_mark('7. related getByCategory count=' . count($relatedProducts), $t0);

$t0 = microtime(true);
$subcatId = (int) ($product['subcategory_id'] ?? 0);
if ($subcatId > 0) {
    $stmt = $db->prepare("
        SELECT * FROM products
        WHERE subcategory_id = ? AND status = 'active' AND id <> ?
        ORDER BY is_featured DESC, id DESC
        LIMIT 12
    ");
    $stmt->execute([$subcatId, $productId]);
    $subRelated = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
}
pdp_mark('8. subcategory related', $t0);

$t0 = microtime(true);
$settingModel = new \App\Models\Setting();
$whatsappNumber = preg_replace('/[^0-9]/', '', $settingModel->get('whatsapp_business_number') ?? '919540317079');
pdp_mark('9. Setting::get whatsapp', $t0);

$t0 = microtime(true);
$allTiersStmt = $db->prepare("SELECT * FROM tiered_prices WHERE product_id = ? ORDER BY min_qty ASC");
$allTiersStmt->execute([$productId]);
$allTiers = $allTiersStmt->fetchAll(\PDO::FETCH_ASSOC);
pdp_mark('10. tiered_prices count=' . count($allTiers), $t0);

$t0 = microtime(true);
$variationMatrix = $variantModel->getVariantMatrix($productId);
pdp_mark('11. getVariantMatrix mode=' . ($variationMatrix['variation_mode'] ?? '?') . ' colors=' . count($variationMatrix['colors'] ?? []), $t0);

$t0 = microtime(true);
$cartWishlistState = function_exists('get_cart_and_wishlist_state') ? get_cart_and_wishlist_state() : [];
pdp_mark('12. get_cart_and_wishlist_state', $t0);

// Extra: feature table size + indexes check
$t0 = microtime(true);
$featCount = (int) $db->query('SELECT COUNT(*) FROM product_image_features')->fetchColumn();
$prodCount = (int) $db->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn();
pdp_mark("13. meta: features={$featCount} active_products={$prodCount}", $t0);

$totalMs = (microtime(true) - $totalT0) * 1000;
echo "\n=== TOTAL CONTROLLER-EQUIVALENT TIME: " . round($totalMs, 1) . " ms ===\n\n";

// Sort blocks
$blocks = $GLOBALS['__pdp_blocks'];
usort($blocks, fn($a, $b) => $b['ms'] <=> $a['ms']);
echo "=== BLOCKS RANKED BY TIME ===\n";
foreach ($blocks as $b) {
    echo sprintf("  %8.1f ms  %s\n", $b['ms'], $b['label']);
}

// Index inventory for key tables
echo "\n=== INDEXES ON KEY TABLES ===\n";
$tables = ['products', 'product_variants', 'product_images', 'product_specifications', 'tiered_prices', 'product_image_features', 'wishlist', 'cart_items', 'categories', 'product_colors', 'product_color_sizes', 'product_variation_attribute_map'];
foreach ($tables as $table) {
    try {
        $idxs = $db->query("SHOW INDEX FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
        $names = [];
        foreach ($idxs as $i) {
            $names[$i['Key_name']][] = $i['Column_name'];
        }
        echo "  {$table}:\n";
        foreach ($names as $kn => $cols) {
            echo "    - {$kn} (" . implode(', ', $cols) . ")\n";
        }
    } catch (\Throwable $e) {
        echo "  {$table}: (missing or error: {$e->getMessage()})\n";
    }
}

echo "\nDone.\n";
