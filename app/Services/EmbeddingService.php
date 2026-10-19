<?php

namespace App\Services;

/**
 * Image embeddings behind one class so the provider can be swapped.
 * Default provider: Jina CLIP v2 over REST. Nothing in this class is Jina-specific
 * beyond the request body; point EMBEDDING_API_URL / EMBEDDING_MODEL at another
 * compatible embeddings endpoint to replace it.
 */
class EmbeddingService
{
    public const TIMEOUT_SECONDS = 8;
    public const MAX_SIDE = 512;
    private const MAX_DOWNLOAD_BYTES = 8 * 1024 * 1024;

    private string $apiKey;
    private string $endpoint;
    private string $model;
    private int $dimensions;

    public function __construct()
    {
        $this->apiKey = trim((string) env('EMBEDDING_API_KEY', ''));
        $this->endpoint = rtrim((string) env('EMBEDDING_API_URL', 'https://api.jina.ai/v1/embeddings'), '/');
        $this->model = trim((string) env('EMBEDDING_MODEL', 'jina-clip-v2'));
        if ($this->model === '') {
            $this->model = 'jina-clip-v2';
        }
        $this->dimensions = (int) env('EMBEDDING_DIMENSIONS', 1024);
        if ($this->dimensions < 64) {
            $this->dimensions = 1024;
        }
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->endpoint !== '';
    }

    public function dimensions(): int
    {
        return $this->dimensions;
    }

    /**
     * Embed a local image. Resizes to max 512px JPEG before the API call.
     *
     * @return float[]
     */
    public function embedFile(string $path): array
    {
        $this->assertConfigured();
        $jpeg = $this->resizeFileToJpeg($path);
        return $this->embedJpegBinary($jpeg);
    }

    /**
     * Download a catalog image (R2 or local), resize, then embed.
     *
     * @return float[]
     */
    public function embedRemote(string $urlOrPath): array
    {
        $this->assertConfigured();
        $binary = $this->loadImageBinary($urlOrPath);
        $tmp = tempnam(sys_get_temp_dir(), 'emb_');
        if ($tmp === false) {
            throw new EmbeddingException('Could not read the product image.', 422);
        }
        file_put_contents($tmp, $binary);
        try {
            return $this->embedFile($tmp);
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * @return float[]
     */
    private function embedJpegBinary(string $jpeg): array
    {
        $dataUri = 'data:image/jpeg;base64,' . base64_encode($jpeg);
        $body = [
            'model' => $this->model,
            'dimensions' => $this->dimensions,
            'normalized' => true,
            'embedding_type' => 'float',
            'input' => [
                ['image' => $dataUri],
            ],
        ];

        try {
            $decoded = $this->post($body);
        } catch (EmbeddingException $e) {
            if ($e->getCode() !== 400) {
                throw $e;
            }
            // Some compatible endpoints accept a bare data-URI string instead of {image: ...}.
            $decoded = $this->post([
                'model' => $this->model,
                'dimensions' => $this->dimensions,
                'normalized' => true,
                'embedding_type' => 'float',
                'input' => [$dataUri],
            ], true);
        }

        $vector = $decoded['data'][0]['embedding'] ?? null;
        if (!is_array($vector) || count($vector) < 8) {
            error_log('[Embedding] empty vector from provider');
            throw new EmbeddingException('Image search is temporarily unavailable. Please try again in a few minutes.', 503);
        }

        $out = [];
        foreach ($vector as $n) {
            if (!is_numeric($n)) {
                throw new EmbeddingException('Image search is temporarily unavailable. Please try again in a few minutes.', 503);
            }
            $out[] = (float) $n;
        }
        return $out;
    }

    private function assertConfigured(): void
    {
        if (!$this->isConfigured()) {
            error_log('[Embedding] EMBEDDING_API_KEY is not set');
            throw new EmbeddingException('Image search is temporarily unavailable. Please try again in a few minutes.', 503);
        }
        if (!function_exists('curl_init')) {
            error_log('[Embedding] PHP curl extension missing');
            throw new EmbeddingException('Image search is temporarily unavailable. Please try again in a few minutes.', 503);
        }
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    private function post(array $body, bool $finalAttemptFormat = false): array
    {
        $payload = json_encode($body);
        if ($payload === false) {
            throw new EmbeddingException('Could not read this image.', 422);
        }

        $attempts = 3;
        $lastDetail = 'request failed';
        for ($i = 1; $i <= $attempts; $i++) {
            $ch = curl_init($this->endpoint);
            if ($ch === false) {
                throw new EmbeddingException('Image search is temporarily unavailable. Please try again in a few minutes.', 503);
            }
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $this->apiKey,
                ],
            ]);
            SecureCurl::applyCaBundle($ch);
            $raw = curl_exec($ch);
            $errno = curl_errno($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($errno !== 0 || $raw === false) {
                $lastDetail = 'curl ' . $errno . ' ' . $err;
                error_log('[Embedding] ' . $lastDetail . ' attempt ' . $i);
                if ($i < $attempts) {
                    usleep(400000 * $i);
                    continue;
                }
                throw new EmbeddingException('Image search is temporarily unavailable. Please try again in a few minutes.', 503);
            }

            $decoded = json_decode((string) $raw, true);
            if ($status >= 200 && $status < 300 && is_array($decoded)) {
                return $decoded;
            }

            $snippet = substr(preg_replace('/\s+/', ' ', (string) $raw) ?? '', 0, 300);
            $lastDetail = 'HTTP ' . $status . ' ' . $snippet;
            error_log('[Embedding] ' . $lastDetail);

            if ($status === 401 || $status === 403) {
                throw new EmbeddingException('Image search is temporarily unavailable. Please try again in a few minutes.', 503);
            }
            if ($status === 400 || $status === 422) {
                if (!$finalAttemptFormat) {
                    throw new EmbeddingException('format', 400);
                }
                throw new EmbeddingException('Could not read this image. Use a clear JPG, PNG, or WEBP photo.', 422);
            }
            if ($i < $attempts && ($status === 429 || $status >= 500)) {
                usleep(400000 * $i);
                continue;
            }
            throw new EmbeddingException('Image search is temporarily unavailable. Please try again in a few minutes.', 503);
        }

        error_log('[Embedding] exhausted retries: ' . $lastDetail);
        throw new EmbeddingException('Image search is temporarily unavailable. Please try again in a few minutes.', 503);
    }

    private function resizeFileToJpeg(string $path): string
    {
        if (!is_file($path)) {
            throw new EmbeddingException('Could not read this image.', 422);
        }
        if (!extension_loaded('gd')) {
            error_log('[Embedding] PHP GD extension missing');
            throw new EmbeddingException('Image search is temporarily unavailable. Please try again in a few minutes.', 503);
        }
        $binary = @file_get_contents($path);
        if ($binary === false || $binary === '') {
            throw new EmbeddingException('Could not read this image.', 422);
        }
        $img = @imagecreatefromstring($binary);
        if (!$img) {
            throw new EmbeddingException('Could not read this image. Use a JPG, PNG, or WEBP photo.', 422);
        }

        $w = imagesx($img);
        $h = imagesy($img);
        if ($w < 1 || $h < 1) {
            imagedestroy($img);
            throw new EmbeddingException('Could not read this image.', 422);
        }

        $scale = min(1, self::MAX_SIDE / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        if ($dst === false) {
            imagedestroy($img);
            throw new EmbeddingException('Could not read this image.', 422);
        }
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $white);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);

        ob_start();
        imagejpeg($dst, null, 85);
        imagedestroy($dst);
        $jpeg = ob_get_clean();
        if (!is_string($jpeg) || $jpeg === '') {
            throw new EmbeddingException('Could not read this image.', 422);
        }
        return $jpeg;
    }

    private function loadImageBinary(string $urlOrPath): string
    {
        if (preg_match('~^https?://~i', $urlOrPath)) {
            return $this->download($urlOrPath);
        }

        $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        $rel = ltrim(str_replace(['..', '\\'], ['', '/'], $urlOrPath), '/');
        $candidates = [
            $root . '/public/' . $rel,
            $root . '/' . $rel,
        ];
        foreach ($candidates as $abs) {
            if (is_file($abs)) {
                $bin = @file_get_contents($abs);
                if (is_string($bin) && $bin !== '') {
                    return $bin;
                }
            }
        }
        throw new EmbeddingException('Could not read the product image.', 422);
    }

    private function download(string $url): string
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new EmbeddingException('Could not read the product image.', 422);
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_USERAGENT => 'ImportWaleImageIndex/1.0',
        ]);
        SecureCurl::applyCaBundle($ch);
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($errno !== 0 || !is_string($raw) || $raw === '' || $status >= 400) {
            error_log('[Embedding] image download failed status=' . $status . ' errno=' . $errno . ' url=' . substr($url, 0, 180));
            throw new EmbeddingException('Could not read the product image.', 422);
        }
        if (strlen($raw) > self::MAX_DOWNLOAD_BYTES) {
            throw new EmbeddingException('Could not read the product image.', 422);
        }
        return $raw;
    }
}
