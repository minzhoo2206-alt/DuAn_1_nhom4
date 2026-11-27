<?php

declare(strict_types=1);

use App\Core\HealthController;
use App\Controllers\Store\ContactController;

/** @var \\FastRoute\RouteCollector $routes */
$routes->addRoute('GET', '/health', [
    'handler' => [HealthController::class, 'index'],
    'middleware' => [],
]);
$routes->addRoute('GET', '/lien-he', [
    'handler' => [ContactController::class, 'index'],
    'middleware' => [],
]);