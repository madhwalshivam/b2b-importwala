<?php
define('ROOT_PATH', __DIR__ . '/..');
$dir = ROOT_PATH . '/public/uploads/featured_categories';
$file = $dir . '/viewall-jewelry-making.jpg';

if (!file_exists($file)) {
    $url = 'https://images.unsplash.com/photo-1611591475140-4388cf34ff5d?auto=format&fit=crop&w=400&q=80';
    $context = stream_context_create(['http' => ['timeout' => 5, 'user_agent' => 'Mozilla/5.0']]);
    $img = @file_get_contents($url, false, $context);
    if ($img && strlen($img) > 1000) {
        file_put_contents($file, $img);
        echo "Downloaded viewall-jewelry-making.jpg\n";
    } else {
        $im = imagecreatetruecolor(400, 400);
        $bg = imagecolorallocate($im, 240, 242, 245);
        $text = imagecolorallocate($im, 55, 65, 81);
        imagefill($im, 0, 0, $bg);
        imagestring($im, 5, 20, 190, "Jewelry Making", $text);
        imagejpeg($im, $file, 85);
        imagedestroy($im);
        echo "Generated GD viewall-jewelry-making.jpg\n";
    }
} else {
    echo "viewall-jewelry-making.jpg exists!\n";
}
