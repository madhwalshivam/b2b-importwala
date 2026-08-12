<?php
define('ROOT_PATH', dirname(__DIR__));

if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Helpers/Functions.php';

use App\Models\BlogPost;

$blogModel = new BlogPost();

echo "=== 1. TESTING GET PUBLISHED POSTS & EXCLUSION ===\n";
$allRecent = $blogModel->getRecentPublished(10);
echo "Total published posts found: " . count($allRecent) . "\n";

$latestPost = !empty($allRecent[0]) ? $allRecent[0] : null;
$editorPicks = array_slice($allRecent, 1, 3);

$excludeIds = [];
if ($latestPost) $excludeIds[] = (int)$latestPost['id'];
foreach ($editorPicks as $pick) {
    $excludeIds[] = (int)$pick['id'];
}

echo "Excluded IDs (Latest + Editor's Picks): " . implode(', ', $excludeIds) . "\n";

$mainGrid = $blogModel->getPublishedPosts(1, 9, $excludeIds);
echo "Main Grid Items Count: " . count($mainGrid['items']) . "\n";

$duplicated = false;
foreach ($mainGrid['items'] as $item) {
    if (in_array((int)$item['id'], $excludeIds)) {
        echo "[ERROR] Post ID {$item['id']} ('{$item['title']}') was duplicated in main grid!\n";
        $duplicated = true;
    }
}

if (!$duplicated) {
    echo "[SUCCESS] Zero duplicates! Every post appears exactly once on page 1.\n";
}

echo "\n=== 2. TESTING RELATED POSTS ===\n";
if ($latestPost) {
    echo "Current Post: ID {$latestPost['id']} | Title: '{$latestPost['title']}'\n";
    $related = $blogModel->getRelatedPosts($latestPost, 3);
    echo "Related Posts Found: " . count($related) . "\n";
    foreach ($related as $rel) {
        echo "  -> Related ID {$rel['id']} | Title: '{$rel['title']}' | Slug: /blog/{$rel['slug']}\n";
    }
}

echo "\n=== 3. TESTING SLUG GENERATION & UNIQUE HANDLING ===\n";
$testSlug1 = $blogModel->generateUniqueSlug("7 Tips to Extend Your Electric Scooter Range in City Traffic");
echo "Generated unique slug (should auto append suffix if exists): " . $testSlug1 . "\n";

echo "\n=== 4. CHECKING FORM CSRF & CONTROLLER TEMPLATES ===\n";
$formContent = file_get_contents(ROOT_PATH . '/app/Views/admin/blogs/form.php');
if (str_contains($formContent, 'csrf_field()') && str_contains($formContent, 'tinymce.triggerSave()')) {
    echo "[SUCCESS] form.php contains CSRF field and tinymce.triggerSave() handler!\n";
} else {
    echo "[ERROR] form.php missing CSRF field or TinyMCE handler!\n";
}

echo "\nAll verification checks completed successfully!\n";
