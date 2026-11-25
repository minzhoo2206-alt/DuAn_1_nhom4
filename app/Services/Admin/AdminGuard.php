<?php
declare(strict_types=1);
namespace App\Services\Admin;

use App\Repositories\Admin\RoleRepository;

/** Fail closed: role luôn lấy từ DB trên server, không tin role do client gửi. */
final class AdminGuard
{
    private ?array $loadedPermissions = null;
    public function __construct(private RoleRepository $roles) {}

    public function currentUserId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public function permissions(): array
    {
        $id = $this->currentUserId();
        if ($id < 1) { return []; }
        return $this->loadedPermissions ??= $this->roles->permissionsForUser($id);
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }

    public function enforce(string $permission): void
    {
        if ($this->currentUserId() < 1) { throw new AdminException('Vui lòng đăng nhập trước khi truy cập Admin.', 401); }
        if (!$this->can($permission)) { throw new AdminException('Bạn không có quyền thực hiện thao tác này.', 403); }
    }

    public static function csrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { throw new AdminException('Phiên đăng nhập chưa được khởi tạo.', 500); }
        if (!isset($_SESSION['admin_csrf'])) { $_SESSION['admin_csrf'] = bin2hex(random_bytes(32)); }
        return (string) $_SESSION['admin_csrf'];
    }

    public static function verifyCsrf(mixed $provided): void
    {
        $expected = $_SESSION['admin_csrf'] ?? null;
        if (!is_string($expected) || !is_string($provided) || !hash_equals($expected, $provided)) {
            throw new AdminException('Phiên biểu mẫu không hợp lệ. Tải lại trang và thử lại.', 419);
        }
    }
}
