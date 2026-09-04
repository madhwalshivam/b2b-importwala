<?php
define('ROOT_PATH', dirname(__DIR__));

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

require_once __DIR__ . '/../app/Helpers/Functions.php';

$s = new App\Services\VisualSearchService();

echo "========================================================\n";
echo " TEST 1: EXACT MATCH UPLOAD (Product #1 Necklace)\n";
echo "========================================================\n";
$img1 = ROOT_PATH . '/public/uploads/products/stainless-steel-necklaces.jpg';
$res1 = $s->searchByUploadedImage($img1);
echo "Auto Redirect: " . ($res1['auto_redirect'] ? "TRUE" : "FALSE") . "\n";
echo "Redirect URL: " . ($res1['redirect_url'] ?? 'NONE') . "\n";
echo "Top Match: " . ($res1['top_match']['name'] ?? 'NONE') . " (Score: " . ($res1['top_match']['similarity_score'] ?? 0) . "%)\n\n";

echo "========================================================\n";
echo " TEST 2: EXACT MATCH UPLOAD (Product #4 Sunglasses)\n";
echo "========================================================\n";
$img4 = ROOT_PATH . '/public/uploads/products/sunglasses.jpg';
$res4 = $s->searchByUploadedImage($img4);
echo "Auto Redirect: " . ($res4['auto_redirect'] ? "TRUE" : "FALSE") . "\n";
echo "Redirect URL: " . ($res4['redirect_url'] ?? 'NONE') . "\n";
echo "Top Match: " . ($res4['top_match']['name'] ?? 'NONE') . " (Score: " . ($res4['top_match']['similarity_score'] ?? 0) . "%)\n\n";

echo "========================================================\n";
echo " TEST 3: UNRELATED RANDOM IMAGE UPLOAD\n";
echo "========================================================\n";
$randomImg = ROOT_PATH . '/storage/temp_visual_search/test_random.jpg';
if (!is_dir(dirname($randomImg))) mkdir(dirname($randomImg), 0755, true);

// Create solid red 100x100 image
$im = imagecreatetruecolor(100, 100);
$red = imagecolorallocate($im, 255, 0, 0);
imagefill($im, 0, 0, $red);
imagejpeg($im, $randomImg);
imagedestroy($im);

$res3 = $s->searchByUploadedImage($randomImg);
echo "Has Matches: " . ($res3['has_matches'] ? "TRUE" : "FALSE") . "\n";
echo "Is Fallback: " . ($res3['is_fallback'] ? "TRUE" : "FALSE") . "\n";
echo "Headline: " . ($res3['headline'] ?? '') . "\n";
echo "Total Fallback Items: " . count($res3['items'] ?? []) . "\n\n";

echo "========================================================\n";
echo " TEST 4: DEBUG ANALYSIS FOR PRODUCT #2 (Ring)\n";
echo "========================================================\n";
$img2 = ROOT_PATH . '/public/uploads/products/statement-rings.jpg';
$dbg = $s->getDebugAnalysis($img2);
echo "Query Path: " . $dbg['query_image_path'] . "\n";
echo "Vector Dims: " . $dbg['vector_dimensions'] . "\n";
echo "Time Taken: " . $dbg['embedding_time_ms'] . " ms\n";
echo "Top Match: " . $dbg['top_5_matches'][0]['name'] . " | Raw Score: " . $dbg['top_5_matches'][0]['raw_score'] . " | Category: " . $dbg['top_5_matches'][0]['match_category'] . "\n";
echo "========================================================\n";
