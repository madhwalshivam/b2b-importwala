<?php
define('ROOT_PATH', dirname(__DIR__));

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

$url = 'https://cbu01.alicdn.com/img/ibank/O1CN01hMajmj1VY9sPbnPkp_!!2213264682664-0-cib.jpg';

$ch = curl_init('http://127.0.0.1:5005/embed');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['image_path' => $url]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 8);

$response = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$code}\n";
echo "CURL Error: {$err}\n";
echo "Response: {$response}\n";
