<?php
declare(strict_types=1);
namespace App\Services\Admin;

final class RbacAdminService
{
    public const ADMIN='task17_admin';
    public const STAFF='task16_staff';
    public const CUSTOMER='task17_customer';
    public const READ='task17.catalog.read';
    public const WRITE='task17.catalog.write';
    public const USERS='task17.users.manage';
    public const RBAC='task17.rbac.manage';
    public const ORDERS='task18.orders.manage';
    public static function allowed(?array $actor, string $permission): bool
    {
        if (!$actor || ($actor['status']??'')!=='active') return false;
        $ceiling=match($actor['role']??'') { self::ADMIN=>[self::READ,self::WRITE,self::USERS,self::RBAC,self::ORDERS], self::STAFF=>[self::READ,self::WRITE], default=>[] };
        return in_array($permission,$ceiling,true) && in_array($permission,$actor['permissions']??[],true);
    }
    public static function routePermission(string $path,string $method): ?string
    {
        if (in_array($path, ['/admin', '/admin/dashboard'], true)) return $method === 'GET' ? self::READ : null;
        if (preg_match('~^/admin/(products|categories|brands)(?:/|$)~D',$path)) {
            return $method==='GET' && !preg_match('~/(create|edit)$~D',$path) ? self::READ : self::WRITE;
        }
        if (preg_match('~^/admin/orders(?:/|$)~D',$path)) return self::ORDERS;
        if (preg_match('~^/admin/users(?:/|$)~D',$path)) return self::USERS;
        if (preg_match('~^/admin/rbac(?:/|$)~D',$path)) return self::RBAC;
        return null;
    }
    public static function testEmail(string $email): bool { return str_ends_with(strtolower($email),'@example.test'); }
    public static function sessionValid(?array $actor,mixed $fingerprint): bool
    { return $actor!==null && ($actor['status']??'')==='active' && is_string($fingerprint) && hash_equals($actor['fingerprint']??'', $fingerprint); }
}
