<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__).'/app/Services/Admin/ProductAdminService.php';
require dirname(__DIR__).'/app/Services/Admin/CategoryAdminService.php';
use App\Services\Admin\ProductAdminService as P;
use App\Services\Admin\CategoryAdminService as C;
$n=0;
function check(bool $ok,string $label):void{global $n;if(!$ok){echo "FAIL $label\n";exit(1);}++$n;echo "[OK] $label\n";}
function rejected(callable $fn):bool{try{$fn();return false;}catch(InvalidArgumentException|OutOfBoundsException){return true;}}
$input=['name'=>'Demo','slug'=>'demo-phone','brand_id'=>1,'category_id'=>1,'description'=>'Demo','warranty_months'=>12];$options=[['id'=>1]];
check(P::validateCreate($input,$options,$options)['status']==='draft','create defaults draft');
check(!array_key_exists('status',P::validateUpdate($input,$options,$options)),'update preserves status');
foreach(['name'=>'','slug'=>'Bad Slug','brand_id'=>99,'category_id'=>99,'warranty_months'=>-1] as $k=>$v)check(rejected(fn()=>P::validateCreate(array_replace($input,[$k=>$v]),$options,$options)),'reject '.$k);
P::validateLifecycleAction('hide',['status'=>'active']);check(true,'hide active');
P::validateLifecycleAction('delete',['status'=>'hidden']);check(true,'delete hidden');
P::validateLifecycleAction('delete',['status'=>'draft']);check(true,'delete draft');
check(rejected(fn()=>P::validateLifecycleAction('delete',['status'=>'active'])),'deny active delete');
check(rejected(fn()=>P::validateLifecycleAction('hide',['status'=>'active','deleted_at'=>'2026-01-01'])),'deny deleted row');
check(C::validate(['name'=>'Demo','slug'=>'demo'])['slug']==='demo','valid category');
check(rejected(fn()=>C::validate(['name'=>'','slug'=>'demo'])),'deny empty category');
C::validateAction('activate',['is_active'=>0],false);check(true,'activate category');
C::validateAction('deactivate',['is_active'=>1],false);check(true,'deactivate empty category');
check(rejected(fn()=>C::validateAction('deactivate',['is_active'=>1],true)),'deny category with products');
check(rejected(fn()=>C::validateAction('delete',['is_active'=>0],true)),'deny delete category with products');
echo "PASS $n catalog checks (offline only)\n";
