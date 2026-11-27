<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$root = dirname(__DIR__);
require $root.'/app/Repositories/Admin/ProductAdminRepository.php';
require $root.'/app/Services/Admin/ProductAdminService.php';
require $root.'/app/Services/Admin/RbacAdminService.php';
use App\Repositories\Admin\ProductAdminRepository as R;
use App\Services\Admin\ProductAdminService as P;
use App\Services\Admin\RbacAdminService as B;
$n=0;
function check(bool $ok,string $label):void {global $n; if(!$ok){echo "FAIL $label\n";exit(1);}++$n;echo "[OK] $label\n";}
function rejected(callable $fn):bool {try{$fn();return false;}catch(InvalidArgumentException|OutOfBoundsException){return true;}}
$f=P::normalizeFilters(['q'=>' Apple ','brand_id'=>'2','category_id'=>'3','status'=>'draft']);
check($f===['q'=>'Apple','brand_id'=>2,'category_id'=>3,'status'=>'draft'],'combined filters');
check(P::normalizeFilters([])===['q'=>'','brand_id'=>0,'category_id'=>0,'status'=>''],'empty filters');
check(rejected(fn()=>P::normalizeFilters(['q'=>str_repeat('a',201)])),'oversize search');
check(rejected(fn()=>P::normalizeFilters(['brand_id'=>-1])),'negative brand');
check(rejected(fn()=>P::normalizeFilters(['category_id'=>['2']])),'array category');
check(rejected(fn()=>P::normalizeFilters(['status'=>'deleted'])),'invalid status');
check(rejected(fn()=>P::normalizeFilters(['status'=>['draft']])),'array status');
[$where,$params]=R::filterPredicate($f);
check(str_contains($where,'p.deleted_at IS NULL') && str_contains($where,'p.brand_id = :brand_id') && str_contains($where,'p.category_id = :category_id') && str_contains($where,'p.status = :status'),'combined SQL predicate');
check($params[':name_q']==='%Apple%' && $params[':slug_q']==='%Apple%' && $params[':brand_id']===2,'distinct bound placeholders');
[$where,$params]=R::filterPredicate(P::normalizeFilters(['q'=>"%_!' OR 1=1"]));
check(!str_contains($where,'OR 1=1') && $params[':name_q']==="%!%!_!!' OR 1=1%",'literal wildcard and injection-like term bound');
check(R::filterPredicate([])===['p.deleted_at IS NULL',[]],'legacy unfiltered predicate');
foreach(['draft','hidden'] as $s){P::validateLifecycleAction('activate',['status'=>$s]);check(true,'activate '.$s);}
check(rejected(fn()=>P::validateLifecycleAction('activate',['status'=>'active'])),'deny repeated activation');
check(rejected(fn()=>P::validateLifecycleAction('activate',['status'=>'hidden','deleted_at'=>'2026-01-01'])),'deny deleted activation');
check(B::routePermission('/admin/products/1/activate','POST')===B::WRITE,'activation needs write permission');
$staff=['status'=>'active','role'=>B::STAFF,'permissions'=>[B::READ]];
check(!B::allowed($staff,B::WRITE),'read-only staff denied');
check(B::allowed(array_replace($staff,['permissions'=>[B::READ,B::WRITE]]),B::WRITE),'authorized staff allowed');
function renderList(string $root,bool $canWrite):string {
    $products=[['id'=>1,'name'=>'<script>alert(1)</script>','brand_name'=>'Apple','category_name'=>'Phone','status'=>'draft']];
    $total=21;$totalPages=3;$page=1;$success='';$isPreview=true;$actionSecret='test-only';
    $filters=P::normalizeFilters(['q'=>'<b>Apple</b>','brand_id'=>'2','category_id'=>'3','status'=>'draft']);
    $filterOptions=['brands'=>[['id'=>2,'name'=>'Apple']],'categories'=>[['id'=>3,'name'=>'Phone']]];
    ob_start();require $root.'/resources/views/admin/products/database-test-list.php';return (string)ob_get_clean();
}
$html=renderList($root,true);
check(str_contains($html,'/admin/products/1/activate') && str_contains($html,hash_hmac('sha256','activate:1','test-only')),'activation form and action-specific CSRF');
check(str_contains($html,'brand_id=2&amp;category_id=3&amp;status=draft&amp;page=2'),'pagination keeps filters');
check(!str_contains($html,'<script>alert(1)</script>') && str_contains($html,'&lt;script&gt;alert(1)&lt;/script&gt;'),'escape product name');
check(str_contains($html,'&lt;b&gt;Apple&lt;/b&gt;'),'escape search value');
$html=renderList($root,false);
check(!str_contains($html,'/admin/products/1/activate') && !str_contains($html,'/admin/products/1/edit') && !str_contains($html,'/admin/products/create'),'read-only UI hides write actions');
echo "PASS $n TASK 21 checks (offline only)\n";
