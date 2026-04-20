<?php

declare(strict_types=1);

namespace Src\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void
    {
        $this->routes['GET'][] = [$path, $controller, $method];
    }

    public function post(string $path, string $controller, string $method): void
    {
        $this->routes['POST'][] = [$path, $controller, $method];
    }

    public function dispatch(): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes[$httpMethod] ?? [] as [$path, $controllerClass, $action]) {
            $params = $this->match($path, $uri);

            if ($params !== null) {
                $controller = new $controllerClass();
                $controller->$action(...$params);
                return;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
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