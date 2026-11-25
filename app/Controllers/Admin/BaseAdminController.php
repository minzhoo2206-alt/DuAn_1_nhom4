<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\Admin\RoleRepository;
use App\Services\Admin\AdminException;
use App\Services\Admin\AdminGuard;
use PDOException;
use Throwable;

abstract class BaseAdminController
{
    private ?AdminGuard $adminGuard = null;

    protected function authorize(string $permission): void
    {
        // Chặn guest trước cả khi truy cập DB.
        if ((int) ($_SESSION['user_id'] ?? 0) < 1) {
            throw new AdminException('Vui lòng đăng nhập trước.', 401);
        }
        $this->guard()->enforce($permission);
    }

    protected function guard(): AdminGuard
    {
        return $this->adminGuard ??= new AdminGuard(new RoleRepository());
    }

    protected function csrf(Request $request): void
    {
        AdminGuard::verifyCsrf($request->input('_csrf'));
    }

    protected function html(Response $response, string $view, array $data = [], int $status = 200): void
    {
        $data['navPermissions'] = $this->guard()->permissions();
        $data['csrfToken'] = AdminGuard::csrfToken();
        $data['flash'] = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);
        $response->html(View::render($view, $data), $status);
    }

    protected function success(Response $response, string $message, string $url): never
    {
        $_SESSION['admin_flash'] = $message;
        $response->redirect($url);
    }

    protected function handle(Response $response, callable $callback): void
    {
        try { $callback(); }
        catch (AdminException $e) {
            $response->html(View::render('admin/error', ['message'=>$e->getMessage(), 'status'=>$e->status()]), $e->status());
        }
        catch (PDOException $e) {
            error_log('FOURTECH Admin database error: '.$e->getMessage());
            $response->html(View::render('admin/error', ['message'=>'Lỗi truy cập dữ liệu. Vui lòng báo quản trị.', 'status'=>500]), 500);
        }
        catch (Throwable $e) {
            error_log('FOURTECH Admin error: '.$e->getMessage());
            $response->html(View::render('admin/error', ['message'=>'Hệ thống tạm thời gặp lỗi.', 'status'=>500]), 500);
        }
    }
}
