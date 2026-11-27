<?php

declare(strict_types=1);

use App\Core\Database;
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
if ($method === 'GET' && in_array($path, ['/', '/admin'], true)) {
    header('Location: /admin/products', true, 302);
    return;
}
// Keep the Task 12 route convention: GET /{id}/edit, POST /{id}/update.
$productId = null;
$operation = null;
if (is_string($path) && preg_match('~^/admin/products/([1-9][0-9]*)/(edit|update)$~D', $path, $match)) {
    $productId = filter_var($match[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $operation = $match[2];
    if ($productId === false) {
        http_response_code(404);
        exit('Khong tim thay san pham');
    }
    $allowed = $operation === 'edit' ? ['GET'] : ['POST'];
} elseif ($path === '/admin/products') {
    $allowed = ['GET', 'POST'];
} elseif ($path === '/admin/products/create') {
    $allowed = ['GET'];
} else {
    http_response_code(404);
    exit('Route not available in Task 14.4');
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
    $service = new ProductAdminService(new ProductAdminRepository($db));
    $isPreview = true;
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
    error_log('[TASK144] ' . get_class($e) . ' code: ' . $e->getCode());
    echo 'Khong the chay Task 14.4. Kiem tra MySQL local, .env.admin-test va schema test.';
}
