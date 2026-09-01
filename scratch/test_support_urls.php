<?php
$urls = [
    'http://localhost/importwala/support',
    'http://localhost/importwala/contact-us',
    'http://localhost/importwala/shipping-policy',
    'http://localhost/importwala/refund-policy',
    'http://localhost/importwala/cancellation-policy',
    'http://localhost/importwala/terms-and-conditions',
    'http://localhost/importwala/privacy-policy'
];

foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "URL: {$url} => HTTP {$code}\n";
}
