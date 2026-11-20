<?php

declare(strict_types=1);

use App\Core\HealthController;

/** @var \\FastRoute\RouteCollector $routes */
$routes->addRoute('GET', '/health', [
    'handler' => [HealthController::class, 'index'],
    'middleware' => [],
]);
