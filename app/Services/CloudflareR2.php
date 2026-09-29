<?php
namespace App\Services;

class CloudflareR2
{
    private string $accessKeyId = '59758d670c5c4a720d5fbfa150a81571';
    private string $secretAccessKey = '54fabc5b173afdb1c9ba2fa37902fb1180220c6bd78f7c2d3ae3c0b0850d494e';
    private string $bucketName = 'importwala-images';
    private string $endpoint = 'https://01e0ff8f64110937bdefd6c0f82bc3c6.r2.cloudflarestorage.com';
    private string $uploadFolder = '';

    public function upload(array $file): string
    {
        if (empty($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return '';
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uniqueName = time() . '_' . uniqid() . '.' . ($extension ?: 'jpg');
        $r2Key = $this->uploadFolder ? ($this->uploadFolder . '/' . $uniqueName) : $uniqueName;

        // 1. Save local copy instantly for 0ms UI latency
        $localDir = __DIR__ . '/../../public/uploads' . ($this->uploadFolder ? ('/' . $this->uploadFolder) : '');
        if (!is_dir($localDir)) {
            @mkdir($localDir, 0777, true);
        }
        $localPath = $localDir . '/' . $uniqueName;

        // Try move_uploaded_file or copy fallback
        $saved = false;
        if (is_uploaded_file($file['tmp_name'])) {
            $saved = @move_uploaded_file($file['tmp_name'], $localPath);
        }
        if (!$saved) {
            $saved = @copy($file['tmp_name'], $localPath);
        }

        if (!$saved) {
            return '';
        }

        $localUrl = '/uploads/' . ($this->uploadFolder ? ($this->uploadFolder . '/') : '') . $uniqueName;

        // 2. Non-blocking Cloudflare R2 Upload (Strict 500ms timeout cap so UI never lags)
        $this->uploadToR2Fast($localPath, $r2Key, $file['type'] ?: 'image/jpeg');

        return $localUrl;
    }

    private function uploadToR2Fast(string $filePath, string $r2Key, string $contentType): bool
    {
        $host = parse_url($this->endpoint, PHP_URL_HOST);
        $uri = '/' . $this->bucketName . '/' . ltrim($r2Key, '/');
        $url = $this->endpoint . $uri;

        $fileData = @file_get_contents($filePath);
        if ($fileData === false)
            return false;

        $region = 'auto';
        $service = 's3';
        $timestamp = time();
        $date = gmdate('Ymd', $timestamp);
        $amzDate = gmdate('Ymd\THis\Z', $timestamp);

        $payloadHash = hash('sha256', $fileData);

        // Canonical Request
        $canonicalHeaders = "host:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$amzDate}\n";
        $signedHeaders = "host;x-amz-content-sha256;x-amz-date";
        $canonicalRequest = "PUT\n{$uri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";

        // Credential Scope & String to Sign
        $credentialScope = "{$date}/{$region}/{$service}/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$amzDate}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

        // Calculate Signature
        $kDate = hash_hmac('sha256', $date, 'AWS4' . $this->secretAccessKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorizationHeader = "AWS4-HMAC-SHA256 Credential={$this->accessKeyId}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $headers = [
            'Host: ' . $host,
            'x-amz-date: ' . $amzDate,
            'x-amz-content-sha256: ' . $payloadHash,
            'Authorization: ' . $authorizationHeader,
            'Content-Type: ' . $contentType,
            'Content-Length: ' . strlen($fileData)
        ];

        $ch = curl_init($url);
        $httpMethod = 'PUT';
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $httpMethod,
            CURLOPT_POSTFIELDS => $fileData,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOSIGNAL => 1,
            CURLOPT_CONNECTTIMEOUT_MS => 1000,
            CURLOPT_TIMEOUT_MS => 5000,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return ($httpCode === 200 || $httpCode === 201);
    }

    /**
     * Upload a file from a local path directly to R2.
     * Used by the central upload_to_r2() helper in Functions.php.
     */
    public function uploadFile(string $localPath, string $r2Key, string $contentType = 'image/jpeg'): bool
    {
        if (!file_exists($localPath)) {
            return false;
        }

        $fakeFile = [
            'tmp_name' => $localPath,
            'name'     => basename($localPath),
            'type'     => $contentType,
            'size'     => filesize($localPath),
        ];

        return $this->uploadToR2Fast($localPath, $r2Key, $contentType);
    }

    /**
     * Delete an object from R2.
     */
    public function deleteObject(string $r2Key): bool
    {
        $host = parse_url($this->endpoint, PHP_URL_HOST);
        $uri = '/' . $this->bucketName . '/' . ltrim($r2Key, '/');
        $url = $this->endpoint . $uri;

        $region = 'auto';
        $service = 's3';
        $timestamp = time();
        $date = gmdate('Ymd', $timestamp);
        $amzDate = gmdate('Ymd\THis\Z', $timestamp);

        $payloadHash = hash('sha256', '');

        // Canonical Request
        $canonicalHeaders = "host:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$amzDate}\n";
        $signedHeaders = "host;x-amz-content-sha256;x-amz-date";
        $canonicalRequest = "DELETE\n{$uri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";

        // Credential Scope & String to Sign
        $credentialScope = "{$date}/{$region}/{$service}/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$amzDate}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

        // Calculate Signature
        $kDate = hash_hmac('sha256', $date, 'AWS4' . $this->secretAccessKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorizationHeader = "AWS4-HMAC-SHA256 Credential={$this->accessKeyId}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $headers = [
            'Host: ' . $host,
            'x-amz-date: ' . $amzDate,
            'x-amz-content-sha256: ' . $payloadHash,
            'Authorization: ' . $authorizationHeader
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOSIGNAL => 1,
            CURLOPT_CONNECTTIMEOUT_MS => 1000,
            CURLOPT_TIMEOUT_MS => 5000,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return ($httpCode === 204 || $httpCode === 200);
    }
}
