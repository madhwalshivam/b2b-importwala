<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Helpers/Functions.php';
require_once ROOT_PATH . '/app/Models/BlogPost.php';
require_once ROOT_PATH . '/app/Helpers/SEO.php';

use App\Models\BlogPost;
use App\Helpers\SEO;

$blogModel = new BlogPost();
$posts = $blogModel->getPublishedPosts(1, 10);

echo "Published posts count: " . count($posts['items']) . "\n";
foreach ($posts['items'] as $p) {
    echo "ID: {$p['id']} | Title: {$p['title']} | Slug: /blog/{$p['slug']} | Alt: {$p['featured_image_alt']}\n";
}

$single = $blogModel->findPublishedBySlug('erw-pipe-vs-seamless-pipe');
if ($single) {
    echo "\nFound Single Post:\n";
    echo "Title: " . $single['title'] . "\n";
    echo "Meta Title: " . $single['meta_title'] . "\n";
    echo "Meta Description: " . $single['meta_description'] . "\n";
    
    // Test SEO helper
    $metaHtml = SEO::renderMeta([
        'title' => $single['meta_title'],
        'description' => $single['meta_description'],
        'image' => $single['featured_image'],
        'url' => 'http://localhost/ecommerce/blog/' . $single['slug'],
        'article' => $single
    ]);

    echo "\nGenerated SEO Meta & Schema:\n";
    echo $metaHtml . "\n";
}
