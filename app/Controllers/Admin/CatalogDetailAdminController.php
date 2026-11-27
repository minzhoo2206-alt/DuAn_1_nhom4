<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Repositories\Admin\CatalogDetailAdminRepository;
use App\Services\Admin\CatalogDetailAdminService;
use App\Services\Admin\RbacAdminService;
final class CatalogDetailAdminController {
    public function __construct(private CatalogDetailAdminRepository $repo,private CatalogDetailAdminService $service,private string $root) {}
    public function dispatch(int $id,string $method,array $actor,?int $imageId): void {
        $product=$this->repo->product($id);if(!$product){http_response_code(404);echo 'Không tìm thấy sản phẩm.';return;}
        if($imageId!==null){$path=$this->service->imagePath($id,$imageId);if(!$path){http_response_code(404);return;}$mime=match(pathinfo($path,PATHINFO_EXTENSION)){'jpg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'};header('Content-Type: '.$mime);header('X-Content-Type-Options: nosniff');header('Content-Security-Policy: default-src \'none\'; sandbox');readfile($path);return;}
        $canWrite=RbacAdminService::allowed($actor,RbacAdminService::WRITE);$_SESSION['task22_secret']??=bin2hex(random_bytes(32));$token=hash_hmac('sha256','catalog:'.$id,$_SESSION['task22_secret']);$error='';$success=$_SESSION['task22_success']??'';unset($_SESSION['task22_success']);
        if($method==='POST'){
            if(!$canWrite||!is_string($_POST['token']??null)||!hash_equals($token,$_POST['token'])){http_response_code(403);echo 'Không đủ quyền hoặc CSRF không hợp lệ.';return;}
            try{$this->service->mutate($id,$_POST,$_FILES);$_SESSION['task22_success']='Đã lưu thay đổi.';header('Location: /admin/products/'.$id.'/catalog',true,303);return;}
            catch(\InvalidArgumentException|\OutOfBoundsException $e){http_response_code(422);$error=$e->getMessage();}
            catch(\PDOException $e){http_response_code((int)($e->errorInfo[1]??0)===1062?409:500);$error=(int)($e->errorInfo[1]??0)===1062?'Cấu hình, màu hoặc SKU đã tồn tại.':'Không lưu được dữ liệu. Kiểm tra SQL Task 22.';error_log('[TASK22] SQLSTATE '.$e->getCode());}
        }
        $configurations=$this->repo->configurations($id);$variants=$this->repo->variants($id);$colors=$this->repo->colors();$images=$this->repo->images($id);$ready=$this->repo->ready($id);$isPreview=$brandModuleEnabled=$userModuleEnabled=true;
        require $this->root.'/resources/views/admin/products/database-test-catalog.php';
    }
}
