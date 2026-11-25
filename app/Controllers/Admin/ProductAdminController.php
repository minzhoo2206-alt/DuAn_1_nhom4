<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\Admin\ProductAdminRepository;
use App\Services\Admin\ProductAdminService;

use InvalidArgumentException;
use PDOException;
final class ProductAdminController
{
    private ProductAdminService $service;

    public function __construct()
    {
        $repository = new ProductAdminRepository(
            Database::connection()
        );

        $this->service = new ProductAdminService($repository);
    }

    public function index(
        Request $request,
        Response $response,
        array $params = []
    ): void {
        $page = filter_var(
            $request->input('page', 1),
            FILTER_VALIDATE_INT
        );

        $page = $page === false ? 1 : $page;

        $data = $this->service->listing($page, 20);

        $data['pageTitle'] = 'Quản lý sản phẩm';

        $response->html(
            View::render('admin/products/index', $data)
        );
    }

    // Chỉ đăng ký route sau khi Minh tích hợp middleware auth/permission/CSRF.
    public function create(Request $request, Response $response, array $params = []): void
    {
        $this->renderCreate($response);
    }

    public function store(Request $request, Response $response, array $params = []): void
    {
        $token = $request->input('admin_product_token', '');
        $expected = $_SESSION['fourtech_product_create_token'] ?? '';
        if (!is_string($token) || !is_string($expected) || $expected === '' || !hash_equals($expected, $token)) {
            $response->html('CSRF token khong hop le', 403);
            return;
        }
        $input = [];
        foreach (['name', 'slug', 'brand_id', 'category_id', 'description', 'warranty_months'] as $key) {
            $input[$key] = $request->input($key, '');
        }
        try {
            $this->service->create($input);
        } catch (InvalidArgumentException $e) {
            $this->renderCreate($response, $input, $e->getMessage(), 422);
            return;
        } catch (PDOException $e) {
            // Không hiển thị SQL hay thông tin kết nối cho người dùng.
            error_log('Admin product create failed; SQLSTATE: ' . $e->getCode());
            $this->renderCreate($response, $input, 'Không lưu được sản phẩm. Kiểm tra dữ liệu hoặc liên hệ quản trị.', 500);
            return;
        }
        unset($_SESSION['fourtech_product_create_token']);
        $response->redirect('/admin/products', 303);
    }

    private function renderCreate(Response $response, array $old = [], string $error = '', int $status = 200): void
    {
        $_SESSION['fourtech_product_create_token'] ??= bin2hex(random_bytes(32));
        $data = $this->service->formOptions();
        $data['old'] = $old;
        $data['error'] = $error;
        $data['adminProductToken'] = $_SESSION['fourtech_product_create_token'];
        $response->html(View::render('admin/products/create', $data), $status);
    }

}
