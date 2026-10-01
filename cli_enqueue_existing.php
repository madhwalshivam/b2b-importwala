<?php
/**
 * CLI Script to Enqueue Existing External URLs
 */
require_once __DIR__ . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(__DIR__);
require_once __DIR__ . '/app/Helpers/Functions.php';
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;
use App\Helpers\ImageMirror;

echo "Scanning database for external URLs...\n";
$db = Database::getInstance();

$queries = [
    "SELECT id, image_url as url FROM product_images WHERE image_url LIKE 'http%' AND image_url NOT LIKE '%r2.dev%' AND image_url NOT LIKE '%cloudflare%'",
    "SELECT id, main_image as url FROM products WHERE main_image LIKE 'http%' AND main_image NOT LIKE '%r2.dev%' AND main_image NOT LIKE '%cloudflare%'",
    "SELECT id, swatch_hex_or_image as url FROM product_colors WHERE swatch_hex_or_image LIKE 'http%' AND swatch_hex_or_image NOT LIKE '%r2.dev%' AND swatch_hex_or_image NOT LIKE '%cloudflare%'",
    "SELECT id, image_url as url FROM product_variants WHERE image_url LIKE 'http%' AND image_url NOT LIKE '%r2.dev%' AND image_url NOT LIKE '%cloudflare%'",
];

$queuedCount = 0;
foreach ($queries as $sql) {
    $stmt = $db->query($sql);
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        if (!empty($row['url']) && filter_var($row['url'], FILTER_VALIDATE_URL)) {
            ImageMirror::enqueue($row['url']);
            $queuedCount++;
            if ($queuedCount % 100 === 0) {
                echo "Queued $queuedCount URLs...\n";
            }
        }
    }
}

echo "Done! Total URLs enqueued: $queuedCount\n";
