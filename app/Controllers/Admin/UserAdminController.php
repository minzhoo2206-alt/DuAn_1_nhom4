<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Services\Admin\UserAdminService;
use PDOException;
use DomainException;
use InvalidArgumentException;
use OutOfBoundsException;

// Local preview controller only. Do not register in production router.
final class UserAdminController
{
    public function __construct(private UserAdminService $service,private string $root) {}
    public function dispatch(string $operation,int $id,string $method): void
    {
        if(PHP_SAPI!=='cli-server'||($_SERVER['HTTP_HOST']??'')!=='127.0.0.1:8001'||!in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true)||session_status()!==PHP_SESSION_ACTIVE){http_response_code(403);return;}
        $allowed=match($operation){'list','create','edit','password'=>'GET','store','update','reset','lock','unlock'=>'POST',default=>''};
        if($method!==$allowed){http_response_code(405);return;}
        $token=$_SESSION['task16_csrf']??=bin2hex(random_bytes(32));
        if($method==='POST'&&(!is_string($_POST['user_token']??null)||!hash_equals($token,$_POST['user_token']))){http_response_code(403);echo 'CSRF không hợp lệ.';return;}
        $isPreview=true; $brandModuleEnabled=true; $userModuleEnabled=true;
        if($operation==='list'){
            $data=$this->service->listing($_GET['q']??'',$_GET['status']??'',$_GET['page']??1);
            $message=$_SESSION['task16_message']??'';unset($_SESSION['task16_message']);
            require $this->root.'/resources/views/admin/users/database-test-list.php'; return;
        }
        $email=''; $error=''; $mode=match($operation){'create','store'=>'create','password','reset'=>'password',default=>'edit'};
        try {
            if($id>0){$row=$this->service->editable($id);$email=$row['email'];}
            if($method==='POST'){
                if($operation==='store') $this->service->create($_POST); else $this->service->action($id,$operation,$_POST);
                $_SESSION['task16_message']='Đã lưu thao tác tài khoản kiểm thử.';unset($_SESSION['task16_csrf']);header('Location: /admin/users',true,303);return;
            }
        } catch(OutOfBoundsException $e){http_response_code(404);echo 'Không tìm thấy tài khoản.';return;}
        catch(DomainException $e){http_response_code(403);echo htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');return;}
        catch(InvalidArgumentException $e){http_response_code(422);$error=$e->getMessage();}
        catch(PDOException $e){if((int)($e->errorInfo[1]??0)===1062){http_response_code(409);$error='Email đã tồn tại.';}else{error_log('[TASK16] SQLSTATE '.$e->getCode());http_response_code(500);$error='Lỗi database kiểm thử.';}}
        if(in_array($operation,['lock','unlock'],true)){echo htmlspecialchars($error,ENT_QUOTES,'UTF-8');return;}
        if(in_array($operation,['store','update'],true)&&is_string($_POST['email']??null)) $email=substr($_POST['email'],0,254);
        require $this->root.'/resources/views/admin/users/database-test-form.php';
    }
}
