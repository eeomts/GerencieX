<?php
namespace Src\Core;

class BaseController {

    protected function render(string $view, array $data = []): void {
        extract($data);
        require __DIR__ . '/../../app/Views/' . $view . '.php';
    }

    protected function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}