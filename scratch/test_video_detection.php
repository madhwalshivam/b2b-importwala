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

$testUrls = [
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'https://youtu.be/dQw4w9WgXcQ',
    'https://www.youtube.com/shorts/dQw4w9WgXcQ',
    'https://www.instagram.com/reel/C3xX1234abc/',
    'https://www.instagram.com/p/C3xX1234abc',
    'https://www.facebook.com/watch/?v=123456789',
    'https://fb.watch/abcd123/',
    '/uploads/videos/vid_12345.mp4',
    'https://invalid-website.com/video123'
];

foreach ($testUrls as $url) {
    $type = (str_contains($url, '/uploads/')) ? 'upload' : 'link';
    $data = App\Models\HomepageVideo::getEmbedData($url, $type);
    echo "URL: {$url}\n";
    echo " -> Detected Platform: {$data['platform']}\n";
    echo " -> Is Valid: " . ($data['is_valid'] ? 'YES' : 'NO') . "\n";
    echo " -> Embed URL: {$data['embed_url']}\n";
    echo " -> Thumbnail: {$data['thumbnail']}\n";
    echo "--------------------------------------------------------\n";
}
