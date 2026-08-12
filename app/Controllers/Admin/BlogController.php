<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\BlogPost;

class BlogController extends Controller {
    private BlogPost $blogModel;

    public function __construct() {
        parent::__construct();
        $this->blogModel = new BlogPost();
    }

    /**
     * List all blog posts in Admin
     */
    public function index(): string {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $status = trim($this->request->get('status', ''));
        $search = trim($this->request->get('q', ''));
        $page = max(1, (int)$this->request->get('page', 1));

        $where = "1=1";
        $params = [];

        if (!empty($status) && in_array($status, ['published', 'draft'])) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $where .= " AND (title LIKE ? OR content LIKE ? OR author_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $pagination = $this->blogModel->paginate($page, 15, $where, $params, "created_at DESC");

        return $this->render('admin/blogs/index', [
            'posts' => $pagination['items'],
            'pagination' => $pagination,
            'statusFilter' => $status,
            'searchQuery' => $search
        ]);
    }

    /**
     * Show Create Blog Post Form
     */
    public function create(): string {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $currentUser = Auth::user();
        $defaultAuthor = $currentUser['name'] ?? 'Mudsor Team';

        return $this->render('admin/blogs/form', [
            'title' => 'Add New Blog Post',
            'post' => null,
            'defaultAuthor' => $defaultAuthor
        ]);
    }

    /**
     * Store new Blog Post
     */
    /**
     * Store new Blog Post
     */
    public function store(): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $title = trim($this->request->input('title', ''));
        $customSlug = trim($this->request->input('slug', ''));
        $excerpt = trim($this->request->input('excerpt', ''));
        $content = $_POST['content'] ?? '';
        $metaTitle = trim($this->request->input('meta_title', ''));
        $metaDescription = trim($this->request->input('meta_description', ''));
        $focusKeyword = trim($this->request->input('focus_keyword', ''));
        $authorName = trim($this->request->input('author_name', ''));
        $featuredImageAlt = trim($this->request->input('featured_image_alt', ''));
        $status = in_array($this->request->input('status'), ['published', 'draft']) ? $this->request->input('status') : 'draft';

        if (empty($title)) {
            $_SESSION['flash_error'] = 'Blog title is required.';
            $this->redirect(url('admin/blogs/create'));
            return;
        }

        // Handle Featured Image Upload
        $featuredImagePath = null;
        if (!empty($_FILES['featured_image']['name'])) {
            $uploadResult = $this->handleFileUpload($_FILES['featured_image']);
            if ($uploadResult['error']) {
                $_SESSION['flash_error'] = $uploadResult['error'];
                $this->redirect(url('admin/blogs/create'));
                return;
            }
            $featuredImagePath = $uploadResult['path'];
        }

        // Featured Image Alt Text Auto-Fallback (Use title if alt text is not provided)
        if (empty($featuredImageAlt)) {
            $featuredImageAlt = $title;
        }

        // Generate unique clean slug
        $uniqueSlug = $this->blogModel->generateUniqueSlug($title, null, $customSlug);

        // Auto-generate excerpt if empty
        if (empty($excerpt) && !empty($content)) {
            $plainText = strip_tags($content);
            $excerpt = mb_strimwidth($plainText, 0, 160, '...');
        }

        $currentUser = Auth::user();
        if (empty($authorName)) {
            $authorName = $currentUser['name'] ?? 'Mudsor Team';
        }

        $publishedAt = ($status === 'published') ? date('Y-m-d H:i:s') : null;

        try {
            $postId = $this->blogModel->insert([
                'title' => $title,
                'slug' => $uniqueSlug,
                'excerpt' => $excerpt,
                'content' => $content,
                'featured_image' => $featuredImagePath,
                'featured_image_alt' => $featuredImageAlt,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'focus_keyword' => $focusKeyword,
                'author_name' => $authorName,
                'status' => $status,
                'published_at' => $publishedAt
            ]);

            if (function_exists('activity_log')) {
                activity_log('Create', 'Blogs', $postId, 'Created blog post: ' . $title);
            }

            $_SESSION['flash_success'] = 'Blog post created successfully!';
            $this->redirect(url('admin/blogs'));
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to create blog post: ' . $e->getMessage();
            $this->redirect(url('admin/blogs/create'));
        }
    }

    /**
     * Show Edit Blog Post Form
     */
    public function edit(mixed $id): string {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $post = $this->blogModel->find((int)$id);
        if (!$post) {
            $_SESSION['flash_error'] = 'Blog post not found.';
            $this->redirect(url('admin/blogs'));
        }

        return $this->render('admin/blogs/form', [
            'title' => 'Edit Blog Post',
            'post' => $post,
            'defaultAuthor' => $post['author_name'] ?? 'Mudsor Team'
        ]);
    }

    /**
     * Update existing Blog Post
     */
    public function update(mixed $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $postId = (int)$id;
        $post = $this->blogModel->find($postId);
        if (!$post) {
            $_SESSION['flash_error'] = 'Blog post not found.';
            $this->redirect(url('admin/blogs'));
            return;
        }

        $title = trim($this->request->input('title', ''));
        $customSlug = trim($this->request->input('slug', ''));
        $excerpt = trim($this->request->input('excerpt', ''));
        $content = $_POST['content'] ?? '';
        $metaTitle = trim($this->request->input('meta_title', ''));
        $metaDescription = trim($this->request->input('meta_description', ''));
        $focusKeyword = trim($this->request->input('focus_keyword', ''));
        $authorName = trim($this->request->input('author_name', ''));
        $featuredImageAlt = trim($this->request->input('featured_image_alt', ''));
        $status = in_array($this->request->input('status'), ['published', 'draft']) ? $this->request->input('status') : 'draft';

        if (empty($title)) {
            $_SESSION['flash_error'] = 'Blog title is required.';
            $this->redirect(url("admin/blogs/edit/{$postId}"));
            return;
        }

        $featuredImagePath = $post['featured_image'];
        if (!empty($_FILES['featured_image']['name'])) {
            $uploadResult = $this->handleFileUpload($_FILES['featured_image']);
            if ($uploadResult['error']) {
                $_SESSION['flash_error'] = $uploadResult['error'];
                $this->redirect(url("admin/blogs/edit/{$postId}"));
                return;
            }

            // Remove old featured image file if present
            if (!empty($post['featured_image'])) {
                $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 3);
                $oldFilePath = $rootPath . '/public/' . ltrim($post['featured_image'], '/');
                if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }

            $featuredImagePath = $uploadResult['path'];
        }

        // Featured Image Alt Text Auto-Fallback (Use title if alt text is not provided)
        if (empty($featuredImageAlt)) {
            $featuredImageAlt = $title;
        }

        // Generate unique clean slug (excluding current post ID)
        $uniqueSlug = $this->blogModel->generateUniqueSlug($title, $postId, $customSlug);

        // Auto-generate excerpt if empty
        if (empty($excerpt) && !empty($content)) {
            $plainText = strip_tags($content);
            $excerpt = mb_strimwidth($plainText, 0, 160, '...');
        }

        if (empty($authorName)) {
            $currentUser = Auth::user();
            $authorName = $currentUser['name'] ?? 'Mudsor Team';
        }

        $publishedAt = $post['published_at'];
        if ($status === 'published' && empty($publishedAt)) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        try {
            $this->blogModel->update($postId, [
                'title' => $title,
                'slug' => $uniqueSlug,
                'excerpt' => $excerpt,
                'content' => $content,
                'featured_image' => $featuredImagePath,
                'featured_image_alt' => $featuredImageAlt,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'focus_keyword' => $focusKeyword,
                'author_name' => $authorName,
                'status' => $status,
                'published_at' => $publishedAt
            ]);

            if (function_exists('activity_log')) {
                activity_log('Update', 'Blogs', $postId, 'Updated blog post: ' . $title);
            }

            $_SESSION['flash_success'] = 'Blog post updated successfully!';
            $this->redirect(url("admin/blogs/edit/{$postId}"));
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to update blog post: ' . $e->getMessage();
            $this->redirect(url("admin/blogs/edit/{$postId}"));
        }
    }

    /**
     * Delete Blog Post
     */
    public function delete(mixed $id): void {
        if (!Auth::check()) $this->redirect(url('admin/login'));

        $postId = (int)$id;
        $post = $this->blogModel->find($postId);

        if ($post) {
            // Delete associated featured image file from disk
            if (!empty($post['featured_image'])) {
                $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 3);
                $filePath = $rootPath . '/public/' . ltrim($post['featured_image'], '/');
                if (file_exists($filePath) && is_file($filePath)) {
                    @unlink($filePath);
                }
            }

            $this->blogModel->delete($postId);
            if (function_exists('activity_log')) {
                activity_log('Delete', 'Blogs', $postId, 'Deleted blog post: ' . $post['title']);
            }
            $_SESSION['flash_success'] = 'Blog post deleted successfully.';
        } else {
            $_SESSION['flash_error'] = 'Blog post not found.';
        }

        $this->redirect(url('admin/blogs'));
    }

    /**
     * WYSIWYG Editor Image Upload Handler (TinyMCE)
     */
    public function uploadImage(): void {
        if (!Auth::check()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');

        if (empty($_FILES['file']['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No image file uploaded.']);
            return;
        }

        $uploadResult = $this->handleFileUpload($_FILES['file'], 'uploads/blogs/content/');
        if ($uploadResult['error']) {
            http_response_code(400);
            echo json_encode(['error' => $uploadResult['error']]);
            return;
        }

        echo json_encode([
            'location' => asset($uploadResult['path'])
        ]);
    }

    /**
     * Private helper to handle image uploads safely
     */
    private function handleFileUpload(array $file, string $subFolder = 'uploads/blogs/'): array {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];
        $maxSizeBytes = 5 * 1024 * 1024; // 5 MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['path' => null, 'error' => 'File upload error code: ' . $file['error']];
        }

        if ($file['size'] > $maxSizeBytes) {
            return ['path' => null, 'error' => 'File size exceeds maximum limit of 5MB.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            return ['path' => null, 'error' => 'Invalid image format. Allowed: JPG, PNG, WEBP, GIF, AVIF.'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (empty($ext) || $ext === 'tmp') {
            $mimeMap = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                'image/avif' => 'avif'
            ];
            $ext = $mimeMap[$mimeType] ?? 'jpg';
        }

        $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 3);
        $targetDir = $rootPath . '/public/' . trim($subFolder, '/') . '/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = 'blog_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $targetPath = $targetDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['path' => trim($subFolder, '/') . '/' . $filename, 'error' => null];
        }

        return ['path' => null, 'error' => 'Failed to save uploaded file.'];
    }
}
