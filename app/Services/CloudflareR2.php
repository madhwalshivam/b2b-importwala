<?php
namespace App\Services;

class CloudflareR2 {
    private string $accessKeyId = '6a2ae9571e41761b1c649cee097b8d21';
    private string $secretAccessKey = 'b00b9b4a5a9a644aa1ca02215e556db52b64ba6c3f1e2a4174bd8c2a20c170cc';
    private string $bucketName = 'tape';
    private string $endpoint = 'https://17a03ed838cff7b48ee24c1876e145fc.r2.cloudflarestorage.com';
    private string $uploadFolder = 'dfix';

    public function upload(array $file): string {
        if (empty($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return '';
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uniqueName = time() . '_' . uniqid() . '.' . ($extension ?: 'jpg');
        $r2Key = $this->uploadFolder . '/' . $uniqueName;

        // 1. Save local copy instantly for 0ms UI latency
        $localDir = __DIR__ . '/../../public/uploads/' . $this->uploadFolder;
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

        $localUrl = '/uploads/' . $this->uploadFolder . '/' . $uniqueName;

        // 2. Non-blocking Cloudflare R2 Upload (Strict 500ms timeout cap so UI never lags)
        $this->uploadToR2Fast($localPath, $r2Key, $file['type'] ?: 'image/jpeg');

        return $localUrl;
    }

    private function uploadToR2Fast(string $filePath, string $r2Key, string $contentType): bool {
        $host = parse_url($this->endpoint, PHP_URL_HOST);
        $uri = '/' . $this->bucketName . '/' . ltrim($r2Key, '/');
        $url = $this->endpoint . $uri;

        $fileData = @file_get_contents($filePath);
        if ($fileData === false) return false;

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
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 200); // 200ms connect timeout
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500);        // 500ms max timeout
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode === 200 || $httpCode === 201);
    }
}
