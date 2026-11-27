<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/Services/Admin/UserAdminService.php';
use App\Services\Admin\UserAdminService as S;
$n=0;
function check(bool $ok,string $name):void{global $n;if(!$ok){fwrite(STDERR,"FAIL $name\n");exit(1);}++$n;echo "[OK] $name\n";}
function rejected(callable $fn):bool{try{$fn();return false;}catch(InvalidArgumentException){return true;}}
check(S::email(' STAFF@example.test ')==='staff@example.test','normalize email');
check(rejected(fn()=>S::email('wrong')),'reject bad email');
check(rejected(fn()=>S::email([])),'reject array email');
check(rejected(fn()=>S::password('short')),'reject short password');
check(rejected(fn()=>S::password(str_repeat('x',73))),'reject long password');
check(rejected(fn()=>S::password(str_repeat(' ',12))),'reject blank password');
check(rejected(fn()=>S::password("abcdefghijkl\0")),'reject null byte');
$p=bin2hex(random_bytes(12));$h=S::password($p);
check($h!==$p && password_verify($p,$h),'password hash verify');
check(!password_verify('wrong',$h),'wrong password rejected');
check((bool)preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',S::uuid()),'UUID v4');
check(S::uuid()!==S::uuid(),'distinct UUID');
check(S::manageable(['role_codes'=>'task16_staff','status'=>'active']),'active test staff');
check(S::manageable(['role_codes'=>'task16_staff','status'=>'locked']),'locked test staff');
check(!S::manageable(['role_codes'=>'admin','status'=>'active']),'admin protected');
check(!S::manageable(['role_codes'=>'admin,task16_staff','status'=>'active']),'multiple roles protected');
check(!S::manageable(['role_codes'=>null,'status'=>'active']),'unknown role protected');
check(!S::manageable(['role_codes'=>'task16_staff','status'=>'disabled']),'disabled protected');
check(!S::manageable(['role_codes'=>'task16_staff','status'=>'pending_verification']),'pending protected');
echo "PASS $n checks (offline only)\n";
