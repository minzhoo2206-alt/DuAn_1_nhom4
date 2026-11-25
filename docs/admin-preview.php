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
if ($method === 'GET' && $path === '/admin/products') {
    $allProducts = $_SESSION['products'];
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
