<?php

declare(strict_types=1);

namespace App\Controllers\Store;

use App\Core\Request;
use App\Core\Response;

class ContactController
{
    public function index(Request $request, Response $response, array $params): void
    {
        // Đi từ thư mục Store lên thư mục gốc của dự án
        $root = dirname(__DIR__, 3);

        // Mở giao diện Liên hệ mà Tín đã làm
        require $root . '/View/Pages/contact.php';
    }
}