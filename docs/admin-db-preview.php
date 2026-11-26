<?php

declare(strict_types=1);

use App\Core\Database;
use App\Repositories\Admin\ProductAdminRepository;
use App\Services\Admin\ProductAdminService;
use Dotenv\Dotenv;

// Local development only. No write routes.
if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Local preview only');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit('Read-only preview');
}
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path === '/' || $path === '/admin') {
    header('Location: /admin/products', true, 302);
    return;
}
if ($path !== '/admin/products') {
    http_response_code(404);
    exit('Route not available in database preview');
}
header('Cache-Control: no-store');
$root = dirname(__DIR__);
try {
    require $root . '/vendor/autoload.php';
    $dotenv = Dotenv::createImmutable($root);
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'])->notEmpty();
    $db = Database::connection();
    $repository = new ProductAdminRepository($db);
    $service = new ProductAdminService($repository);
    $requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    $data = $service->listing($requestedPage === false ? 1 : $requestedPage, 10);
    $products = $data['products'];
    $page = $data['page'];
    $total = $data['total'];
    $totalPages = $data['totalPages'];
    $isPreview = true;
    require $root . '/resources/views/admin/products/database-preview.php';
} catch (Throwable $e) {
    http_response_code(500);
    error_log('[ADMIN DB PREVIEW] ' . get_class($e) . ': ' . $e->getCode());
    echo 'Khong the tai danh sach. Chay php docs/check-database.php de kiem tra.';
}
