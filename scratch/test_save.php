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
$posts = $blogModel->all();
echo "Total posts in database: " . count($posts) . "\n";

if (!empty($posts[0])) {
    $p = $posts[0];
    echo "Testing update on Post ID {$p['id']}...\n";
    try {
        $res = $blogModel->update($p['id'], [
            'title' => $p['title'],
            'slug' => $p['slug'],
            'excerpt' => $p['excerpt'],
            'content' => $p['content'],
            'featured_image' => $p['featured_image'],
            'featured_image_alt' => $p['featured_image_alt'] ?? $p['title'],
            'meta_title' => $p['meta_title'],
            'meta_description' => $p['meta_description'],
            'focus_keyword' => $p['focus_keyword'],
            'author_name' => $p['author_name'],
            'status' => $p['status'],
            'published_at' => $p['published_at']
        ]);
        echo "Update result: " . ($res ? "SUCCESS" : "FAILED") . "\n";
    } catch (\Throwable $e) {
        echo "Update exception: " . $e->getMessage() . "\n";
    }
}
