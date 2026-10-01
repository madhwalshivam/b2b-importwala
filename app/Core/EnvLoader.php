<?php

namespace App\Core;

class EnvLoader
{
    public static function load(string $directory): void
    {
        // Check one level above first (outside public_html)
        $envPath = dirname($directory) . '/.env';
        if (!file_exists($envPath)) {
            // Check the web root itself
            $envPath = rtrim($directory, '/\\') . '/.env';
        }
        if (!file_exists($envPath)) {
            // Check inside public folder (user explicitly requested this)
            $envPath = rtrim($directory, '/\\') . '/public/.env';
        }

        if (!file_exists($envPath)) {
            error_log("CRITICAL ERROR: .env file is missing. Expected at $envPath or its parent.");
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json', true, 500);
                echo json_encode(['status' => 'error', 'message' => 'System configuration missing.']);
            } else {
                http_response_code(500);
                echo '<div style="font-family:sans-serif; text-align:center; padding:60px; color:#333;"><h2 style="font-size:24px; font-weight:bold;">Something went wrong</h2><p style="color:#666;">We are experiencing a brief system issue. Please refresh or return to the homepage.</p><a href="/" style="display:inline-block; margin-top:15px; padding:10px 20px; background:#f05a29; color:#fff; text-decoration:none; border-radius:8px; font-weight:bold;">Return to Homepage</a></div>';
            }
            exit;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#') || strpos($line, '=') === false) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv(sprintf('%s=%s', $key, $value));
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}
