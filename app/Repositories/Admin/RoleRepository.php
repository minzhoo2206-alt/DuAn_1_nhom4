<?php
declare(strict_types=1);
namespace App\Repositories\Admin;

use App\Core\Database;
use PDO;

/** Contract chờ đối chiếu DDL: roles.name, permissions.name và các bảng liên kết. */
class RoleRepository
{
    public function __construct(protected ?PDO $db = null) { $this->db ??= Database::connection(); }

    public function permissionsForUser(int $userId): array
    {
        $sql = 'SELECT DISTINCT p.name FROM permissions p
                JOIN role_has_permissions rp ON rp.permission_id=p.id
                JOIN user_has_roles ur ON ur.role_id=rp.role_id
                WHERE ur.user_id=:id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function listRoles(): array
    {
        return $this->db->query('SELECT id, name FROM roles ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listUsers(int $limit = 100): array
    {
        $stmt = $this->db->prepare('SELECT id, email FROM users ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rolesForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT role_id FROM user_has_roles WHERE user_id=:id');
        $stmt->execute(['id' => $userId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function listPermissions(): array
    {
        return $this->db->query('SELECT id, name FROM permissions ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function permissionsForRole(int $roleId): array
    {
        $stmt = $this->db->prepare('SELECT permission_id FROM role_has_permissions WHERE role_id=:role_id');
        $stmt->execute(['role_id'=>$roleId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function permissionExists(int $permissionId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM permissions WHERE id=:id LIMIT 1');
        $stmt->execute(['id'=>$permissionId]);
        return $stmt->fetchColumn() !== false;
    }

    public function grantPermission(int $roleId, int $permissionId): void
    {
        $stmt = $this->db->prepare('SELECT role_id FROM role_has_permissions WHERE role_id=:role_id AND permission_id=:permission_id');
        $stmt->execute(['role_id'=>$roleId,'permission_id'=>$permissionId]);
        if ($stmt->fetchColumn() !== false) { return; }
        $stmt = $this->db->prepare('INSERT INTO role_has_permissions (role_id,permission_id) VALUES (:role_id,:permission_id)');
        $stmt->execute(['role_id'=>$roleId,'permission_id'=>$permissionId]);
    }

    public function roleExists(int $roleId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE id=:id LIMIT 1');
        $stmt->execute(['id' => $roleId]);
        return $stmt->fetchColumn() !== false;
    }

    public function userExists(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE id=:id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchColumn() !== false;
    }

    public function assignRole(int $userId, int $roleId): void
    {
        $stmt = $this->db->prepare('SELECT role_id FROM user_has_roles WHERE user_id=:uid AND role_id=:rid');
        $stmt->execute(['uid'=>$userId, 'rid'=>$roleId]);
        if ($stmt->fetchColumn() !== false) { return; }
        $stmt = $this->db->prepare('INSERT INTO user_has_roles (user_id, role_id) VALUES (:uid, :rid)');
        $stmt->execute(['uid'=>$userId, 'rid'=>$roleId]);
    }
}
