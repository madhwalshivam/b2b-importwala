<?php

namespace App\Services;

/**
 * Local XAMPP often has no CA bundle configured, so HTTPS calls to Jina and
 * Qdrant fail with curl error 60. Use the ini bundle when it exists, and the
 * XAMPP bundle on this machine. Hosting that already trusts public CAs is unchanged.
 */
class SecureCurl
{
    public static function applyCaBundle($ch): void
    {
        $candidates = [
            (string) ini_get('curl.cainfo'),
            (string) ini_get('openssl.cafile'),
            'C:/xampp/apache/bin/curl-ca-bundle.crt',
        ];
        foreach ($candidates as $path) {
            if ($path !== '' && is_file($path)) {
                curl_setopt($ch, CURLOPT_CAINFO, $path);
                return;
            }
        }
    }
}
