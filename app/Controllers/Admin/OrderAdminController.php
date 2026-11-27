<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Repositories\Admin\OrderAdminRepository;
use App\Services\Admin\RbacAdminService as Rbac;
use DomainException;
use OutOfBoundsException;
final class OrderAdminController
{
    public function __construct(private OrderAdminRepository $repo,private string $root){}
    private function render(string $view,array $data):void{extract($data,EXTR_SKIP);$isPreview=true;$brandModuleEnabled=true;$userModuleEnabled=true;require $this->root.'/resources/views/admin/orders/'.$view.'.php';}
    private function text(array $input,string $key):string{return is_string($input[$key]??null)?trim($input[$key]):'';}
    public function dispatch(string $operation,int $id,array $actor):void
    {
        if(!Rbac::allowed($actor,Rbac::ORDERS)){http_response_code(403);echo 'Không đủ quyền.';return;}
        $token=$_SESSION['task18_token']??=bin2hex(random_bytes(32));
        try{
            if($operation==='update'){
                if(!hash_equals($token,$this->text($_POST,'token'))){http_response_code(403);echo 'CSRF không hợp lệ.';return;}
                $this->repo->transition($id,(int)$actor['id'],$this->text($_POST,'expected'),$this->text($_POST,'status'),$this->text($_POST,'reason'));
                unset($_SESSION['task18_token']);$_SESSION['task18_message']='Đã cập nhật trạng thái mô phỏng.';header('Location: /admin/orders/'.$id,true,303);return;
            }
            if($operation==='detail'){$data=$this->repo->detail($id);$message=$_SESSION['task18_message']??'';unset($_SESSION['task18_message']);$this->render('database-test-detail',$data+compact('token','message'));return;}
            $search=substr($this->text($_GET,'q'),0,100);$status=$this->text($_GET,'status');$page=filter_var($_GET['page']??1,FILTER_VALIDATE_INT)?:1;
            $this->render('database-test-list',$this->repo->listing($search,$status,$page)+compact('search','status'));
        }catch(OutOfBoundsException $e){http_response_code(404);echo 'Không tìm thấy đơn mẫu.';}
        catch(DomainException $e){http_response_code(409);echo htmlspecialchars($e->getMessage(),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');echo ' <a href="/admin/orders">Quay lại danh sách</a>';}
    }
}
