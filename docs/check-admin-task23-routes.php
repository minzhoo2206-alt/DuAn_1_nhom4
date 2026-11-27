<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$dispatcher = \FastRoute\simpleDispatcher(static function (\FastRoute\RouteCollector $routes) use ($root): void {
    require $root . '/routes/web.php';
});
$checks = [
    ['GET', '/health', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/dashboard', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/login', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/login', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/logout', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/logout', \FastRoute\Dispatcher::METHOD_NOT_ALLOWED],
    ['GET', '/admin/products', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/products', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/products/create', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/products/1/edit', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/products/1/update', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/products/1/activate', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/products/1/hide', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/products/1/delete', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/products/1/delete', \FastRoute\Dispatcher::METHOD_NOT_ALLOWED],
    ['GET', '/admin/products/1/catalog', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/products/1/catalog', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/products/1/catalog/image/2', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/categories', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/categories/1/deactivate', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/brands', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/brands/1/activate', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/users', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/users/1/reset', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/users/1/password', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/users/1/lock', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/rbac', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/rbac/role', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/rbac/staff-write', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/orders', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/orders/1', \FastRoute\Dispatcher::FOUND],
    ['POST', '/admin/orders/1/update', \FastRoute\Dispatcher::FOUND],
    ['GET', '/admin/products/0/edit', \FastRoute\Dispatcher::NOT_FOUND],
    ['GET', '/admin/not-a-route', \FastRoute\Dispatcher::NOT_FOUND],
];
foreach ($checks as [$method, $path, $expected]) {
    $result = $dispatcher->dispatch($method, $path);
    $ok = $result[0] === $expected;
    if ($ok && $expected === \FastRoute\Dispatcher::FOUND && $path !== '/health') {
        $ok = $result[1]['handler'] === [\App\Controllers\Admin\LocalCoreAdminController::class, 'dispatch'];
    }
    if (!$ok) { echo "FAIL $method $path\n"; exit(1); }
    echo "[OK] $method $path\n";
}
echo 'PASS ' . count($checks) . " TASK 23 ROUTE CHECKS (routing only)\n";
