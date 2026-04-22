<?php

declare(strict_types=1);

function renderAssets(): void
{
        echo '<link rel="stylesheet" href="' . asset('css/style.css') . '">' . PHP_EOL;
        echo '<link rel="stylesheet" href="' . asset('vendor/jquery-steps/jquery.steps.css') . '">' . PHP_EOL;
        echo '<link rel="stylesheet" href="' . asset('vendor/toastify/toastify.css') . '">' . PHP_EOL;
        echo '<script src="' . asset('vendor/jquery/jquery.min.js') . '"></script>' . PHP_EOL;
        echo '<script src="' . asset('vendor/jquery-steps/jquery.steps.min.js') . '"></script>' . PHP_EOL;
        echo '<script src="' . asset('vendor/toastify/toastify.js') . '"></script>' . PHP_EOL;
        echo '<script src="' . asset('js/app.js') . '"></script>' . PHP_EOL;
}
