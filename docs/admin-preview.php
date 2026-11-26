<?php

declare(strict_types=1);

use App\Services\Admin\ProductAdminService;

if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Preview only');
}
$root = dirname(__DIR__);
require_once $root . '/app/Services/Admin/ProductAdminService.php';
session_name('FOURTECH_ADMIN_PREVIEW');
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);
$isPreview = true;
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$brands = [ ['id'=>1,'name'=>'Apple'], ['id'=>2,'name'=>'Samsung'], ['id'=>3,'name'=>'Xiaomi'], ['id'=>4,'name'=>'OPPO'], ['id'=>5,'name'=>'Vivo'] ];
$categories = [['id'=>1,'name'=>'Điện thoại']];
$brandNames = array_column($brands, 'name', 'id');
$categoryNames = array_column($categories, 'name', 'id');
if (!isset($_SESSION['products'])) {
    $seed = [
        [1,'iPhone 16 Pro Max','iphone-16-pro-max',1,'active'],
        [2,'Samsung Galaxy S25 Ultra','samsung-galaxy-s25-ultra',2,'active'],
        [3,'Xiaomi 15','xiaomi-15',3,'active'],
        [4,'OPPO Find X8','oppo-find-x8',4,'hidden'],
        [5,'iPhone 15','iphone-15',1,'active'],
        [6,'Vivo X200','vivo-x200',5,'draft'],
    ];
    $_SESSION['products'] = [];
    foreach ($seed as [$id,$name,$slug,$brandId,$status]) {
        $_SESSION['products'][] = ['id'=>$id,'name'=>$name,'slug'=>$slug,'brand_id'=>$brandId,'category_id'=>1,'brand_name'=>$brandNames[$brandId],'category_name'=>'Điện thoại','status'=>$status,'warranty_months'=>12,'description'=>''];
    }
    $_SESSION['next_product_id'] = 7;
}
$_SESSION['fourtech_product_create_token'] ??= bin2hex(random_bytes(32));
$adminProductToken = $_SESSION['fourtech_product_create_token'];

if ($method === 'GET' && ($path === '/' || $path === '/admin')) {
    require $root . '/resources/views/admin/dashboard.php';
    return;
}
if ($method === 'GET' && $path === '/admin/products/create') {
    $old = [];
    $error = '';
    require $root . '/resources/views/admin/products/create.php';
    return;
}
if ($method === 'POST' && $path === '/admin/products') {
    $token = $_POST['admin_product_token'] ?? '';
    if (!is_string($token) || !hash_equals($adminProductToken, $token)) {
        http_response_code(403);
        exit('CSRF token khong hop le');
    }
    try {
        $data = ProductAdminService::validateCreate($_POST, $brands, $categories);
        foreach ($_SESSION['products'] as $product) {
            if ($product['slug'] === $data['slug']) {
                throw new InvalidArgumentException('Slug đã tồn tại.');
            }
        }
        $data['id'] = $_SESSION['next_product_id']++;
        $data['brand_name'] = $brandNames[$data['brand_id']];
        $data['category_name'] = $categoryNames[$data['category_id']];
        array_unshift($_SESSION['products'], $data);
        $_SESSION['success'] = 'Thêm sản phẩm thành công!';
        unset($_SESSION['fourtech_product_create_token']);
        header('Location: /admin/products', true, 303);
        return;
    } catch (InvalidArgumentException $e) {
        http_response_code(422);
        $error = $e->getMessage();
        $old = $_POST;
        require $root . '/resources/views/admin/products/create.php';
        return;
    }
}
// Chỉnh sửa sản phẩm demo theo ID, không tạo bản ghi mới.
if (preg_match('~^/admin/products/([1-9][0-9]*)/(edit|update)$~D', $path ?? '', $matches)) {
    $productId = (int) filter_var($matches[1], FILTER_VALIDATE_INT);
    $action = $matches[2];
    if (($action === 'edit' && $method !== 'GET') || ($action === 'update' && $method !== 'POST')) {
        http_response_code(405);
        exit('Method not allowed');
    }
    $index = array_search($productId, array_column($_SESSION['products'], 'id'), true);
    if ($index === false || !empty($_SESSION['products'][$index]['deleted_at'])) {
        http_response_code(404);
        exit('Không tìm thấy sản phẩm.');
    }
    $_SESSION['fourtech_product_edit_token'] ??= bin2hex(random_bytes(32));
    $adminProductToken = $_SESSION['fourtech_product_edit_token'];
    $old = $_SESSION['products'][$index];
    $error = '';
    if ($action === 'update') {
        $token = $_POST['admin_product_token'] ?? '';
        if (!is_string($token) || !hash_equals($adminProductToken, $token)) {
            http_response_code(403);
            exit('CSRF token khong hop le');
        }
        try {
            $data = ProductAdminService::validateUpdate($_POST, $brands, $categories);
            foreach ($_SESSION['products'] as $product) {
                if ($product['id'] !== $productId && $product['slug'] === $data['slug']) {
                    throw new InvalidArgumentException('Slug đã tồn tại ở sản phẩm khác.');
                }
            }
            $data['brand_name'] = $brandNames[$data['brand_id']];
            $data['category_name'] = $categoryNames[$data['category_id']];
            $_SESSION['products'][$index] = array_replace($old, $data);
            $_SESSION['success'] = 'Cập nhật sản phẩm thành công!';
            unset($_SESSION['fourtech_product_edit_token']);
            header('Location: /admin/products', true, 303);
            return;
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            $old = $_POST;
            $error = $e->getMessage();
        }
    }
    require $root . '/resources/views/admin/products/edit.php';
    return;
}
// Ẩn và xóa mềm chỉ bằng POST; giữ lại dữ liệu đã xóa trong session.
if (preg_match('~^/admin/products/([1-9][0-9]*)/(hide|delete)$~D', $path ?? '', $matches)) {
    if ($method !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Method not allowed');
    }
    $expected = $_SESSION['fourtech_product_action_token'] ?? '';
    $token = $_POST['admin_product_action_token'] ?? '';
    if (!is_string($token) || !is_string($expected) || $expected === '' || !hash_equals($expected, $token)) {
        http_response_code(403);
        exit('CSRF token khong hop le');
    }
    $id = (int) filter_var($matches[1], FILTER_VALIDATE_INT);
    $index = array_search($id, array_column($_SESSION['products'], 'id'), true);
    if ($index === false || !empty($_SESSION['products'][$index]['deleted_at'])) {
        http_response_code(404);
        exit('Không tìm thấy sản phẩm.');
    }
    $action = $matches[2];
    try {
        ProductAdminService::validateLifecycleAction($action, $_SESSION['products'][$index]);
    } catch (InvalidArgumentException $e) {
        http_response_code(409);
        exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
    $now = gmdate('Y-m-d H:i:s');
    if ($action === 'hide') {
        $_SESSION['products'][$index]['status'] = 'hidden';
        $_SESSION['success'] = 'Ẩn sản phẩm thành công!';
    } else {
        $_SESSION['products'][$index]['deleted_at'] = $now;
        $_SESSION['success'] = 'Xóa mềm sản phẩm thành công!';
    }
    $_SESSION['products'][$index]['updated_at'] = $now;
    unset($_SESSION['fourtech_product_action_token']);
    header('Location: /admin/products', true, 303);
    return;
}
if ($method === 'GET' && $path === '/admin/products') {
    $_SESSION['fourtech_product_action_token'] ??= bin2hex(random_bytes(32));
    $actionToken = $_SESSION['fourtech_product_action_token'];
    $allProducts = array_values(array_filter($_SESSION['products'],
        static fn(array $product): bool => empty($product['deleted_at'])));
    $perPage = 3;
    $total = count($allProducts);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = max(1, (int) filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT));
    $page = min($page, $totalPages);
    $products = array_slice($allProducts, ($page - 1) * $perPage, $perPage);
    $success = $_SESSION['success'] ?? '';
    unset($_SESSION['success']);
    require $root . '/resources/views/admin/products/index.php';
    return;
}
http_response_code(404);
echo '404 - Khong tim thay trang';
