<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BlogPost;

class BlogFrontendController extends BaseController {
    private BlogPost $blogModel;

    public function __construct() {
        parent::__construct();
        $this->blogModel = new BlogPost();
    }

    /**
     * Blog Listing Page (/blog)
     */
    public function index(): void {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['q'] ?? '');
        $categorySlug = trim($_GET['cat'] ?? '');

        $categories = $this->blogModel->getAllCategories();
        $pagination = $this->blogModel->getPublishedPostsFiltered($page, 9, $categorySlug ?: null, $search ?: null);

        $seoOptions = [
            'title' => 'ImportWale Journal — Insights, Guides & B2B News',
            'description' => 'Explore the latest B2B wholesale insights, product guides, and industry news from ImportWale.',
            'url' => url('blog'),
            'type' => 'website'
        ];

        $this->renderView('web/blog', [
            'posts' => $pagination['items'],
            'pagination' => $pagination,
            'categories' => $categories,
            'activeCategory' => $categorySlug,
            'searchQuery' => $search,
            'seoOptions' => $seoOptions
        ]);
    }

    /**
     * Single Blog Post Page (/blog/{slug})
     */
    public function show(string $slug): void {
        $slug = trim($slug);
        $post = $this->blogModel->findPublishedBySlug($slug);

        if (!$post) {
            http_response_code(404);
            $this->renderView('errors/404');
            return;
        }

        // Increment view count
        $this->blogModel->incrementViews($post['id']);

        // Fetch related articles
        $relatedPosts = $this->blogModel->getRelatedPosts($post, 3);

        // Fetch recent posts for sidebar (excluding current post ID)
        $recentPosts = $this->blogModel->getRecentPublished(5, [(int)$post['id']]);

        $metaTitle = !empty($post['meta_title']) ? $post['meta_title'] : ($post['title'] . ' | ImportWale Journal');
        $metaDescription = !empty($post['meta_description']) ? $post['meta_description'] : (!empty($post['excerpt']) ? $post['excerpt'] : mb_strimwidth(strip_tags($post['content']), 0, 160, '...'));
        $featuredImage = !empty($post['featured_image']) ? asset($post['featured_image']) : null;

        $seoOptions = [
            'title' => $metaTitle,
            'description' => $metaDescription,
            'image' => $featuredImage,
            'url' => url('blog/' . $post['slug']),
            'type' => 'article'
        ];

        $this->renderView('web/blog_detail', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'recentPosts' => $recentPosts,
            'seoOptions' => $seoOptions
        ]);
    }
}
