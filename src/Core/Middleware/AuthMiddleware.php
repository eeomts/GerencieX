<?php

declare(strict_types=1);

namespace Src\Core\Middleware;

use Src\Core\Url;

class AuthMiddleware
{
    public function handle(): bool
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . Url::getInstance()->getBase() . '/login');
            exit;
        }

        return true;
    }
}
