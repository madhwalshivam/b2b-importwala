<?php
namespace App\Core;

class Request {
    public function getMethod(): string {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(): bool {
        return $this->getMethod() === 'POST';
    }

    public function isGet(): bool {
        return $this->getMethod() === 'GET';
    }

    public function getPath(): string {
        $path = $_GET['url'] ?? '/';
        $path = rtrim($path, '/');
        return $path === '' ? '/' : '/' . $path;
    }

    public function getBody(): array {
        $data = [];
        $sanitize = function($val) {
            if (function_exists('sanitize_input')) {
                return \sanitize_input($val);
            }
            return is_string($val) ? htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8') : $val;
        };

        if ($this->getMethod() === 'GET') {
            foreach ($_GET as $key => $value) {
                if ($key !== 'url') {
                    $data[$key] = is_array($value) ? array_map($sanitize, $value) : $sanitize($value);
                }
            }
        }
        if ($this->getMethod() === 'POST') {
            foreach ($_POST as $key => $value) {
                $data[$key] = is_array($value) ? array_map($sanitize, $value) : $sanitize($value);
            }
        }
        return $data;
    }

    public function input(string $key, mixed $default = null): mixed {
        $body = $this->getBody();
        return $body[$key] ?? $default;
    }

    public function get(string $key, mixed $default = null): mixed {
        return $_GET[$key] ?? $this->input($key, $default);
    }

    public function file(string $key): ?array {
        return $_FILES[$key] ?? null;
    }

    public function isAjax(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function getIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
