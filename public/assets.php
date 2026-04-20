<?php

declare(strict_types=1);

if (!function_exists('renderAssets')) {
    function renderAssets(string $basePath = ''): void
    {
        $basePath = rtrim($basePath, '/');
        $appJsPath = ($basePath !== '' ? $basePath : '') . '/assets/js/app.js';

        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-steps@1.1.0/build/jquery.steps.css">' . PHP_EOL;
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">' . PHP_EOL;
        echo '<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>' . PHP_EOL;
        echo '<script src="https://cdn.jsdelivr.net/npm/jquery-steps@1.1.0/build/jquery.steps.min.js"></script>' . PHP_EOL;
        echo '<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>' . PHP_EOL;
        echo '<script src="' . htmlspecialchars($appJsPath, ENT_QUOTES, 'UTF-8') . '"></script>' . PHP_EOL;
    }
}
