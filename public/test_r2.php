<?php
/**
 * R2 Upload Debug Test - Direct access via /importwala/public/test_r2.php
 */
// Include autoloader if needed
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) require $autoload;

$accessKeyId    = '59758d670c5c4a720d5fbfa150a81571';
$secretAccessKey = '54fabc5b173afdb1c9ba2fa37902fb1180220c6bd78f7c2d3ae3c0b0850d494e';
$bucketName     = 'importwala-images';
$endpoint       = 'https://01e0ff8f64110937bdefd6c0f82bc3c6.r2.cloudflarestorage.com';

// 1x1 pixel PNG
$testImageData = base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg=='
);
$r2Key = 'test_debug_' . time() . '.png';

$host     = parse_url($endpoint, PHP_URL_HOST);
$uri      = '/' . $bucketName . '/' . $r2Key;
$url      = $endpoint . $uri;

$region   = 'auto';
$service  = 's3';
$timestamp = time();
$date     = gmdate('Ymd', $timestamp);
$amzDate  = gmdate('Ymd\THis\Z', $timestamp);

$payloadHash = hash('sha256', $testImageData);

$canonicalHeaders = "host:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$amzDate}\n";
$signedHeaders    = 'host;x-amz-content-sha256;x-amz-date';
$canonicalRequest = "PUT\n{$uri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";

$credentialScope = "{$date}/{$region}/{$service}/aws4_request";
$stringToSign    = "AWS4-HMAC-SHA256\n{$amzDate}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

$kDate    = hash_hmac('sha256', $date, 'AWS4' . $secretAccessKey, true);
$kRegion  = hash_hmac('sha256', $region, $kDate, true);
$kService = hash_hmac('sha256', $service, $kRegion, true);
$kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
$signature = hash_hmac('sha256', $stringToSign, $kSigning);

$authHeader = "AWS4-HMAC-SHA256 Credential={$accessKeyId}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

$headers = [
    'Host: ' . $host,
    'x-amz-date: ' . $amzDate,
    'x-amz-content-sha256: ' . $payloadHash,
    'Authorization: ' . $authHeader,
    'Content-Type: image/png',
    'Content-Length: ' . strlen($testImageData),
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST  => 'PUT',
    CURLOPT_POSTFIELDS     => $testImageData,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);


echo "<pre style='font-size:14px;font-family:monospace;padding:20px;'>";
echo "=== R2 Upload Debug ===\n\n";
echo "URL       : $url\n";
echo "Bucket    : $bucketName\n";
echo "Key       : $r2Key\n";
echo "amzDate   : $amzDate\n\n";
echo "HTTP Code : $httpCode\n";
echo "cURL Err  : " . ($curlErr ?: 'none') . "\n";
echo "Response  :\n" . htmlspecialchars($response ?: '(empty)') . "\n\n";

if ($httpCode === 200 || $httpCode === 201) {
    echo "✅ SUCCESS! Image R2 mein upload ho gayi!\n";
} else {
    echo "❌ FAILED!\n";
    if ($curlErr)                                       echo "Wajah: cURL error - $curlErr\n";
    elseif (str_contains($response, 'InvalidAccessKeyId'))  echo "Wajah: Access Key galat hai\n";
    elseif (str_contains($response, 'SignatureDoesNotMatch')) echo "Wajah: Secret Key galat ya signing issue\n";
    elseif (str_contains($response, 'NoSuchBucket'))    echo "Wajah: Bucket nahi mila\n";
    elseif (str_contains($response, 'AccessDenied'))    echo "Wajah: Token mein WRITE permission nahi hai!\n";
    elseif ($httpCode === 0)                            echo "Wajah: Network/timeout issue\n";
}
echo "</pre>";
