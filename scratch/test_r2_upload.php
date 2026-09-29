<?php
/**
 * R2 Upload Debug Test
 * Run: http://localhost/importwala/scratch/test_r2_upload.php
 */

$accessKeyId    = '59758d670c5c4a720d5fbfa150a81571';
$secretAccessKey = '54fabc5b173afdb1c9ba2fa37902fb1180220c6bd78f7c2d3ae3c0b0850d494e';
$bucketName     = 'importwala-images';
$endpoint       = 'https://01e0ff8f64110937bdefd6c0f82bc3c6.r2.cloudflarestorage.com';

// Create a tiny test image (1x1 red pixel PNG)
$testImageData = base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg=='
);
$r2Key = 'test_debug_' . time() . '.png';

// ---- Build signed request ----
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

echo "<pre style='font-family:monospace; font-size:13px;'>";
echo "=== R2 Upload Debug Test ===\n\n";
echo "URL      : $url\n";
echo "Host     : $host\n";
echo "Bucket   : $bucketName\n";
echo "Key      : $r2Key\n";
echo "amzDate  : $amzDate\n";
echo "Payload  : " . strlen($testImageData) . " bytes\n\n";

// ---- Send with FULL timeout (no 500ms cap) ----
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
curl_close($ch);

echo "HTTP Code  : $httpCode\n";
echo "cURL Error : " . ($curlErr ?: 'none') . "\n";
echo "Response   :\n" . htmlspecialchars($response) . "\n\n";

if ($httpCode === 200 || $httpCode === 201) {
    echo "SUCCESS! Image uploaded to R2!\n";
} else {
    echo "FAILED! HTTP $httpCode\n";
    if ($curlErr) {
        echo "Reason: cURL error - $curlErr\n";
    } elseif (str_contains($response, 'InvalidAccessKeyId')) {
        echo "Reason: Access Key galat hai\n";
    } elseif (str_contains($response, 'SignatureDoesNotMatch')) {
        echo "Reason: Secret Key galat hai ya signing error\n";
    } elseif (str_contains($response, 'NoSuchBucket')) {
        echo "Reason: Bucket '$bucketName' exist nahi karta\n";
    } elseif (str_contains($response, 'AccessDenied')) {
        echo "Reason: Token ka Write permission nahi hai\n";
    } elseif ($httpCode === 0) {
        echo "Reason: Connection timeout ya network issue\n";
    }
}

echo "</pre>";
