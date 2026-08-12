<?php
namespace App\Models;

use App\Core\Model;

class HomepageVideo extends Model {
    protected string $table = 'homepage_videos';

    public function getActiveVideos(): array {
        return $this->all('display_order ASC, id DESC');
    }

    /**
     * Detect platform from video URL (youtube, instagram, facebook, or unknown)
     */
    public static function detectPlatform(string $url): string {
        $url = trim($url);
        if (empty($url)) return 'unknown';

        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url)) {
            return 'youtube';
        }
        if (preg_match('/(?:instagram\.com|instagr\.am)\/(?:p|reel|tv)\/([a-zA-Z0-9_\-]+)/i', $url)) {
            return 'instagram';
        }
        if (preg_match('/(?:facebook\.com\/(?:.*\/videos\/|video\.php|watch|reel|share\/)|fb\.watch\/)/i', $url)) {
            return 'facebook';
        }

        return 'unknown';
    }

    /**
     * Get Embed URL and Platform Details
     */
    public static function getEmbedData(string $url, string $videoType = 'link'): array {
        $url = trim($url);
        if ($videoType === 'upload') {
            return [
                'platform'  => 'upload',
                'embed_url' => asset(ltrim($url, '/')),
                'is_valid'  => true,
                'thumbnail' => ''
            ];
        }

        $platform = self::detectPlatform($url);

        if ($platform === 'youtube') {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $m);
            $id = $m[1] ?? '';
            return [
                'platform'  => 'youtube',
                'embed_url' => 'https://www.youtube.com/embed/' . $id . '?autoplay=1',
                'is_valid'  => !empty($id),
                'thumbnail' => !empty($id) ? 'https://img.youtube.com/vi/' . $id . '/hqdefault.jpg' : ''
            ];
        }

        if ($platform === 'instagram') {
            preg_match('/(?:instagram\.com|instagr\.am)\/(?:p|reel|tv)\/([a-zA-Z0-9_\-]+)/i', $url, $m);
            $shortcode = $m[1] ?? '';
            return [
                'platform'  => 'instagram',
                'embed_url' => 'https://www.instagram.com/p/' . $shortcode . '/embed/',
                'is_valid'  => !empty($shortcode),
                'thumbnail' => ''
            ];
        }

        if ($platform === 'facebook') {
            return [
                'platform'  => 'facebook',
                'embed_url' => 'https://www.facebook.com/plugins/video.php?href=' . rawurlencode($url) . '&show_text=0&autoplay=1',
                'is_valid'  => true,
                'thumbnail' => ''
            ];
        }

        return [
            'platform'  => 'unknown',
            'embed_url' => '',
            'is_valid'  => false,
            'thumbnail' => ''
        ];
    }

    /**
     * Legacy YouTube Embed URL helper
     */
    public static function getEmbedUrl(string $url): string {
        $embedData = self::getEmbedData($url, 'link');
        return $embedData['embed_url'] ?: $url;
    }

    public static function getYouTubeThumbnail(string $url): string {
        $embedData = self::getEmbedData($url, 'link');
        return $embedData['thumbnail'] ?? '';
    }
}

