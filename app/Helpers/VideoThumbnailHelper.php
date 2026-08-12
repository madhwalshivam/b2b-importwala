<?php
namespace App\Helpers;

class VideoThumbnailHelper {

    /**
     * Find FFmpeg binary path on system if available
     */
    public static function findFFmpegPath(): ?string {
        $paths = [
            'ffmpeg',
            'C:\\ffmpeg\\bin\\ffmpeg.exe',
            'C:\\Program Files\\ffmpeg\\bin\\ffmpeg.exe',
            'C:\\xampp\\ffmpeg\\bin\\ffmpeg.exe',
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg'
        ];

        foreach ($paths as $path) {
            $cmd = (PHP_OS_FAMILY === 'Windows' && $path === 'ffmpeg')
                ? 'where.exe ffmpeg 2>nul'
                : escapeshellcmd($path) . ' -version 2>&1';
            
            $output = [];
            $returnCode = 1;
            @exec($cmd, $output, $returnCode);
            if ($returnCode === 0 && !empty($output)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Extract frame using FFmpeg CLI if available
     * Saves to $uploadDir with name $outputFilename
     */
    public static function generateFromFFmpeg(string $videoAbsolutePath, string $uploadDir, string $outputFilename): ?string {
        if (!file_exists($videoAbsolutePath)) return null;

        $ffmpeg = self::findFFmpegPath();
        if (!$ffmpeg) return null;

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $outputPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $outputFilename;

        // Extract frame at 1.0 second mark (-ss 00:00:01)
        $cmd = escapeshellcmd($ffmpeg) . ' -y -ss 00:00:01 -i ' . escapeshellarg($videoAbsolutePath) . ' -vframes 1 -q:v 2 ' . escapeshellarg($outputPath) . ' 2>&1';

        $output = [];
        $returnCode = 1;
        @exec($cmd, $output, $returnCode);

        if (file_exists($outputPath) && filesize($outputPath) > 0) {
            return $outputFilename;
        }

        return null;
    }

    /**
     * Save base64 thumbnail captured client-side via HTML5 Canvas
     */
    public static function saveBase64Thumbnail(string $base64Data, string $uploadDir, string $outputFilename): ?string {
        if (empty($base64Data)) return null;

        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data)) {
            $data = substr($base64Data, strpos($base64Data, ',') + 1);
            $data = base64_decode($data);
            if ($data === false) return null;
        } else {
            return null;
        }

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $outputPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $outputFilename;
        file_put_contents($outputPath, $data);

        if (file_exists($outputPath) && filesize($outputPath) > 0) {
            return $outputFilename;
        }

        return null;
    }

    /**
     * Helper to auto-generate video thumbnail using FFmpeg CLI or Canvas Base64 fallback
     */
    public static function processAutoThumbnail(string $videoAbsolutePath, ?string $base64Data, string $uploadDir, string $filenamePrefix): ?string {
        $thumbFilename = $filenamePrefix . '_auto_thumb.jpg';

        // 1. Try FFmpeg extraction first
        $ffmpegResult = self::generateFromFFmpeg($videoAbsolutePath, $uploadDir, $thumbFilename);
        if ($ffmpegResult) {
            return $thumbFilename;
        }

        // 2. Fallback to client-side HTML5 Canvas base64 frame
        if (!empty($base64Data)) {
            $base64Result = self::saveBase64Thumbnail($base64Data, $uploadDir, $thumbFilename);
            if ($base64Result) {
                return $thumbFilename;
            }
        }

        return null;
    }

    /**
     * Resolve effective thumbnail URL across site
     * Priority: manual cover photo -> auto-generated thumbnail -> YouTube default -> generic placeholder
     */
    public static function resolveThumbnail(?string $manualThumb, ?string $autoThumb, ?string $videoUrl = '', ?string $placeholder = ''): string {
        if (!empty($manualThumb)) {
            if (str_starts_with($manualThumb, 'http://') || str_starts_with($manualThumb, 'https://')) {
                return $manualThumb;
            }
            return asset(ltrim($manualThumb, '/'));
        }

        if (!empty($autoThumb)) {
            if (str_starts_with($autoThumb, 'http://') || str_starts_with($autoThumb, 'https://')) {
                return $autoThumb;
            }
            return asset(ltrim($autoThumb, '/'));
        }

        if (!empty($videoUrl) && class_exists('\App\Models\HomepageVideo')) {
            $platform = \App\Models\HomepageVideo::detectPlatform($videoUrl);
            if ($platform === 'youtube') {
                $ytThumb = \App\Models\HomepageVideo::getYouTubeThumbnail($videoUrl);
                if (!empty($ytThumb)) return $ytThumb;
            }
        }

        if (!empty($placeholder)) {
            return asset(ltrim($placeholder, '/'));
        }

        return asset('assets/images/placeholder.jpg');
    }
}
