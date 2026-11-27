<?php

declare(strict_types=1);

use App\Core\Database;
use App\Repositories\Admin\BrandAdminRepository;
use App\Services\Admin\BrandAdminService;
use App\Repositories\Admin\CategoryAdminRepository;
use App\Services\Admin\CategoryAdminService;
use App\Repositories\Admin\ProductAdminRepository;
use App\Services\Admin\ProductAdminService;
use Dotenv\Dotenv;

// Development only: not a production router or a substitute for RBAC.
if (PHP_SAPI !== 'cli-server'
    || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
    || ($_SERVER['HTTP_HOST'] ?? '') !== '127.0.0.1:8001') {
    http_response_code(403);
    exit('Local test only');
}
header('Cache-Control: no-store');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if ($method === 'GET' && $path === '/') {
    header('Location: /admin', true, 302);
    return;
}
// Task 17: local login and server-side RBAC.
$rbacRoute = false;
$orderOperation = null; $orderId = 0;
// Task 12/13 routes: GET edit; POST update, hide, delete.
$userOperation = null;
$userId = 0;
$brandRoute = false;
$brandId = 0;
$brandOperation = null;
$categoryRoute = false;
$categoryId = 0;
$categoryOperation = null;
$productId = null;
$operation = null;
if (in_array($path, ['/admin', '/admin/dashboard'], true)) {
    $allowed = ['GET'];
} elseif (in_array($path, ['/admin/login','/admin/logout','/admin/rbac','/admin/rbac/role','/admin/rbac/staff-write'], true)) {
    $rbacRoute = true;
    $allowed = match ($path) { '/admin/login' => ['GET','POST'], '/admin/rbac' => ['GET'], default => ['POST'] };
} elseif ($path === '/admin/orders') {
    $orderOperation = 'list'; $allowed = ['GET'];
} elseif (is_string($path) && preg_match('~^/admin/orders/([1-9][0-9]*)(/update)?$~D', $path, $match)) {
    $orderId = filter_var($match[1], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    if ($orderId === false) { http_response_code(404); exit('Không tìm thấy đơn.'); }
    $orderOperation = isset($match[2]) ? 'update' : 'detail';
    $allowed = $orderOperation === 'update' ? ['POST'] : ['GET'];
} elseif (is_string($path) && preg_match('~^/admin/products/([1-9][0-9]*)/(edit|update|hide|delete)$~D', $path, $match)) {
    $productId = filter_var($match[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $operation = $match[2];
    if ($productId === false) {
        http_response_code(404);
        exit('Khong tim thay san pham');
    }
    $allowed = $operation === 'edit' ? ['GET'] : ['POST'];
} elseif ($path === '/admin/categories') {
    $categoryRoute = true;
    $categoryOperation = $method === 'GET' ? 'list' : 'store';
    $allowed = ['GET', 'POST'];
} elseif ($path === '/admin/categories/create') {
    $categoryRoute = true;
    $categoryOperation = 'create';
    $allowed = ['GET'];
} elseif (is_string($path) && preg_match('~^/admin/categories/([1-9][0-9]*)/(edit|update|activate|deactivate|delete)$~D', $path, $match)) {
    $categoryRoute = true;
    $categoryId = filter_var($match[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($categoryId === false) {
        http_response_code(404);
        exit('Không tìm thấy danh mục.');
    }
    $categoryOperation = $match[2];
    $allowed = $categoryOperation === 'edit' ? ['GET'] : ['POST'];
} elseif ($path === '/admin/brands') {
    $brandRoute = true;
    $brandOperation = $method === 'GET' ? 'list' : 'store';
    $allowed = ['GET', 'POST'];
} elseif ($path === '/admin/brands/create') {
    $brandRoute = true;
    $brandOperation = 'create';
    $allowed = ['GET'];
} elseif (is_string($path) && preg_match('~^/admin/brands/([1-9][0-9]*)/(edit|update|activate|deactivate|delete)$~D', $path, $match)) {
    $brandRoute = true;
    $brandId = filter_var($match[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($brandId === false) {
        http_response_code(404);
        exit('Không tìm thấy thương hiệu.');
    }
    $brandOperation = $match[2];
    $allowed = $brandOperation === 'edit' ? ['GET'] : ['POST'];
} elseif ($path === '/admin/users') {
    $userOperation = $method === 'GET' ? 'list' : 'store';
    $allowed = ['GET', 'POST'];
} elseif ($path === '/admin/users/create') {
    $userOperation = 'create'; $allowed = ['GET'];
} elseif (is_string($path) && preg_match('~^/admin/users/([1-9][0-9]*)/(edit|update|password|reset|lock|unlock)$~D', $path, $match)) {
    $userId = filter_var($match[1], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    if ($userId === false) { http_response_code(404); exit('Không tìm thấy tài khoản.'); }
    $userOperation = $match[2];
    $allowed = in_array($userOperation, ['edit','password'], true) ? ['GET'] : ['POST'];
} elseif ($path === '/admin/products') {
    $allowed = ['GET', 'POST'];
} elseif ($path === '/admin/products/create') {
    $allowed = ['GET'];
} else {
    http_response_code(404);
    exit('Route not available in Task 17');
}
if (!in_array($method, $allowed, true)) {
    http_response_code(405);
    header('Allow: ' . implode(', ', $allowed));
    exit('Method not allowed');
}
$root = dirname(__DIR__);
try {
    require $root . '/vendor/autoload.php';
    // Never load the hosting .env here. Check effective config before PDO.
    $dotenv = Dotenv::createImmutable($root, '.env.admin-test');
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_CHARSET'])->notEmpty();
    $dotenv->required('DB_PASSWORD');
    $config = require $root . '/config/database.php';
    if ($config['host'] !== '127.0.0.1' || $config['database'] !== 'fourtech_admin_test') {
        http_response_code(403);
        exit('Blocked: only 127.0.0.1 / fourtech_admin_test is allowed');
    }
    $db = Database::connection();
    if ($db->query('SELECT DATABASE()')->fetchColumn() !== 'fourtech_admin_test') {
        http_response_code(403);
        exit('Blocked: wrong database');
    }
    session_name('FOURTECH_ADMIN_TEST');
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Strict', 'use_strict_mode' => true]);
    $rbacController = new \App\Controllers\Admin\RbacAdminController(new \App\Repositories\Admin\RbacAdminRepository($db), $root);
    if ($path === '/admin/login') { $rbacController->login($method); return; }
    if ($path === '/admin/logout') { $rbacController->logout(); return; }
    $actor = $rbacController->current();
    if ($actor === null) { header('Location: /admin/login', true, 303); return; }
    $required = \App\Services\Admin\RbacAdminService::routePermission($path, $method);
    if ($required === null || !\App\Services\Admin\RbacAdminService::allowed($actor, $required)) { $rbacController->deny($actor); return; }
    $nav = [];
    foreach ([\App\Services\Admin\RbacAdminService::READ, \App\Services\Admin\RbacAdminService::USERS, \App\Services\Admin\RbacAdminService::RBAC, \App\Services\Admin\RbacAdminService::ORDERS] as $permission) {
        $nav[$permission] = \App\Services\Admin\RbacAdminService::allowed($actor, $permission);
    }
    $_SESSION['task17_nav'] = $nav;
    $_SESSION['task17_csrf'] ??= bin2hex(random_bytes(32));
    if ($rbacRoute) { $rbacController->dispatch($path, $method, $actor); return; }
    $brandModuleEnabled = true;
    $userModuleEnabled = true;
    if (in_array($path, ['/admin', '/admin/dashboard'], true)) {
        $dashboard = (new \App\Repositories\Admin\DashboardAdminRepository($db))->summary($actor);
        $isPreview = true;
        require $root . '/resources/views/admin/dashboard.php';
        return;
    }
    if ($orderOperation !== null) {
        (new \App\Controllers\Admin\OrderAdminController(new \App\Repositories\Admin\OrderAdminRepository($db), $root))->dispatch($orderOperation, $orderId, $actor);
        return;
    }
    if ($userOperation !== null) {
        $userService = new \App\Services\Admin\UserAdminService(new \App\Repositories\Admin\UserAdminRepository($db));
        $userController = new \App\Controllers\Admin\UserAdminController($userService, $root);
        $userController->dispatch($userOperation, $userId, $method);
        return;
    }
    if ($brandRoute) {
        $brandService = new BrandAdminService(new BrandAdminRepository($db));
        require $root . '/docs/admin-db-test-brands.php';
        return;
    }
    if ($categoryRoute) {
        $categoryService = new CategoryAdminService(new CategoryAdminRepository($db));
        require $root . '/docs/admin-db-test-categories.php';
        return;
    }
    $service = new ProductAdminService(new ProductAdminRepository($db));
    $isPreview = true;
    $_SESSION['task145_secret'] ??= bin2hex(random_bytes(32));
    $actionSecret = $_SESSION['task145_secret'];
    if ($method === 'GET' && $path === '/admin/products') {
        $requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        $data = $service->listing($requestedPage === false ? 1 : $requestedPage, 10);
        $products = $data['products'];
        $page = $data['page'];
        $total = $data['total'];
        $totalPages = $data['totalPages'];
        $success = $_SESSION['task143_success'] ?? '';
        unset($_SESSION['task143_success']);
        require $root . '/resources/views/admin/products/database-test-list.php';
        return;
    }
    if ($productId !== null && in_array($operation, ['hide', 'delete'], true)) {
        $expected = hash_hmac('sha256', $operation . ':' . $productId, $actionSecret);
        $token = $_POST['admin_product_action_token'] ?? null;
        if (!is_string($token) || !hash_equals($expected, $token)) {
            http_response_code(403);
            exit('CSRF token khong hop le');
        }
        try {
            if ($operation === 'hide') {
                $service->hide($productId);
                $_SESSION['task143_success'] = 'Ẩn sản phẩm thành công! ID: ' . $productId;
            } else {
                $service->softDelete($productId);
                $_SESSION['task143_success'] = 'Xóa mềm sản phẩm thành công! ID: ' . $productId;
            }
            unset($_SESSION['task145_secret']);
            header('Location: /admin/products', true, 303);
            return;
        } catch (OutOfBoundsException $e) {
            http_response_code(404);
            exit('Không tìm thấy sản phẩm.');
        } catch (InvalidArgumentException $e) {
            http_response_code(409);
            exit(htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        } catch (PDOException $e) {
            error_log('[TASK145 ACTION] SQLSTATE: ' . $e->getCode());
            http_response_code(500);
            exit('Không thực hiện được thao tác. Kiểm tra database test.');
        }
    }
    if ($productId !== null) {
        // A token bound to this product and action cannot be reused for another ID.
        $_SESSION['task144_secret'] ??= bin2hex(random_bytes(32));
        $adminProductToken = hash_hmac('sha256', 'update:' . $productId, $_SESSION['task144_secret']);
        if ($method === 'POST') {
            $token = $_POST['admin_product_token'] ?? null;
            if (!is_string($token) || !hash_equals($adminProductToken, $token)) {
                http_response_code(403);
                exit('CSRF token khong hop le');
            }
        }
        $product = $service->find($productId);
        if ($product === null) {
            http_response_code(404);
            exit('Khong tim thay san pham');
        }
        $old = $product;
        $error = '';
        if ($method === 'POST') {
            $old = $_POST;
            try {
                // The service validates fields and excludes status from UPDATE.
                $service->update($productId, $old);
                unset($_SESSION['task144_secret']);
                $_SESSION['task143_success'] = 'Cập nhật sản phẩm thành công! ID: ' . $productId;
                header('Location: /admin/products', true, 303);
                return;
            } catch (OutOfBoundsException $e) {
                http_response_code(404);
                exit('Khong tim thay san pham');
            } catch (InvalidArgumentException $e) {
                http_response_code(422);
                $error = $e->getMessage();
            } catch (PDOException $e) {
                if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                    http_response_code(409);
                    $error = 'Slug đã tồn tại ở sản phẩm khác.';
                } else {
                    http_response_code(500);
                    $error = 'Không cập nhật được sản phẩm. Kiểm tra database test.';
                }
                error_log('[TASK144 UPDATE] SQLSTATE: ' . $e->getCode());
            }
        }
        $options = $service->formOptions();
        $brands = $options['brands'];
        $categories = $options['categories'];
        require $root . '/resources/views/admin/products/database-test-edit.php';
        return;
    }
    $old = [];
    $error = '';
    $_SESSION['task143_token'] ??= bin2hex(random_bytes(32));
    $adminProductToken = $_SESSION['task143_token'];
    if ($method === 'POST') {
        $token = $_POST['admin_product_token'] ?? null;
        if (!is_string($token) || !hash_equals($adminProductToken, $token)) {
            http_response_code(403);
            exit('CSRF token khong hop le');
        }
        $old = $_POST;
        try {
            $id = $service->create($old);
            unset($_SESSION['task143_token']);
            $_SESSION['task143_success'] = 'Thêm sản phẩm thành công! ID: ' . $id;
            header('Location: /admin/products', true, 303);
            return;
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            $error = $e->getMessage();
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                http_response_code(409);
                $error = 'Dữ liệu bị trùng. Hãy kiểm tra slug và thử lại.';
            } else {
                http_response_code(500);
                $error = 'Không lưu được sản phẩm. Kiểm tra cấu trúc database test.';
            }
            error_log('[TASK143 INSERT] SQLSTATE: ' . $e->getCode());
        }
    }
    $options = $service->formOptions();
    $brands = $options['brands'];
    $categories = $options['categories'];
    require $root . '/resources/views/admin/products/database-test-create.php';
} catch (Throwable $e) {
    http_response_code(500);
    error_log('[ADMIN TEST] ' . get_class($e) . ' code: ' . $e->getCode());
    echo 'Khong the chay Admin test. Kiem tra MySQL local, .env.admin-test va schema test.';
}
