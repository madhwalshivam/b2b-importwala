<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\BlogPost;

class BlogFrontendController extends Controller {
    private BlogPost $blogModel;

    public function __construct() {
        parent::__construct();
        $this->blogModel = new BlogPost();
    }

    /**
     * Blog Listing Page (/blog)
     */
    public function index(): string {
        $page = max(1, (int)$this->request->get('page', 1));
        $search = trim($this->request->get('q', ''));

        $where = "status = 'published'";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (title LIKE ? OR content LIKE ? OR author_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $allPublished = $this->blogModel->getRecentPublished(12);
        $latestPost = null;
        $editorPicks = [];
        $gridPosts = [];

        if (empty($search) && $page === 1) {
            $latestPost = !empty($allPublished[0]) ? $allPublished[0] : null;
            $editorPicks = array_slice($allPublished, 1, 3);
            $gridPosts = array_slice($allPublished, 4);

            $pagination = [
                'items' => $gridPosts,
                'current_page' => 1,
                'last_page' => 1,
                'total' => count($allPublished)
            ];
        } else {
            $pagination = $this->blogModel->paginate($page, 9, $where, $params, "published_at DESC, id DESC");
            $gridPosts = $pagination['items'];
        }

        $seoOptions = [
            'title' => 'Mudsor Blog & Electric Scooter Insights',
            'description' => 'The best tips, tricks & news about electric scooter accessories, crash guards, and maintenance.',
            'url' => url('blog'),
            'type' => 'website'
        ];

        return $this->render('storefront/blog/index', [
            'posts' => $gridPosts,
            'pagination' => $pagination,
            'latestPost' => $latestPost,
            'editorPicks' => $editorPicks,
            'allPublished' => $allPublished,
            'searchQuery' => $search,
            'seoOptions' => $seoOptions
        ]);
    }

    /**
     * Single Blog Post Page (/blog/{slug})
     */
    public function show(string $slug): string {
        $slug = trim($slug);
        $post = $this->blogModel->findPublishedBySlug($slug);

        if (!$post) {
            $this->response->setStatusCode(404);
            return $this->render('errors/404');
        }

        // Increment view count
        $this->blogModel->incrementViews($post['id']);

        // Fetch related articles based on title/focus keyword relevance
        $relatedPosts = $this->blogModel->getRelatedPosts($post, 3);

        // Fetch recent posts for sidebar (excluding current post ID)
        $recentPosts = $this->blogModel->getRecentPublished(5, [(int)$post['id']]);

        $metaTitle = !empty($post['meta_title']) ? $post['meta_title'] : $post['title'];
        $metaDescription = !empty($post['meta_description']) ? $post['meta_description'] : (!empty($post['excerpt']) ? $post['excerpt'] : mb_strimwidth(strip_tags($post['content']), 0, 160, '...'));
        $featuredImage = !empty($post['featured_image']) ? asset($post['featured_image']) : null;

        $seoOptions = [
            'title' => $metaTitle,
            'description' => $metaDescription,
            'image' => $featuredImage,
            'url' => url('blog/' . $post['slug']),
            'type' => 'article',
            'article' => $post
        ];

        return $this->render('storefront/blog/show', [
            'post' => $post,
            'recentPosts' => $recentPosts,
            'relatedPosts' => $relatedPosts,
            'seoOptions' => $seoOptions
        ]);
    }
}
