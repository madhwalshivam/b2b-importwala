<?php

$url = 'http://localhost/importwala/api/visual-search';
$imgPath = __DIR__ . '/../public/assets/images/body_cover_icon.png';

$ch = curl_init($url);
$cfile = new \CURLFile($imgPath, 'image/png', 'body_cover_icon.png');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['photo' => $cfile]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$res = curl_exec($ch);
curl_close($ch);

echo "API Upload Visual Search Response:\n";
echo $res . "\n";
