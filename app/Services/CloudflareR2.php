<?php
namespace App\Services;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class CloudflareR2
{
    private ?S3Client $client = null;
    private string $bucketName;
    private string $publicBaseUrl;
    
    public function __construct()
    {
        $accountId = env('R2_ACCOUNT_ID');
        $accessKey = env('R2_ACCESS_KEY_ID');
        $secretKey = env('R2_SECRET_ACCESS_KEY');
        $endpoint = env('R2_ENDPOINT');
        
        $this->bucketName = env('R2_BUCKET', 'importwala-images');
        $this->publicBaseUrl = rtrim(env('R2_PUBLIC_URL', ''), '/');

        if ($accountId && $accessKey && $secretKey) {
            try {
                $clientConfig = [
                    'region'      => 'auto',
                    'version'     => 'latest',
                    'credentials' => [
                        'key'    => $accessKey,
                        'secret' => $secretKey,
                    ],
                    'use_path_style_endpoint' => true,
                    'http' => [
                        'verify' => false
                    ]
                ];
                
                if ($endpoint) {
                    $clientConfig['endpoint'] = $endpoint;
                } else {
                    $clientConfig['endpoint'] = "https://{$accountId}.r2.cloudflarestorage.com";
                }
                
                $this->client = new S3Client($clientConfig);
            } catch (\Exception $e) {
                error_log("WARNING: Failed to instantiate R2 client: " . $e->getMessage());
            }
        } else {
            error_log("WARNING: R2 credentials missing. Skipping R2 client initialization.");
        }
    }

    public function getPublicBaseUrl(): string
    {
        return $this->publicBaseUrl;
    }

    public function getClient(): ?S3Client
    {
        return $this->client;
    }

    public function getBucketName(): string
    {
        return $this->bucketName;
    }

    /**
     * Upload a local file to R2 via S3 SDK.
     */
    public function uploadFile(string $localPath, string $r2Key, string $contentType = 'image/jpeg'): bool
    {
        if (!$this->client || !file_exists($localPath)) {
            return false;
        }

        try {
            $contentType = $this->resolveContentType($localPath, $contentType);
            $cacheControl = strncmp($contentType, 'image/', 6) === 0
                ? 'public, max-age=31536000, immutable'
                : 'public, max-age=31536000';
            $this->client->putObject([
                'Bucket'       => $this->bucketName,
                'Key'          => ltrim($r2Key, '/'),
                'SourceFile'   => $localPath,
                'ContentType'  => $contentType,
                'CacheControl' => $cacheControl
            ]);
            return true;
        } catch (AwsException $e) {
            error_log("R2 Upload Failed [{$r2Key}]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prefer a detected image MIME type over a browser-supplied or generic type.
     */
    private function resolveContentType(string $localPath, string $contentType): string
    {
        $contentType = strtolower(trim(explode(';', $contentType, 2)[0]));
        if ($contentType === 'image/jpg') {
            $contentType = 'image/jpeg';
        }

        $passedIsImage = strncmp($contentType, 'image/', 6) === 0;
        $passedIsGeneric = $contentType === ''
            || $contentType === 'application/octet-stream'
            || $contentType === 'binary/octet-stream';

        if (!$passedIsImage && !$passedIsGeneric) {
            return $contentType;
        }

        $detected = '';
        if (function_exists('mime_content_type')) {
            $detected = strtolower((string) @mime_content_type($localPath));
        }
        if ($detected === 'image/jpg') {
            $detected = 'image/jpeg';
        }
        if (strncmp($detected, 'image/', 6) === 0) {
            return $detected;
        }

        $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
        $map = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'svg'  => 'image/svg+xml',
        ];
        if (isset($map[$ext])) {
            return $map[$ext];
        }

        return $passedIsImage ? $contentType : 'image/jpeg';
    }

    /**
     * Delete an object from R2.
     */
    public function deleteObject(string $r2Key): bool
    {
        if (!$this->client) return false;

        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key'    => ltrim($r2Key, '/')
            ]);
            return true;
        } catch (AwsException $e) {
            error_log("R2 Delete Failed [{$r2Key}]: " . $e->getMessage());
            return false;
        }
    }
}

