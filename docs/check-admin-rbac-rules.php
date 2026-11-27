<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/Services/Admin/RbacAdminService.php';
use App\Services\Admin\RbacAdminService as R;
$n=0;
function check(bool $ok,string $name): void {global $n;if(!$ok){fwrite(STDERR,"FAIL $name\n");exit(1);}++$n;echo "[OK] $name\n";}
$all=[R::READ,R::WRITE,R::USERS,R::RBAC];
$admin=['status'=>'active','role'=>R::ADMIN,'permissions'=>$all,'fingerprint'=>'abc'];
$staff=['status'=>'active','role'=>R::STAFF,'permissions'=>[R::READ]];
$staffWrite=array_replace($staff,['permissions'=>$all]);
$customer=['status'=>'active','role'=>R::CUSTOMER,'permissions'=>$all];
foreach($all as $permission)check(R::allowed($admin,$permission),'admin '.$permission);
check(R::allowed($staff,R::READ),'staff read');check(!R::allowed($staff,R::WRITE),'staff cannot write by default');check(R::allowed($staffWrite,R::WRITE),'staff explicit write');
check(!R::allowed($staffWrite,R::USERS),'staff never manage users');check(!R::allowed($staffWrite,R::RBAC),'staff never manage roles');
foreach($all as $permission)check(!R::allowed($customer,$permission),'customer denied '.$permission);
check(!R::allowed(null,R::READ),'anonymous denied');check(!R::allowed(array_replace($admin,['status'=>'locked']),R::RBAC),'locked admin denied');check(!R::allowed(array_replace($admin,['role'=>'unknown']),R::RBAC),'unknown role denied');check(!R::allowed(array_replace($admin,['permissions'=>[]]),R::RBAC),'missing permission denied');
foreach(['products','categories','brands'] as $module){check(R::routePermission('/admin/'.$module,'GET')===R::READ,$module.' GET read');check(R::routePermission('/admin/'.$module,'POST')===R::WRITE,$module.' POST write');check(R::routePermission('/admin/'.$module.'/create','GET')===R::WRITE,$module.' create form');check(R::routePermission('/admin/'.$module.'/1/edit','GET')===R::WRITE,$module.' edit form');check(R::routePermission('/admin/'.$module.'/1/delete','POST')===R::WRITE,$module.' delete POST');}
check(R::routePermission('/admin/users/1/reset','POST')===R::USERS,'reset guarded');check(R::routePermission('/admin/rbac/role','POST')===R::RBAC,'role assignment guarded');check(R::routePermission('/admin/rbac/staff-write','POST')===R::RBAC,'permission grant guarded');check(R::routePermission('/admin/unknown','GET')===null,'unknown route deny');
check(R::testEmail('admin17@example.test'),'test email allowed');check(!R::testEmail('admin@example.com'),'real email denied');
check(R::sessionValid($admin,'abc'),'valid session');check(!R::sessionValid($admin,'changed'),'password changed invalidates session');check(!R::sessionValid(array_replace($admin,['status'=>'locked']),'abc'),'locked invalidates session');
echo "PASS $n checks (offline only)\n";
