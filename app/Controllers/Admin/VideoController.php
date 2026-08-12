<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\HomepageVideo;

class VideoController extends Controller {

    protected HomepageVideo $videoModel;

    public function __construct() {
        parent::__construct();
        $this->videoModel = new HomepageVideo();
    }

    public function index(): string {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
        }

        $videos = $this->videoModel->getActiveVideos();

        return $this->render('admin/videos/index', [
            'videos' => $videos
        ]);
    }

    public function store(): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
        }

        $title       = trim($this->request->input('title', ''));
        $videoType   = $this->request->input('video_type', 'link');
        $videoUrl    = trim($this->request->input('video_url', ''));
        $productUrl  = trim($this->request->input('product_url', ''));
        $description = trim($this->request->input('description', ''));
        $displayOrder = (int)$this->request->input('display_order', 0);

        $thumbnailPath = '';

        // Validate URL if source is link
        if ($videoType !== 'upload') {
            if (empty($videoUrl)) {
                $this->setFlash('error', 'Please enter a video URL link.');
                $this->redirect(url('admin/videos'));
                return;
            }
            $platform = HomepageVideo::detectPlatform($videoUrl);
            if ($platform === 'unknown') {
                $this->setFlash('error', 'Please enter a valid YouTube, Instagram, or Facebook video link.');
                $this->redirect(url('admin/videos'));
                return;
            }
            $videoType = $platform;
        }

        // Handle Thumbnail Upload if provided
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/uploads/videos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = 'thumb_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['thumbnail']['name']);
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadDir . $fileName)) {
                $thumbnailPath = '/uploads/videos/' . $fileName;
            }
        }

        $autoThumbnailPath = '';

        // Handle Video File Upload if type is 'upload'
        if ($videoType === 'upload' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/uploads/videos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $baseName = 'vid_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['video_file']['name']);
            $videoAbsPath = $uploadDir . $baseName;
            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $videoAbsPath)) {
                $videoUrl = '/uploads/videos/' . $baseName;
                
                // Auto-generate thumbnail (FFmpeg / Canvas Base64 fallback)
                $base64Thumb = $_POST['auto_thumbnail_base64'] ?? '';
                $autoThumbName = \App\Helpers\VideoThumbnailHelper::processAutoThumbnail($videoAbsPath, $base64Thumb, $uploadDir, pathinfo($baseName, PATHINFO_FILENAME));
                if (!empty($autoThumbName)) {
                    $autoThumbnailPath = '/uploads/videos/' . $autoThumbName;
                }
            }
        }

        // Auto thumbnail for YouTube if thumbnail not manually uploaded
        if (empty($thumbnailPath) && $videoType === 'youtube' && !empty($videoUrl)) {
            $autoThumbnailPath = HomepageVideo::getYouTubeThumbnail($videoUrl);
        }

        if (!empty($title) && !empty($videoUrl)) {
            $this->videoModel->insert([
                'title'          => $title,
                'thumbnail'      => $thumbnailPath,
                'auto_thumbnail' => $autoThumbnailPath,
                'video_type'     => $videoType,
                'video_url'      => $videoUrl,
                'product_url'    => $productUrl,
                'description'    => $description,
                'display_order'  => $displayOrder
            ]);
            $this->setFlash('success', 'Video added successfully!');
        } else {
            $this->setFlash('error', 'Video Title and URL/File are required.');
        }

        $this->redirect(url('admin/videos'));
    }

    public function update(int $id): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
        }

        $video = $this->videoModel->find($id);
        if (!$video) {
            $this->setFlash('error', 'Video not found.');
            $this->redirect(url('admin/videos'));
        }

        $title        = trim($this->request->input('title', $video['title']));
        $videoType    = $this->request->input('video_type', $video['video_type']);
        $videoUrl     = trim($this->request->input('video_url', $video['video_url']));
        $productUrl   = trim($this->request->input('product_url', $video['product_url'] ?? ''));
        $description  = trim($this->request->input('description', $video['description']));
        $displayOrder = (int)$this->request->input('display_order', $video['display_order']);

        if ($videoType !== 'upload') {
            if (empty($videoUrl)) {
                $this->setFlash('error', 'Please enter a video URL link.');
                $this->redirect(url('admin/videos'));
                return;
            }
            $platform = HomepageVideo::detectPlatform($videoUrl);
            if ($platform === 'unknown') {
                $this->setFlash('error', 'Please enter a valid YouTube, Instagram, or Facebook video link.');
                $this->redirect(url('admin/videos'));
                return;
            }
            $videoType = $platform;
        }

        $thumbnailPath     = $video['thumbnail'];
        $autoThumbnailPath = $video['auto_thumbnail'] ?? '';

        // Handle Custom Thumbnail Upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/uploads/videos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = 'thumb_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['thumbnail']['name']);
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadDir . $fileName)) {
                $thumbnailPath = '/uploads/videos/' . $fileName;
            }
        }

        // Handle Video File Upload if type is 'upload'
        if ($videoType === 'upload' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/uploads/videos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $baseName = 'vid_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['video_file']['name']);
            $videoAbsPath = $uploadDir . $baseName;
            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $videoAbsPath)) {
                $videoUrl = '/uploads/videos/' . $baseName;

                // Auto-generate thumbnail on video re-upload
                $base64Thumb = $_POST['auto_thumbnail_base64'] ?? '';
                $autoThumbName = \App\Helpers\VideoThumbnailHelper::processAutoThumbnail($videoAbsPath, $base64Thumb, $uploadDir, pathinfo($baseName, PATHINFO_FILENAME));
                if (!empty($autoThumbName)) {
                    $autoThumbnailPath = '/uploads/videos/' . $autoThumbName;
                }
            }
        }

        if ($videoType === 'youtube' && !empty($videoUrl)) {
            $autoThumbnailPath = HomepageVideo::getYouTubeThumbnail($videoUrl);
        }

        $this->videoModel->update($id, [
            'title'          => $title,
            'thumbnail'      => $thumbnailPath,
            'auto_thumbnail' => $autoThumbnailPath,
            'video_type'     => $videoType,
            'video_url'      => $videoUrl,
            'product_url'    => $productUrl,
            'description'    => $description,
            'display_order'  => $displayOrder
        ]);

        $this->setFlash('success', 'Video updated successfully!');
        $this->redirect(url('admin/videos'));
    }

    public function delete(int $id): void {
        if (!Auth::check() || !Auth::canAccessModule('products')) {
            $this->redirect(url('admin/dashboard'));
        }

        $this->videoModel->delete($id);
        $this->setFlash('success', 'Video removed successfully!');
        $this->redirect(url('admin/videos'));
    }
}
