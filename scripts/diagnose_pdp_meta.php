<?php
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_PATH);
require ROOT_PATH . '/app/Helpers/Functions.php';
spl_autoload_register(function ($c) {
    if (strncmp('App\\', $c, 4) === 0) {
        $f = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($c, 4)) . '.php';
        if (file_exists($f)) require $f;
    }
});

$db = \App\Core\Database::getInstance();

$rows = $db->query('SELECT product_id, COUNT(*) c FROM product_variants WHERE is_active=1 GROUP BY product_id ORDER BY c DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
echo "Top variant counts:\n";
foreach ($rows as $r) {
    $p = $db->prepare('SELECT slug, name FROM products WHERE id=?');
    $p->execute([$r['product_id']]);
    $prod = $p->fetch(PDO::FETCH_ASSOC);
    echo "  id={$r['product_id']} variants={$r['c']} slug=" . ($prod['slug'] ?? '?') . "\n";
}

$missing = $db->query("SELECT COUNT(*) FROM products p LEFT JOIN product_image_features f ON f.product_id=p.id WHERE p.status='active' AND f.id IS NULL")->fetchColumn();
echo "active without features={$missing}\n";

$featRows = $db->query('SELECT COUNT(*) FROM product_image_features')->fetchColumn();
echo "feature rows={$featRows}\n";

$active = $db->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn();
echo "active products={$active}\n";

// Time getAllIndexedFeatures via reflection
$vs = new \App\Services\VisualSearchService();
$ref = new ReflectionClass($vs);
$m = $ref->getMethod('getAllIndexedFeatures');
$m->setAccessible(true);
$t0 = microtime(true);
$all = $m->invoke($vs, null);
$ms = (microtime(true) - $t0) * 1000;
echo sprintf("getAllIndexedFeatures(null): %d rows in %.1f ms\n", count($all), $ms);

// Time scoring loop alone
$t0 = microtime(true);
$queryFeatures = [
    'dhash' => $all[0]['dhash'] ?? '',
    'ahash' => $all[0]['ahash'] ?? '',
    'color_hist' => json_decode($all[0]['color_hist'] ?? '[]', true) ?: [],
];
$n = 0;
foreach ($all as $target) {
    $vs->scoreFeatures($queryFeatures, $target);
    $n++;
}
$ms = (microtime(true) - $t0) * 1000;
echo sprintf("scoreFeatures x %d: %.1f ms\n", $n, $ms);

// Simulate 3000 products * 2 images
echo "Estimated score time at 6000 features: " . round($ms / max(1, $n) * 6000, 1) . " ms\n";

// N+1 count for a high-variant product
if (!empty($rows[0])) {
    $pid = (int)$rows[0]['product_id'];
    $vm = new \App\Models\ProductVariant();
    $t0 = microtime(true);
    $variants = $vm->getByProduct($pid, true);
    echo sprintf("getByProduct(%d) variants=%d took %.1f ms\n", $pid, count($variants), (microtime(true)-$t0)*1000);
    $t0 = microtime(true);
    $matrix = $vm->getVariantMatrix($pid);
    echo sprintf("getVariantMatrix(%d) took %.1f ms mode=%s\n", $pid, (microtime(true)-$t0)*1000, $matrix['variation_mode'] ?? '?');
}
