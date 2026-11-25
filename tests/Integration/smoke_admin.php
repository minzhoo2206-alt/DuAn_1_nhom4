<?php
declare(strict_types=1);
/** Smoke test độc lập, không cần Composer/DB driver/PHPUnit. Lệnh: php tests/Integration/smoke_admin.php */
$root = dirname(__DIR__, 2);
foreach ([
    'app/Services/Admin/AdminException.php',
    'app/Services/Admin/AdminValidationException.php',
    'app/Repositories/Admin/CatalogAdminRepository.php',
    'app/Services/Admin/CatalogAdminService.php',
] as $file) { require_once $root.'/'.$file; }

use App\Repositories\Admin\CatalogAdminRepository;
use App\Services\Admin\AdminException;
use App\Services\Admin\AdminValidationException;
use App\Services\Admin\CatalogAdminService;

$repository = new class extends CatalogAdminRepository {
    public array $items = [1=>['id'=>1,'name'=>'Điện thoại mẫu','slug'=>'demo','status'=>'active']];
    public function __construct() {}
    public function find(string $kind, int $id): ?array { return $this->items[$id] ?? null; }
    public function slugExists(string $kind, string $slug, ?int $exceptId=null): bool {
        foreach($this->items as $item) if($item['slug']===$slug && $item['id']!==$exceptId) return true;
        return false;
    }
    public function create(string $kind, array $data): int { $id=count($this->items)+1; $this->items[$id]=$data+['id'=>$id]; return $id; }
    public function update(string $kind,int $id,array $data): void { $this->items[$id]=$data+['id'=>$id]; }
    public function setStatus(string $kind,int $id,string $status): void { $this->items[$id]['status']=$status; }
};
$service=new CatalogAdminService($repository);
$tests = [];
$tests['create ok'] = $service->create('products', ['name'=>'Điện thoại B','slug'=>'dien-thoai-b','status'=>'draft'])===2;
try { $service->create('products', ['name'=>'','slug'=>'name','status'=>'draft']); $tests['empty name 422']=false; }
catch(AdminValidationException) { $tests['empty name 422']=true; }
try { $service->create('products', ['name'=>'bad','slug'=>'demo','status'=>'draft']); $tests['duplicate slug']=false; }
catch(AdminValidationException) { $tests['duplicate slug']=true; }
try { $service->find('products',999); $tests['missing id 404']=false; }
catch(AdminException $e) { $tests['missing id 404']=$e->status()===404; }
$tests['toggle status'] = $service->toggle('products',1)==='inactive';
foreach($tests as $label=>$passed) { echo ($passed?'PASS':'FAIL').' '.$label.PHP_EOL; }
exit(in_array(false,$tests,true)?1:0);
