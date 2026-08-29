<?php
$url = 'http://localhost/importwala/api/visual-search';
$imgPath = __DIR__ . '/../public/uploads/products/sunglasses.jpg';

$cfile = new CURLFile($imgPath, 'image/jpeg', 'sunglasses.jpg');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['photo' => $cfile]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "POST Status Code: {$httpCode}\n";
echo "POST Response:\n" . substr($res, 0, 1000) . "\n";
