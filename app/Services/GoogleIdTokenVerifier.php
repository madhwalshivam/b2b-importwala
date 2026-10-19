<?php

namespace App\Services;

/**
 * Verifies a Google Identity Services ID token.
 * Signature is checked against Google's JWKS. Claims are checked locally.
 * The raw token is never written to logs.
 */
class GoogleIdTokenVerifier
{
    private const CERTS_URL = 'https://www.googleapis.com/oauth2/v3/certs';
    private const TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    public static function verify(string $idToken, string $clientId): array
    {
        $idToken = trim($idToken);
        if ($idToken === '' || substr_count($idToken, '.') !== 2 || $clientId === '') {
            return ['ok' => false, 'error' => 'Google sign-in could not be verified. Please try again.'];
        }

        $parts = explode('.', $idToken);
        $header = json_decode(self::b64urlDecode($parts[0]), true);
        $claims = json_decode(self::b64urlDecode($parts[1]), true);
        $signature = self::b64urlDecode($parts[2]);

        if (!is_array($header) || !is_array($claims) || $signature === '') {
            return ['ok' => false, 'error' => 'Google sign-in token is invalid.'];
        }

        $signed = false;
        $kid = (string)($header['kid'] ?? '');
        $alg = (string)($header['alg'] ?? '');
        if ($alg === 'RS256' && $kid !== '') {
            $pem = self::pemForKid($kid);
            if ($pem !== null) {
                $signed = openssl_verify($parts[0] . '.' . $parts[1], $signature, $pem, OPENSSL_ALGO_SHA256) === 1;
            }
        }

        if (!$signed) {
            $remote = self::tokenInfo($idToken);
            if (empty($remote['ok'])) {
                return ['ok' => false, 'error' => 'Google sign-in token is invalid.'];
            }
            $claims = $remote['claims'];
        }

        $iss = (string)($claims['iss'] ?? '');
        if (!in_array($iss, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            return ['ok' => false, 'error' => 'Google sign-in token is invalid.'];
        }

        $aud = $claims['aud'] ?? '';
        $audiences = is_array($aud) ? $aud : [$aud];
        if (!in_array($clientId, $audiences, true)) {
            return ['ok' => false, 'error' => 'Google sign-in token is invalid.'];
        }

        $exp = (int)($claims['exp'] ?? 0);
        if ($exp < time()) {
            return ['ok' => false, 'error' => 'Google sign-in expired. Please try again.'];
        }

        $email = strtolower(trim((string)($claims['email'] ?? '')));
        $verified = $claims['email_verified'] ?? false;
        $emailVerified = $verified === true || $verified === 1 || $verified === 'true';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Google did not share a valid email address.'];
        }
        if (!$emailVerified) {
            return ['ok' => false, 'error' => 'Your Google email is not verified. Verify it with Google, then try again.'];
        }

        $name = trim((string)($claims['name'] ?? ''));
        if ($name === '') {
            $name = trim((string)($claims['given_name'] ?? '') . ' ' . (string)($claims['family_name'] ?? ''));
        }
        if ($name === '') {
            $name = strstr($email, '@', true) ?: 'ImportWale Customer';
        }

        return [
            'ok' => true,
            'sub' => (string)($claims['sub'] ?? ''),
            'email' => $email,
            'name' => mb_substr($name, 0, 150),
            'email_verified' => true,
        ];
    }

    private static function pemForKid(string $kid): ?string
    {
        foreach (self::jwks() as $jwk) {
            if (($jwk['kid'] ?? '') === $kid && ($jwk['kty'] ?? '') === 'RSA' && !empty($jwk['n']) && !empty($jwk['e'])) {
                return self::jwkToPem((string)$jwk['n'], (string)$jwk['e']);
            }
        }
        return null;
    }

    private static function jwks(): array
    {
        $cacheFile = dirname(__DIR__, 2) . '/storage/cache/google_jwks.json';
        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
            $cached = json_decode((string)file_get_contents($cacheFile), true);
            if (!empty($cached['keys']) && is_array($cached['keys'])) {
                return $cached['keys'];
            }
        }

        $json = self::httpGet(self::CERTS_URL);
        $data = json_decode($json ?? '', true);
        if (empty($data['keys']) || !is_array($data['keys'])) {
            return [];
        }
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($cacheFile, json_encode($data));
        return $data['keys'];
    }

    private static function tokenInfo(string $idToken): array
    {
        $json = self::httpPost(self::TOKENINFO_URL, ['id_token' => $idToken]);
        $data = json_decode($json ?? '', true);
        if (!is_array($data) || !empty($data['error']) || empty($data['sub'])) {
            return ['ok' => false];
        }
        return ['ok' => true, 'claims' => $data];
    }

    private static function httpGet(string $url): ?string
    {
        return self::http($url, null);
    }

    private static function httpPost(string $url, array $fields): ?string
    {
        return self::http($url, http_build_query($fields));
    }

    private static function http(string $url, ?string $postBody): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            $opts = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_CONNECTTIMEOUT => 5,
            ];
            if ($postBody !== null) {
                $opts[CURLOPT_POST] = true;
                $opts[CURLOPT_POSTFIELDS] = $postBody;
            }
            curl_setopt_array($ch, $opts);
            $body = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($body !== false && $code >= 200 && $code < 300) {
                return (string)$body;
            }
            return null;
        }
        $ctxOpts = ['http' => ['timeout' => 8]];
        if ($postBody !== null) {
            $ctxOpts['http']['method'] = 'POST';
            $ctxOpts['http']['header'] = "Content-Type: application/x-www-form-urlencoded\r\n";
            $ctxOpts['http']['content'] = $postBody;
        }
        $body = @file_get_contents($url, false, stream_context_create($ctxOpts));
        return $body === false ? null : $body;
    }

    private static function jwkToPem(string $n, string $e): string
    {
        $modulus = self::b64urlDecode($n);
        $exponent = self::b64urlDecode($e);
        if ($modulus !== '' && (ord($modulus[0]) > 0x7f)) {
            $modulus = "\x00" . $modulus;
        }
        if ($exponent !== '' && (ord($exponent[0]) > 0x7f)) {
            $exponent = "\x00" . $exponent;
        }
        $rsa = self::asn1('30', self::asn1('02', $modulus) . self::asn1('02', $exponent));
        $algo = hex2bin('300d06092a864886f70d0101010500');
        $spki = self::asn1('30', $algo . self::asn1('03', "\x00" . $rsa));
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    private static function asn1(string $tagHex, string $value): string
    {
        $len = strlen($value);
        if ($len < 128) {
            $length = chr($len);
        } else {
            $lenBytes = ltrim(pack('N', $len), "\x00");
            $length = chr(0x80 | strlen($lenBytes)) . $lenBytes;
        }
        return hex2bin($tagHex) . $length . $value;
    }

    private static function b64urlDecode(string $data): string
    {
        $data = strtr($data, '-_', '+/');
        $pad = strlen($data) % 4;
        if ($pad) {
            $data .= str_repeat('=', 4 - $pad);
        }
        $decoded = base64_decode($data, true);
        return $decoded === false ? '' : $decoded;
    }
}
