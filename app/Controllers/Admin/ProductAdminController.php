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
        $_SESSION['fourtech_product_action_token'] ??= bin2hex(random_bytes(32));
        $data['actionToken'] = $_SESSION['fourtech_product_action_token'];
        $data['success'] = $_SESSION['fourtech_product_action_success'] ?? '';
        unset($_SESSION['fourtech_product_action_success']);

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


    public function edit(Request $request, Response $response, array $params = []): void
    {
        $id = (int) filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        $product = $this->service->find($id);
        if ($product === null) {
            $response->html('Không tìm thấy sản phẩm.', 404);
            return;
        }
        $this->renderEdit($response, $id, $product);
    }

    public function update(Request $request, Response $response, array $params = []): void
    {
        $id = (int) filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        $token = $request->input('admin_product_token', '');
        $expected = $_SESSION['fourtech_product_edit_token'] ?? '';
        if (!is_string($token) || !is_string($expected) || $expected === '' || !hash_equals($expected, $token)) {
            $response->html('CSRF token khong hop le', 403);
            return;
        }
        $input = [];
        foreach (['name', 'slug', 'brand_id', 'category_id', 'description', 'warranty_months'] as $key) {
            $input[$key] = $request->input($key, '');
        }
        try {
            $this->service->update($id, $input);
        } catch (\OutOfBoundsException $e) {
            $response->html('Không tìm thấy sản phẩm.', 404);
            return;
        } catch (InvalidArgumentException $e) {
            $this->renderEdit($response, $id, $input, $e->getMessage(), 422);
            return;
        } catch (PDOException $e) {
            error_log('Admin product update failed; SQLSTATE: ' . $e->getCode());
            $this->renderEdit($response, $id, $input, 'Không lưu được sản phẩm. Vui lòng thử lại.', 500);
            return;
        }
        unset($_SESSION['fourtech_product_edit_token']);
        $response->redirect('/admin/products', 303);
    }

    private function renderEdit(Response $response, int $id, array $old, string $error = '', int $status = 200): void
    {
        $_SESSION['fourtech_product_edit_token'] ??= bin2hex(random_bytes(32));
        $data = $this->service->formOptions();
        $data['productId'] = $id;
        $data['old'] = $old;
        $data['error'] = $error;
        $data['adminProductToken'] = $_SESSION['fourtech_product_edit_token'];
        $response->html(View::render('admin/products/edit', $data), $status);
    }


    // Minh đăng ký các route POST với middleware xác thực, phân quyền và CSRF.
    public function hide(Request $request, Response $response, array $params = []): void
    {
        $this->lifecycleAction('hide', $request, $response, $params);
    }

    public function softDelete(Request $request, Response $response, array $params = []): void
    {
        $this->lifecycleAction('delete', $request, $response, $params);
    }

    private function lifecycleAction(string $action, Request $request, Response $response, array $params): void
    {
        $id = (int) filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        $token = $request->input('admin_product_action_token', '');
        $expected = $_SESSION['fourtech_product_action_token'] ?? '';
        if (!is_string($token) || !is_string($expected) || $expected === '' || !hash_equals($expected, $token)) {
            $response->html('CSRF token khong hop le', 403);
            return;
        }
        try {
            if ($action === 'hide') {
                $this->service->hide($id);
            } else {
                $this->service->softDelete($id);
            }
        } catch (\OutOfBoundsException $e) {
            $response->html('Không tìm thấy sản phẩm.', 404);
            return;
        } catch (InvalidArgumentException $e) {
            $response->html(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'), 409);
            return;
        } catch (PDOException $e) {
            error_log('Admin product action failed; SQLSTATE: ' . $e->getCode());
            $response->html('Không thực hiện được thao tác.', 500);
            return;
        }
        $_SESSION['fourtech_product_action_success'] = $action === 'hide'
            ? 'Ẩn sản phẩm thành công!' : 'Xóa mềm sản phẩm thành công!';
        unset($_SESSION['fourtech_product_action_token']);
        $response->redirect('/admin/products', 303);
    }

}
