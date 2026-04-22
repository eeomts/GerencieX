<?php

declare(strict_types=1);

namespace Src\Core;

class Router
{
    private array $routes = [];
    private array $middlewareAliases = [];

    public function setMiddlewareAliases(array $aliases): void
    {
        $this->middlewareAliases = $aliases;
    }

    public function get(string $path, string $controller, string $method, array $middleware = []): void
    {
        $this->routes['GET'][] = [$path, $controller, $method, $middleware];
    }

    public function post(string $path, string $controller, string $method, array $middleware = []): void
    {
        $this->routes['POST'][] = [$path, $controller, $method, $middleware];
    }

    public function dispatch(): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $this->normalizeUri((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        foreach ($this->routes[$httpMethod] ?? [] as [$path, $controllerClass, $action, $middleware]) {
            $params = $this->match($path, $uri);

            if ($params !== null) {
                $this->runMiddleware($middleware);

                $controller = new $controllerClass();
                $result = $controller->$action(...$params);

                if (is_array($result)) {
                    header('Content-Type: application/json');
                    echo json_encode($result);
                }

                return;
            }
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'message' => '404 Not Found']);
    }

    private function runMiddleware(array $middleware): void
    {
        foreach ($middleware as $alias) {
            $class = $this->middlewareAliases[$alias] ?? null;

            if ($class === null || !class_exists($class)) {
                http_response_code(500);
                echo 'Middleware não registrado: ' . htmlspecialchars($alias);
                exit;
            }

            (new $class())->handle();
        }
    }

    private function normalizeUri(string $uri): string
    {
        $baseUrl = Url::getInstance()->getBase();
        $basePath = (string) parse_url($baseUrl, PHP_URL_PATH);

        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        if ($uri === '' || $uri === false) {
            return '/';
        }

        return '/' . ltrim($uri, '/');
    }

    private function match(string $path, string $uri): ?array
    {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            return $matches;
        }

        return null;
    }
}
