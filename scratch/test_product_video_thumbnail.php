<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});
require_once __DIR__ . '/../app/Helpers/Functions.php';

use App\Helpers\VideoThumbnailHelper;

echo "=== 1. TEST POSTER RESOLUTION ORDER ===\n";

// Case 1: Manual Cover set
$res1 = VideoThumbnailHelper::resolveThumbnail('/uploads/videos/manual_cover.jpg', '/uploads/videos/auto_frame.jpg', 'https://youtube.com/watch?v=123');
echo "Case 1 (Manual Cover Present): {$res1}\n";

// Case 2: Only Auto Cover set
$res2 = VideoThumbnailHelper::resolveThumbnail('', '/uploads/videos/auto_frame.jpg', 'https://youtube.com/watch?v=123');
echo "Case 2 (Only Auto Frame Present): {$res2}\n";

// Case 3: YouTube Video URL, No manual/auto image set
$res3 = VideoThumbnailHelper::resolveThumbnail('', '', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
echo "Case 3 (YouTube Auto Poster): {$res3}\n";

// Case 4: No image, invalid video
$res4 = VideoThumbnailHelper::resolveThumbnail('', '', '');
echo "Case 4 (Generic Placeholder): {$res4}\n";

echo "\n=== 2. TEST BASE64 FRAME GENERATION ===\n";
// Create a 1x1 red pixel JPEG base64 string for testing
$testBase64 = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';
$uploadDir = __DIR__ . '/../public/uploads/videos/';
$savedName = VideoThumbnailHelper::saveBase64Thumbnail($testBase64, $uploadDir, 'test_unit_auto_thumb.jpg');
echo "Base64 Frame Saved File: " . ($savedName ? $savedName : 'FAILED') . "\n";
if ($savedName && file_exists($uploadDir . $savedName)) {
    echo "File Size: " . filesize($uploadDir . $savedName) . " bytes\n";
    @unlink($uploadDir . $savedName);
}

echo "\nTests completed successfully!\n";
