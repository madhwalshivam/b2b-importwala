<?php
namespace App\Core;

class View {
    public function render(string $view, array $params = []): string {
        extract($params);
        $viewPath = __DIR__ . "/../Views/{$view}.php";

        if (!file_exists($viewPath)) {
            return "View file [{$view}] not found.";
        }

        ob_start();
        include $viewPath;
        return ob_get_clean();
    }
}
