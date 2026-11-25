<?php
declare(strict_types=1);
namespace App\Services\Admin;

use App\Repositories\Admin\RoleRepository;

final class RolePermissionService
{
    public function __construct(private RoleRepository $roles) {}

    public function listing(): array
    {
        $users = $this->roles->listUsers();
        foreach ($users as &$user) { $user['role_ids'] = $this->roles->rolesForUser((int) $user['id']); }
        unset($user);
        $roles = $this->roles->listRoles();
        $rolePermissions = [];
        foreach ($roles as $role) { $rolePermissions[$role['id']] = $this->roles->permissionsForRole((int) $role['id']); }
        return ['users'=>$users, 'roles'=>$roles,
                'permissions'=>$this->roles->listPermissions(), 'rolePermissions'=>$rolePermissions];
    }

    public function grant(int $actorId, int $roleId, int $permissionId): void
    {
        if ($actorId < 1 || $roleId < 1 || $permissionId < 1) {
            throw new AdminValidationException(['permission' => 'Vai trò/quyền không hợp lệ.']);
        }
        // Không cho sửa role đang gắn với bản thân: ngăn tự tăng quyền gián tiếp.
        if (in_array($roleId, $this->roles->rolesForUser($actorId), true)) {
            throw new AdminException('Không được cấp thêm quyền cho vai trò của chính mình.', 403);
        }
        if (!$this->roles->roleExists($roleId) || !$this->roles->permissionExists($permissionId)) {
            throw new AdminException('Không tìm thấy vai trò hoặc quyền.', 404);
        }
        $this->roles->grantPermission($roleId, $permissionId);
    }

    public function assign(int $actorId, int $targetId, int $roleId): void
    {
        if ($actorId < 1 || $targetId < 1 || $roleId < 1) {
            throw new AdminValidationException(['role' => 'Tài khoản/vai trò không hợp lệ.']);
        }
        if ($actorId === $targetId) { throw new AdminException('Không tự cấp quyền cho chính mình.', 403); }
        if (!$this->roles->userExists($targetId) || !$this->roles->roleExists($roleId)) {
            throw new AdminException('Không tìm thấy tài khoản hoặc vai trò.', 404);
        }
        $this->roles->assignRole($targetId, $roleId);
    }
}
