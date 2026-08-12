<?php
namespace App\Core;

class Router {
    public Request $request;
    public Response $response;
    protected array $routes = [];

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $path, mixed $callback, array $middleware = []): void {
        $this->routes['GET'][$path] = [
            'callback' => $callback,
            'middleware' => $middleware
        ];
    }

    public function post(string $path, mixed $callback, array $middleware = []): void {
        $this->routes['POST'][$path] = [
            'callback' => $callback,
            'middleware' => $middleware
        ];
    }

    public function resolve() {
        $method = $this->request->getMethod();
        $path = $this->request->getPath();

        // Direct matching first
        $route = $this->routes[$method][$path] ?? null;

        // Dynamic parameter route matching (e.g. /product/{slug})
        if (!$route) {
            foreach ($this->routes[$method] ?? [] as $routePath => $routeData) {
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $routePath);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches);
                    $route = $routeData;
                    $route['params'] = $matches;
                    break;
                }
            }
        }

        if (!$route) {
            $this->response->setStatusCode(404);
            return (new View())->render('errors/404');
        }

        // Middleware execution
        foreach ($route['middleware'] ?? [] as $middleware) {
            if (is_string($middleware) && class_exists($middleware)) {
                $instance = new $middleware();
                $instance->execute();
            } elseif (is_callable($middleware)) {
                call_user_func($middleware);
            }
        }

        $callback = $route['callback'];

        if (is_string($callback)) {
            [$controllerClass, $methodName] = explode('@', $callback);
            $fullClass = "App\\Controllers\\" . $controllerClass;
            if (class_exists($fullClass)) {
                $controller = new $fullClass();
                return call_user_func_array([$controller, $methodName], $route['params'] ?? []);
            }
        }

        if (is_array($callback)) {
            $controller = new $callback[0]();
            return call_user_func_array([$controller, $callback[1]], $route['params'] ?? []);
        }

        if (is_callable($callback)) {
            return call_user_func_array($callback, $route['params'] ?? []);
        }

        $this->response->setStatusCode(404);
        return (new View())->render('errors/404');
    }
}
