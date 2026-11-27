<?php

declare(strict_types=1);

use App\Controllers\Admin\LocalCoreAdminController;

// Include from routes/web.php, only in the integration worktree.
// The adapter checks loopback, port, separate session and local DB.
// Do NOT attach the incomplete Core RBAC or incompatible CSRF middleware yet.
/** @var \FastRoute\RouteCollector $routes */
$localAdminRoute = [
    'handler' => [LocalCoreAdminController::class, 'dispatch'],
    'middleware' => [], // Existing dispatcher enforces its own auth, RBAC and CSRF.
];
foreach (['/admin', '/admin/dashboard', '/admin/rbac', '/admin/orders'] as $path) {
    $routes->addRoute('GET', $path, $localAdminRoute);
}
$routes->addRoute(['GET', 'POST'], '/admin/login', $localAdminRoute);
foreach (['/admin/logout', '/admin/rbac/role', '/admin/rbac/staff-write'] as $path) {
    $routes->addRoute('POST', $path, $localAdminRoute);
}
$id = '{id:[1-9][0-9]*}';
$routes->addRoute('GET', '/admin/orders/' . $id, $localAdminRoute);
$routes->addRoute('POST', '/admin/orders/' . $id . '/update', $localAdminRoute);
foreach (['products', 'categories', 'brands', 'users'] as $module) {
    $base = '/admin/' . $module;
    $routes->addRoute(['GET', 'POST'], $base, $localAdminRoute);
    $routes->addRoute('GET', $base . '/create', $localAdminRoute);
    $routes->addRoute('GET', $base . '/' . $id . '/edit', $localAdminRoute);
    $actions = match ($module) {
        'products' => ['update', 'activate', 'hide', 'delete'],
        'categories', 'brands' => ['update', 'activate', 'deactivate', 'delete'],
        'users' => ['update', 'reset', 'lock', 'unlock'],
    };
    foreach ($actions as $action) {
        $routes->addRoute('POST', $base . '/' . $id . '/' . $action, $localAdminRoute);
    }
}
$routes->addRoute('GET', '/admin/users/' . $id . '/password', $localAdminRoute);
$routes->addRoute(['GET', 'POST'], '/admin/products/' . $id . '/catalog', $localAdminRoute);
$routes->addRoute('GET', '/admin/products/' . $id . '/catalog/image/{imageId:[1-9][0-9]*}', $localAdminRoute);
unset($localAdminRoute, $path, $id, $module, $base, $actions, $action);
