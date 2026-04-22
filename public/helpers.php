<?php

declare(strict_types=1);

use Src\Core\Url;

function url(string $path): string
{
    $base = Url::getInstance()->getBase();
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $base = Url::getInstance()->getBase();
    return $base . '/assets/' . ltrim($path, '/');
}
