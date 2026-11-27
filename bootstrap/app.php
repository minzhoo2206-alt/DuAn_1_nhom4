<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Router;
use Dotenv\Dotenv;

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable($root);
$dotenv->safeLoad();

$appConfig = require $root . '/config/app.php';

date_default_timezone_set($appConfig['timezone']);

if (session_status() !== PHP_SESSION_ACTIVE) {

    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
        'cookie_secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off'
    ]);
}

$request = new Request();

Router::dispatch($request);
