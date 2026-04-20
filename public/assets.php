<?php

declare(strict_types=1);


function renderAssets(): void
{
    echo '<link rel="stylesheet" href="/assets/vendor/jquery-steps/jquery.steps.css">' . PHP_EOL;
    echo '<link rel="stylesheet" href="/assets/vendor/toastify/toastify.css">' . PHP_EOL;
    echo '<script src="/assets/vendor/jquery/jquery.min.js"></script>' . PHP_EOL;
    echo '<script src="/assets/vendor/jquery-steps/jquery.steps.min.js"></script>' . PHP_EOL;
    echo '<script src="/assets/vendor/toastify/toastify.js"></script>' . PHP_EOL;
    echo '<script src="/assets/js/app.js"></script>' . PHP_EOL;
}
