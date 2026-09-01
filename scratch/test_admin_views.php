<?php
$urls = [
    'http://localhost/importwala/admin/inquiries',
    'http://localhost/importwala/admin/rfq'
];

foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "URL: {$url} => HTTP {$code}\n";
}
