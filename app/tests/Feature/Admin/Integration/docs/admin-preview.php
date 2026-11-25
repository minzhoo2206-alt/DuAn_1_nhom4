<?php

declare(strict_types=1);

// Chỉ sử dụng để xem giao diện local.

if (PHP_SAPI !== 'cli-server') {
    http_response_code(403);
    exit('Preview only');
}

$path = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

if (!in_array($path, ['/', '/admin'], true)) {
    http_response_code(404);
    exit('404 - Khong tim thay trang');
}

$isPreview = true;

require dirname(__DIR__, 5)
    . '/resources/views/admin/dashboard.php';
