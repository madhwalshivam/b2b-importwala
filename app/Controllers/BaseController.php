<?php

namespace App\Controllers;

use App\Infrastructure\Cache\CacheManager;

abstract class BaseController
{
    protected CacheManager $cache;

    public function __construct()
    {
        $this->cache = CacheManager::getInstance();
    }

    protected function renderView(string $viewPath, array $data = []): void
    {
        extract($data);
        $file = __DIR__ . '/../../views/' . str_replace('.', '/', $viewPath) . '.php';
        if (!file_exists($file)) {
            $file = __DIR__ . '/../Views/' . str_replace('.', '/', $viewPath) . '.php';
        }

        if (file_exists($file)) {
            require $file;
        } else {
            throw new \RuntimeException("View template not found: {$viewPath}");
        }
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
