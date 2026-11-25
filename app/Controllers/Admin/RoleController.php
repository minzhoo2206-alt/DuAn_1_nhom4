<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\Admin\RoleRepository;
use App\Services\Admin\AdminValidationException;
use App\Services\Admin\RolePermissionService;

final class RoleController extends BaseAdminController
{
    private function service(): RolePermissionService { return new RolePermissionService(new RoleRepository()); }

    public function index(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($response): void {
            $this->authorize('users.view');
            $this->html($response, 'admin/roles/index', $this->service()->listing() + ['pageTitle'=>'Phân quyền tài khoản']);
        });
    }

    public function grant(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($request, $response): void {
            $this->authorize('roles.assign_permissions');
            $this->csrf($request);
            try {
                $this->service()->grant($this->guard()->currentUserId(),
                    (int) $request->input('role_id'), (int) $request->input('permission_id'));
                $this->success($response, 'Đã cấp quyền cho vai trò.', '/admin/roles');
            } catch (AdminValidationException $e) {
                $this->html($response, 'admin/roles/index', $this->service()->listing() + [
                    'pageTitle'=>'Phân quyền tài khoản', 'errors'=>$e->errors(),
                ], 422);
            }
        });
    }

    public function assign(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($request, $response): void {
            $this->authorize('users.assign_roles');
            $this->csrf($request);
            try {
                $this->service()->assign($this->guard()->currentUserId(), (int) $request->input('user_id'), (int) $request->input('role_id'));
                $this->success($response, 'Đã gán quyền.', '/admin/roles');
            } catch (AdminValidationException $e) {
                $this->html($response, 'admin/roles/index', $this->service()->listing() + [
                    'pageTitle'=>'Phân quyền tài khoản', 'errors'=>$e->errors(),
                ], 422);
            }
        });
    }
}
