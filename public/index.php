<?php

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

require_once __DIR__ . '/assets.php';

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../vendor/autoload.php';

use Src\Core\Router;

$router = new Router();

require __DIR__ . '/../config/routes.php';

$router->dispatch();
