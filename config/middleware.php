<?php

declare(strict_types=1);

return [
    'auth'  => \Src\Core\Middleware\AuthMiddleware::class,
    'admin' => \Src\Core\Middleware\AdminMiddleware::class,
];
