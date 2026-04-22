<?php

declare(strict_types=1);

namespace Src\Core\Middleware;

use Src\Core\Url;

class AdminMiddleware
{
    public function handle(): bool
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . Url::getInstance()->getBase() . '/login');
            exit;
        }

        if ((int) ($_SESSION['user']['user_type'] ?? 0) !== 2) {
            http_response_code(403);
            echo '<h1>403 - Acesso restrito a administradores</h1>';
            exit;
        }

        return true;
    }
}
