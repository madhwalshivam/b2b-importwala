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
        $accountId = getenv('R2_ACCOUNT_ID') ?: '01e0ff8f64110937bdefd6c0f82bc3c6';
        $accessKey = getenv('R2_ACCESS_KEY') ?: '1e453fb192850ae21d857265efb6a98a'; 
        $secretKey = getenv('R2_SECRET_KEY') ?: '899894385cd18e4bb6f51918d679fdd3fb3e0167c26350ecc309503f6f5e7f1a';
        
        $this->bucketName = getenv('R2_BUCKET') ?: 'importwala-images';
        // Go to your bucket settings -> Public Access -> Custom Domains or r2.dev subdomain
        $this->publicBaseUrl = rtrim(getenv('R2_PUBLIC_URL') ?: 'https://pub-d9978a80e9cc429a9b9b47103cded128.r2.dev', '/');

        if ($accountId !== 'YOUR_ACCOUNT_ID' && $accessKey !== 'YOUR_ACCESS_KEY') {
            try {
                $this->client = new S3Client([
                    'region'      => 'auto',
                    'endpoint'    => "https://{$accountId}.r2.cloudflarestorage.com",
                    'version'     => 'latest',
                    'credentials' => [
                        'key'    => $accessKey,
                        'secret' => $secretKey,
                    ],
                    'use_path_style_endpoint' => true,
                    'http' => [
                        'verify' => false
                    ]
                ]);
            } catch (\Exception $e) {
                // Log or handle init failure
            }
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
            $this->client->putObject([
                'Bucket'       => $this->bucketName,
                'Key'          => ltrim($r2Key, '/'),
                'SourceFile'   => $localPath,
                'ContentType'  => $contentType,
                'CacheControl' => 'public, max-age=31536000'
            ]);
            return true;
        } catch (AwsException $e) {
            error_log("R2 Upload Failed [{$r2Key}]: " . $e->getMessage());
            return false;
        }
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

