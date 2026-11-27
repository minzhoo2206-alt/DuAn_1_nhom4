<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/Services/Admin/OrderAdminService.php';
require dirname(__DIR__).'/app/Services/Admin/RbacAdminService.php';
use App\Services\Admin\OrderAdminService as O;
use App\Services\Admin\RbacAdminService as R;
$n=0;
function check(bool $ok,string $label):void{global $n;if(!$ok){fwrite(STDERR,"FAIL $label\n");exit(1);}++$n;echo "[OK] $label\n";}
function rejects(string $a,string $b,string $reason):bool{try{O::validate($a,$b,$reason);return false;}catch(DomainException){return true;}}
foreach([['pending_confirmation','confirmed'],['confirmed','packing'],['packing','shipped'],['shipped','delivered'],['shipped','delivery_failed'],['delivery_failed','returning'],['returning','returned'],['pending_confirmation','cancelled'],['confirmed','cancelled'],['packing','cancelled']] as [$a,$b]){O::validate($a,$b,'test');check(true,"$a -> $b");}
foreach([['pending_confirmation','delivered',''],['shipped','cancelled','test'],['cancelled','confirmed','test'],['delivered','packing','test'],['confirmed','cancelled',''],['shipped','delivery_failed',''],['confirmed','packing',str_repeat('x',256)]] as [$a,$b,$r])check(rejects($a,$b,$r),"deny $a -> $b");
foreach(['delivered','returned','cancelled','expired','rejected','unknown'] as $s)check(O::next($s)===[],"terminal $s");
foreach([R::ADMIN,R::STAFF,R::CUSTOMER,'unknown'] as $role)check(R::allowed(['status'=>'active','role'=>$role,'permissions'=>[R::ORDERS]],R::ORDERS)===($role===R::ADMIN),"order access $role");
check(!R::allowed(['status'=>'locked','role'=>R::ADMIN,'permissions'=>[R::ORDERS]],R::ORDERS),'locked admin denied');check(!R::allowed(['status'=>'active','role'=>R::ADMIN,'permissions'=>[]],R::ORDERS),'missing permission denied');check(!R::allowed(null,R::ORDERS),'anonymous denied');
foreach(['/admin/orders','/admin/orders/1','/admin/orders/1/update'] as $path)check(R::routePermission($path,'POST')===R::ORDERS,"route $path");
echo "PASS $n checks (offline only)\n";
