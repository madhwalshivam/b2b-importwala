<?php
/**
 * Prove visual search ranks the correct product for:
 *  1) the product's own main image
 *  2) a variant image
 *  3) a cropped / resized / brightness-changed copy of the main image
 *
 * Usage (CLI):
 *   php test_visual_search.php
 *   php test_visual_search.php --product=396
 *
 * Or open in browser: /test_visual_search.php
 */

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/app/Helpers/Functions.php';
if (file_exists(ROOT_PATH . '/app/Core/EnvLoader.php')) {
    require_once ROOT_PATH . '/app/Core/EnvLoader.php';
    \App\Core\EnvLoader::load(ROOT_PATH);
}

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

@set_time_limit(0);
header_remove();
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

echo "=== test_visual_search.php ===\n";
echo 'GD: ' . (extension_loaded('gd') ? 'YES' : 'NO') . "\n";
echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . "\n";
echo 'post_max_size: ' . ini_get('post_max_size') . "\n\n";

if (!extension_loaded('gd')) {
    echo "FAIL: PHP GD is not enabled. Enable extension=gd in php.ini.\n";
    exit(1);
}

$db = \App\Core\Database::getInstance();
$svc = new \App\Services\VisualSearchService();

// Ensure table + at least some index
$featCount = (int) $db->query('SELECT COUNT(*) FROM product_image_features')->fetchColumn();
echo "Indexed feature rows: {$featCount}\n";
if ($featCount < 10) {
    echo "Index looks empty — running backfill (this may take a few minutes)...\n";
    $stats = $svc->indexAllProducts(false, 50, function ($done, $total) {
        if ($done % 10 === 0 || $done === $total) {
            echo "  indexed progress {$done}/{$total}\n";
        }
    });
    echo "Backfill done. feature rows={$stats['total_images_indexed']}\n\n";
}

$productId = null;
foreach ($argv ?? [] as $arg) {
    if (preg_match('/^--product=(\d+)$/', $arg, $m)) {
        $productId = (int) $m[1];
    }
}
if (!$productId && !empty($_GET['product'])) {
    $productId = (int) $_GET['product'];
}

if (!$productId) {
    // Prefer a product that has both main + variant image and is already indexed
    $row = $db->query("
        SELECT p.id, p.name, p.main_image
        FROM products p
        INNER JOIN product_image_features f ON f.product_id = p.id
        WHERE p.status = 'active' AND p.main_image IS NOT NULL AND p.main_image <> ''
        ORDER BY (SELECT COUNT(*) FROM product_image_features fx WHERE fx.product_id = p.id) DESC, p.id ASC
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->prepare("SELECT id, name, main_image FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$row) {
    echo "FAIL: No suitable test product found.\n";
    exit(1);
}

$productId = (int) $row['id'];
$mainUrl = trim($row['main_image']);
echo "Test product #{$productId}: {$row['name']}\n";
echo "Main image: {$mainUrl}\n";

// Ensure this product is indexed
$svc->indexProduct($productId, true);

$variantUrl = null;
$vStmt = $db->prepare("SELECT image_url FROM product_variants WHERE product_id = ? AND image_url IS NOT NULL AND image_url <> '' LIMIT 1");
$vStmt->execute([$productId]);
$variantUrl = $vStmt->fetchColumn() ?: null;
if (!$variantUrl) {
    $cStmt = $db->prepare("SELECT swatch_hex_or_image FROM product_colors WHERE product_id = ? AND swatch_hex_or_image LIKE 'http%' LIMIT 1");
    $cStmt->execute([$productId]);
    $variantUrl = $cStmt->fetchColumn() ?: null;
}
echo 'Variant image: ' . ($variantUrl ?: '(none — will reuse main)') . "\n\n";

$tempDir = ROOT_PATH . '/storage/temp_visual_search';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
}

function vs_download_to_temp(string $url, string $tempDir): ?string
{
    $svc = new \App\Services\VisualSearchService();
    // Use extractFeatures path loader indirectly via reflection-free download:
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'ImportWalaVisualSearchTest/1.0',
    ]);
    $data = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($data === false || $code >= 400 || $data === '') {
        // try local
        if (is_file($url)) {
            $data = file_get_contents($url);
        } else {
            return null;
        }
    }
    $path = $tempDir . '/test_src_' . md5($url) . '.jpg';
    file_put_contents($path, $data);
    return $path;
}

function vs_make_perturbed_copy(string $srcPath, string $tempDir): ?string
{
    $bin = file_get_contents($srcPath);
    $img = @imagecreatefromstring($bin);
    if (!$img) {
        return null;
    }
    $w = imagesx($img);
    $h = imagesy($img);
    // Crop 10% margins
    $x0 = (int) round($w * 0.10);
    $y0 = (int) round($h * 0.10);
    $cw = max(8, (int) round($w * 0.80));
    $ch = max(8, (int) round($h * 0.80));
    $cropped = imagecrop($img, ['x' => $x0, 'y' => $y0, 'width' => $cw, 'height' => $ch]);
    if (!$cropped) {
        $cropped = $img;
    } else {
        imagedestroy($img);
    }
    // Resize ~85%
    $nw = max(8, (int) round(imagesx($cropped) * 0.85));
    $nh = max(8, (int) round(imagesy($cropped) * 0.85));
    $resized = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($resized, $cropped, 0, 0, 0, 0, $nw, $nh, imagesx($cropped), imagesy($cropped));
    if ($cropped !== $img) {
        imagedestroy($cropped);
    }
    // Brightness +15
    imagefilter($resized, IMG_FILTER_BRIGHTNESS, 15);
    $out = $tempDir . '/test_perturbed_' . time() . '.jpg';
    imagejpeg($resized, $out, 85);
    imagedestroy($resized);
    return $out;
}

function vs_rank_of_product(\App\Services\VisualSearchService $svc, string $queryPath, int $expectedId): array
{
    // searchByUploadedImage deletes temp files under temp_visual_search — copy first
    $tmp = dirname($queryPath) . '/vs_query_test_' . uniqid() . '.jpg';
    copy($queryPath, $tmp);
    $result = $svc->searchByUploadedImage($tmp, null, 24);
    $rank = null;
    $score = null;
    foreach ($result['items'] ?? [] as $i => $item) {
        if ((int) $item['id'] === $expectedId) {
            $rank = $i + 1;
            $score = $item['raw_score'] ?? $item['score'] ?? null;
            break;
        }
    }
    return [
        'rank' => $rank,
        'score' => $score,
        'top_id' => $result['items'][0]['id'] ?? null,
        'top_score' => $result['items'][0]['raw_score'] ?? null,
        'total' => $result['total'] ?? count($result['items'] ?? []),
        'fallback' => !empty($result['is_fallback']),
        'parse_error' => !empty($result['image_parse_error']),
        'headline' => $result['headline'] ?? '',
    ];
}

$mainLocal = vs_download_to_temp($mainUrl, $tempDir);
if (!$mainLocal) {
    echo "FAIL: Could not download main image.\n";
    exit(1);
}

$cases = [];
$cases['main_image'] = vs_rank_of_product($svc, $mainLocal, $productId);

if ($variantUrl) {
    $varLocal = vs_download_to_temp($variantUrl, $tempDir);
    if ($varLocal) {
        $cases['variant_image'] = vs_rank_of_product($svc, $varLocal, $productId);
    } else {
        $cases['variant_image'] = ['rank' => null, 'error' => 'download failed'];
    }
} else {
    $cases['variant_image'] = vs_rank_of_product($svc, $mainLocal, $productId);
    $cases['variant_image']['note'] = 'no variant — reused main';
}

$perturbed = vs_make_perturbed_copy($mainLocal, $tempDir);
if ($perturbed) {
    $cases['perturbed_copy'] = vs_rank_of_product($svc, $perturbed, $productId);
} else {
    $cases['perturbed_copy'] = ['rank' => null, 'error' => 'could not create perturbed copy'];
}

$pass = true;
foreach ($cases as $name => $c) {
    $rank = $c['rank'] ?? null;
    $ok = $rank !== null && $rank <= 3;
    if (!$ok) {
        $pass = false;
    }
    echo sprintf(
        "[%s] %s rank=%s score=%s top_id=%s top_score=%s fallback=%s parse_error=%s\n",
        $ok ? 'PASS' : 'FAIL',
        $name,
        $rank === null ? 'NOT_IN_TOP24' : $rank,
        isset($c['score']) ? round((float) $c['score'], 4) : 'n/a',
        $c['top_id'] ?? 'n/a',
        isset($c['top_score']) ? round((float) $c['top_score'], 4) : 'n/a',
        !empty($c['fallback']) ? 'yes' : 'no',
        !empty($c['parse_error']) ? 'yes' : 'no'
    );
    if (!empty($c['note'])) {
        echo "  note: {$c['note']}\n";
    }
    if (!empty($c['error'])) {
        echo "  error: {$c['error']}\n";
    }
}

echo "\n" . ($pass ? 'OVERALL PASS: correct product ranked 1–3 for all cases.' : 'OVERALL FAIL: see cases above.') . "\n";
exit($pass ? 0 : 2);
