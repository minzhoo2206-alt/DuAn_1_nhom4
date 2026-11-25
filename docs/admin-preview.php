<?php

declare(strict_types=1);

// Chỉ chạy bản demo trên PHP Development Server.
if (PHP_SAPI !== 'cli-server') {
    http_response_code(403);
    exit('Preview only');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    exit('Method not allowed');
}

$path = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

$isPreview = true;

$root = dirname(__DIR__);

// Trang Dashboard
if ($path === '/' || $path === '/admin') {

    require $root . '/resources/views/admin/dashboard.php';

    return;
}

// Trang quản lý sản phẩm
if ($path === '/admin/products') {

    // Dữ liệu mẫu, chưa lấy từ MySQL.
    $allProducts = [
        [
            'id' => 1,
            'name' => 'iPhone 16 Pro Max',
            'brand_name' => 'Apple',
            'category_name' => 'Điện thoại',
            'status' => 'active',
        ],
        [
            'id' => 2,
            'name' => 'Samsung Galaxy S25 Ultra',
            'brand_name' => 'Samsung',
            'category_name' => 'Điện thoại',
            'status' => 'active',
        ],
        [
            'id' => 3,
            'name' => 'Xiaomi 15',
            'brand_name' => 'Xiaomi',
            'category_name' => 'Điện thoại',
            'status' => 'active',
        ],
        [
            'id' => 4,
            'name' => 'OPPO Find X8',
            'brand_name' => 'OPPO',
            'category_name' => 'Điện thoại',
            'status' => 'hidden',
        ],
        [
            'id' => 5,
            'name' => 'iPhone 15',
            'brand_name' => 'Apple',
            'category_name' => 'Điện thoại',
            'status' => 'active',
        ],
        [
            'id' => 6,
            'name' => 'Vivo X200',
            'brand_name' => 'Vivo',
            'category_name' => 'Điện thoại',
            'status' => 'draft',
        ],
    ];

    // Phân trang demo: 3 sản phẩm/trang.
    $perPage = 3;

    $total = count($allProducts);

    $totalPages = max(
        1,
        (int) ceil($total / $perPage)
    );

    $page = filter_var(
        $_GET['page'] ?? 1,
        FILTER_VALIDATE_INT
    );

    $page = max(1, (int) $page);

    $page = min($page, $totalPages);

    $offset = ($page - 1) * $perPage;

    $products = array_slice(
        $allProducts,
        $offset,
        $perPage
    );

    require $root
        . '/resources/views/admin/products/index.php';

    return;
}

http_response_code(404);

echo '404 - Khong tim thay trang';
