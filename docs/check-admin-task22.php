<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$root=dirname(__DIR__);
require $root.'/app/Services/Admin/CatalogDetailAdminService.php';
require $root.'/app/Services/Admin/RbacAdminService.php';
use App\Services\Admin\CatalogDetailAdminService as S;
use App\Services\Admin\RbacAdminService as B;
$n=0;
function check(bool $ok,string $name):void{global $n;if(!$ok){echo "FAIL $name\n";exit(1);}++$n;echo "[OK] $name\n";}
function rejects(callable $f):bool{try{$f();return false;}catch(InvalidArgumentException){return true;}}
$base=['storage_gb'=>'256','ram_gb'=>'8','list_price'=>'29990000','sale_price'=>'','sale_starts_at'=>'','sale_ends_at'=>'','is_active'=>'1'];
$c=S::config($base);check($c['list_price']===29990000&&$c['sale_price']===null,'integer VND and optional sale');
$promo=array_replace($base,['sale_price'=>'27990000','sale_starts_at'=>'2026-11-27T10:00','sale_ends_at'=>'2026-12-01T10:00']);
check(S::config($promo)['sale_starts_at']==='2026-11-27 10:00:00','sale date normalization');
foreach([['list_price'=>'0'],['list_price'=>'-1'],['list_price'=>'1.5'],['storage_gb'=>'65536'],['ram_gb'=>[]],['is_active'=>'2'],['sale_price'=>'29990000'],['sale_price'=>'30000000'],['sale_ends_at'=>'2026-11-26T10:00'],['sale_ends_at'=>'2026-02-30T10:00'],['sale_starts_at'=>'']] as $change)check(rejects(fn()=>S::config(array_replace($promo,$change))),'reject '.array_key_first($change).' '.json_encode(array_values($change)[0]));
check(rejects(fn()=>S::config(array_replace($base,['sale_starts_at'=>'2026-11-27T10:00']))),'reject date without sale');
$v=['configuration_id'=>'1','color_id'=>'2','sku'=>'ip16-256-black','is_active'=>'1'];check(S::variant($v)['sku']==='IP16-256-BLACK','normalize SKU');
foreach(['<script>','a b','',str_repeat('A',81)] as $bad)check(rejects(fn()=>S::variant(array_replace($v,['sku'=>$bad]))),'reject invalid SKU');
check(S::imageExtension('image/jpeg')==='jpg'&&S::imageExtension('image/png')==='png'&&S::imageExtension('image/webp')==='webp','allowed image MIME');
check(rejects(fn()=>S::imageExtension('image/svg+xml')),'deny SVG');check(rejects(fn()=>S::imageExtension('text/html')),'deny HTML');
check(S::validKey(str_repeat('a',64).'.png')&&!S::validKey('../a.php')&&!S::validKey(str_repeat('a',64).'.php'),'safe storage keys');
check((bool)preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D',S::uuid()),'UUID v4');
check(B::routePermission('/admin/products/1/catalog','GET')===B::READ&&B::routePermission('/admin/products/1/catalog','POST')===B::WRITE,'read/write route permission');
check(!B::allowed(['status'=>'active','role'=>B::STAFF,'permissions'=>[B::READ]],B::WRITE),'deny read-only staff write');
check(B::routePermission('/admin/products/1/catalog/image/2','GET')===B::READ,'image requires catalog read');
function render(string $root,bool $canWrite):string{$product=['id'=>1,'name'=>'<script>test</script>'];$configurations=[['id'=>1,'storage_gb'=>256,'ram_gb'=>8,'list_price'=>'29990000.00','sale_price'=>null,'sale_starts_at'=>null,'sale_ends_at'=>null,'is_active'=>1]];$variants=[['id'=>1,'configuration_id'=>1,'color_id'=>1,'color_name'=>'Đen','sku'=>'TEST-1','is_active'=>1]];$colors=[['id'=>1,'name'=>'Đen']];$images=[['id'=>1,'alt_text'=>'<b>test</b>','sort_order'=>0]];$token='csrf-demo';$ready=true;$error=$success='';$isPreview=true;ob_start();require $root.'/resources/views/admin/products/database-test-catalog.php';return (string)ob_get_clean();}
$html=render($root,true);check(str_contains($html,'name="list_price"')&&str_contains($html,'name="sku"')&&str_contains($html,'multipart/form-data')&&str_contains($html,'csrf-demo'),'write forms and CSRF');
check(!str_contains($html,'<script>test</script>')&&str_contains($html,'&lt;b&gt;test&lt;/b&gt;'),'escape product name and image alt');
$html=render($root,false);check(!str_contains($html,'name="list_price"')&&!str_contains($html,'name="sku"')&&!str_contains($html,'multipart/form-data')&&str_contains($html,'TEST-1'),'read-only view');
$repo=file_get_contents($root.'/app/Repositories/Admin/ProductAdminRepository.php');check(str_contains($repo,'AND EXISTS (SELECT 1 FROM product_configurations')&&str_contains($repo,'f.list_price>0')&&str_contains($repo,'v.is_active=1'),'activation SQL requires configuration and variant (static check)');
echo "PASS $n TASK 22 checks (offline only)\n";
