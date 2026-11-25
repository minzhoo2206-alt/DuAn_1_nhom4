<?php
declare(strict_types=1);
namespace Database\Seeders;

use PDO;
use RuntimeException;

/** Chỉ dùng sau khi Nhuận xác nhận DDL: name (unique), role_id/permission_id. */
final class RolePermissionSeeder
{
    public const PERMISSIONS = [
        'dashboard.view', 'products.view','products.create','products.update',
        'categories.view','categories.create','categories.update',
        'brands.view','brands.create','brands.update',
        'orders.view','orders.update','users.view','users.assign_roles','roles.assign_permissions',
    ];

    public function run(PDO $db): void
    {
        $db->beginTransaction();
        try {
            $admin = $this->findOrCreate($db, 'roles', 'admin');
            $staff = $this->findOrCreate($db, 'roles', 'staff');
            $this->findOrCreate($db, 'roles', 'customer');
            foreach (self::PERMISSIONS as $name) {
                $permission = $this->findOrCreate($db, 'permissions', $name);
                $this->link($db, $admin, $permission);
                if (in_array($name, ['dashboard.view','products.view','categories.view','brands.view','orders.view'], true)) {
                    $this->link($db, $staff, $permission);
                }
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw new RuntimeException('Seed quyền thất bại: kiểm tra DDL roles/permissions và các bảng pivot.', 0, $e);
        }
    }

    private function findOrCreate(PDO $db, string $table, string $name): int
    {
        // $table là hằng nội bộ, không bao giờ lấy từ input.
        $stmt = $db->prepare("SELECT id FROM {$table} WHERE name=:name LIMIT 1");
        $stmt->execute(['name'=>$name]);
        $id = $stmt->fetchColumn();
        if ($id !== false) { return (int) $id; }
        $stmt = $db->prepare("INSERT INTO {$table} (name) VALUES (:name)");
        $stmt->execute(['name'=>$name]);
        return (int)$db->lastInsertId();
    }

    private function link(PDO $db, int $roleId, int $permissionId): void
    {
        $exists = $db->prepare('SELECT role_id FROM role_has_permissions WHERE role_id=:r AND permission_id=:p LIMIT 1');
        $exists->execute(['r'=>$roleId,'p'=>$permissionId]);
        if ($exists->fetchColumn() !== false) { return; }
        $stmt = $db->prepare('INSERT INTO role_has_permissions (role_id, permission_id) VALUES (:r,:p)');
        $stmt->execute(['r'=>$roleId,'p'=>$permissionId]);
    }
}
