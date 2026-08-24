<?php
define('ROOT_PATH', __DIR__ . '/..');

$uploadDir = ROOT_PATH . '/public/uploads/featured_categories';

$missing = [
    'bracelets.jpg' => [
        'name' => 'Beaded & Charm Bracelets',
        'url' => 'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?auto=format&fit=crop&w=400&q=80'
    ],
    'findings.jpg' => [
        'name' => 'Jewelry Findings & Clasps',
        'url' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=400&q=80'
    ],
    'pens-markers.jpg' => [
        'name' => 'Gel Pens & Markers',
        'url' => 'https://images.unsplash.com/photo-1583485088034-697b5bc54ccd?auto=format&fit=crop&w=400&q=80'
    ],
    'phone-cases.jpg' => [
        'name' => 'Phone Cases & Protectors',
        'url' => 'https://images.unsplash.com/photo-1580910051074-3eb694886505?auto=format&fit=crop&w=400&q=80'
    ]
];

foreach ($missing as $file => $data) {
    $filePath = $uploadDir . '/' . $file;
    if (!file_exists($filePath)) {
        echo "Creating {$file}... ";
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'user_agent' => 'Mozilla/5.0'
            ]
        ]);
        $img = @file_get_contents($data['url'], false, $context);
        if ($img && strlen($img) > 1000) {
            file_put_contents($filePath, $img);
            echo "Downloaded from web!\n";
        } else {
            // GD fallback
            $im = imagecreatetruecolor(400, 400);
            $bg = imagecolorallocate($im, 235, 238, 242);
            $text = imagecolorallocate($im, 31, 41, 55);
            imagefill($im, 0, 0, $bg);
            imagestring($im, 5, 20, 190, $data['name'], $text);
            imagejpeg($im, $filePath, 85);
            imagedestroy($im);
            echo "GD Image Generated!\n";
        }
    }
}
echo "Done!\n";
