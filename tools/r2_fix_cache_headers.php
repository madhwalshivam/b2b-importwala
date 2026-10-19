<?php

/**
 * One-time, re-runnable update of Cache-Control and Content-Type
 * on objects already stored in the importwala-images R2 bucket.
 *
 * Uses S3 CopyObject in place with MetadataDirective REPLACE.
 * Objects that already have the target cache header and a matching
 * content type are skipped, so running it again is safe.
 *
 * Usage (from the project root):
 *   php tools/r2_fix_cache_headers.php
 *   php tools/r2_fix_cache_headers.php --dry-run
 *   php tools/r2_fix_cache_headers.php --prefix=products/
 */

require_once __DIR__ . '/cli_bootstrap.php';

use App\Services\CloudflareR2;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this from the command line.\n");
    exit(1);
}

$dryRun = in_array('--dry-run', $argv, true);
$prefix = '';
$batchSize = 100;
foreach ($argv as $arg) {
    if (strpos($arg, '--prefix=') === 0) {
        $prefix = substr($arg, 9);
    } elseif (strpos($arg, '--batch=') === 0) {
        $batchSize = max(1, min(1000, (int) substr($arg, 8)));
    }
}

$desiredCache = 'public, max-age=31536000, immutable';

$r2 = new CloudflareR2();
$client = $r2->getClient();
if (!$client) {
    fwrite(STDERR, "R2 client is not configured. Check R2_ACCOUNT_ID, R2_ACCESS_KEY_ID, R2_SECRET_ACCESS_KEY and R2_ENDPOINT.\n");
    exit(1);
}

$bucket = $r2->getBucketName();
echo "Bucket: {$bucket}" . ($dryRun ? " (dry run)" : "") . PHP_EOL;

$mimeByExt = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'avif' => 'image/avif',
    'svg'  => 'image/svg+xml',
];

$copySource = static function (string $bucket, string $key): string {
    $segments = explode('/', $key);
    $encoded = implode('/', array_map('rawurlencode', $segments));
    return rawurlencode($bucket) . '/' . $encoded;
};

$contentTypeFor = static function (string $key, string $current) use ($mimeByExt): string {
    $current = strtolower(trim(explode(';', $current, 2)[0]));
    if ($current === 'image/jpg') {
        $current = 'image/jpeg';
    }
    $path = parse_url($key, PHP_URL_PATH);
    $ext = strtolower(pathinfo(is_string($path) ? $path : $key, PATHINFO_EXTENSION));
    if (isset($mimeByExt[$ext])) {
        return $mimeByExt[$ext];
    }
    if ($current !== '' && $current !== 'application/octet-stream' && $current !== 'binary/octet-stream') {
        return $current;
    }
    return 'application/octet-stream';
};

$token = null;
$seen = 0;
$updated = 0;
$skipped = 0;
$failed = 0;

do {
    $params = [
        'Bucket'  => $bucket,
        'MaxKeys' => $batchSize,
    ];
    if ($prefix !== '') {
        $params['Prefix'] = $prefix;
    }
    if ($token) {
        $params['ContinuationToken'] = $token;
    }

    $result = $client->listObjectsV2($params);
    foreach ($result['Contents'] ?? [] as $object) {
        $key = (string) ($object['Key'] ?? '');
        if ($key === '' || substr($key, -1) === '/') {
            continue;
        }
        $seen++;

        try {
            $head = $client->headObject([
                'Bucket' => $bucket,
                'Key'    => $key,
            ]);
            $currentType = (string) ($head['ContentType'] ?? '');
            $currentCache = trim((string) ($head['CacheControl'] ?? ''));
            $fixedType = $contentTypeFor($key, $currentType);
            $typeOk = strtolower(trim(explode(';', $currentType, 2)[0])) === $fixedType
                || ($fixedType === 'image/jpeg' && strtolower(trim(explode(';', $currentType, 2)[0])) === 'image/jpg');

            if ($currentCache === $desiredCache && $typeOk) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                echo "would update {$key} type={$fixedType}" . PHP_EOL;
                $updated++;
                continue;
            }

            $copy = [
                'Bucket'            => $bucket,
                'Key'               => $key,
                'CopySource'        => $copySource($bucket, $key),
                'MetadataDirective' => 'REPLACE',
                'CacheControl'      => $desiredCache,
                'ContentType'       => $fixedType,
            ];
            if (!empty($head['ContentDisposition'])) {
                $copy['ContentDisposition'] = $head['ContentDisposition'];
            }
            if (!empty($head['ContentEncoding'])) {
                $copy['ContentEncoding'] = $head['ContentEncoding'];
            }
            if (!empty($head['ContentLanguage'])) {
                $copy['ContentLanguage'] = $head['ContentLanguage'];
            }
            $metadata = $head['Metadata'] ?? [];
            if (is_array($metadata) && $metadata) {
                $copy['Metadata'] = $metadata;
            }

            $client->copyObject($copy);
            $updated++;
            echo "updated {$key} ({$fixedType})" . PHP_EOL;
        } catch (Throwable $e) {
            $failed++;
            fwrite(STDERR, "failed {$key}: {$e->getMessage()}" . PHP_EOL);
        }
    }

    echo "batch done. seen={$seen} updated={$updated} skipped={$skipped} failed={$failed}" . PHP_EOL;
    $token = !empty($result['IsTruncated']) ? ($result['NextContinuationToken'] ?? null) : null;
} while ($token);

echo "finished. seen={$seen} updated={$updated} skipped={$skipped} failed={$failed}" . PHP_EOL;
exit($failed > 0 ? 1 : 0);
