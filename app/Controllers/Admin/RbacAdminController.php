<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Repositories\Admin\RbacAdminRepository;
use App\Services\Admin\RbacAdminService as Rules;
use DomainException;

// Local preview only. The official Router/Middleware belongs to Minh.
final class RbacAdminController
{
    public function __construct(private RbacAdminRepository $repo,private string $root) {}
    public function current(): ?array
    {
        $id=$_SESSION['task17_id']??null;
        $actor=is_int($id)?$this->repo->actor($id):null;
        if(!Rules::sessionValid($actor,$_SESSION['task17_fingerprint']??null) || time()-(int)($_SESSION['task17_since']??0)>3600){unset($_SESSION['task17_id'],$_SESSION['task17_fingerprint'],$_SESSION['task17_since'],$_SESSION['task17_nav']);return null;}
        return $actor;
    }
    private function token(): string { return $_SESSION['task17_csrf']??=bin2hex(random_bytes(32)); }
    private function csrf(): bool {return is_string($_POST['rbac_token']??null)&&hash_equals($this->token(),$_POST['rbac_token']);}
    private function render(string $view,array $vars=[]): void {extract($vars,EXTR_SKIP);$isPreview=true;$brandModuleEnabled=true;$userModuleEnabled=true;require $this->root.'/resources/views/admin/rbac/'.$view.'.php';}
    public function login(string $method): void
    {
        $error='';$token=$this->token();
        if($method==='POST'){
            if(!$this->csrf()){http_response_code(403);echo 'CSRF không hợp lệ.';return;}
            $attempts=array_values(array_filter($_SESSION['task17_attempts']??[],static fn($t)=>is_int($t)&&$t>time()-60));
            if(count($attempts)>=5){http_response_code(429);$error='Thử lại sau 60 giây.';}
            else{
                $attempts[]=time();$_SESSION['task17_attempts']=$attempts;
                $email=is_string($_POST['email']??null)?strtolower(trim($_POST['email'])):'';
                $pass=is_string($_POST['password']??null)?$_POST['password']:'';
                $actor=$this->repo->authenticate($email,$pass);
                if($actor){session_regenerate_id(true);$_SESSION=['task17_id'=>(int)$actor['id'],'task17_fingerprint'=>$actor['fingerprint'],'task17_since'=>time()];header('Location: '.(Rules::allowed($actor,Rules::RBAC)?'/admin/rbac':'/admin/products'),true,303);return;}
                http_response_code(401);$error='Email, mật khẩu hoặc trạng thái tài khoản không hợp lệ.';
            }
        }
        $this->render('login',compact('token','error'));
    }
    public function logout(): void
    {
        if(!$this->csrf()){http_response_code(403);echo 'CSRF không hợp lệ.';return;}
        $_SESSION=[];session_regenerate_id(true);header('Location: /admin/login',true,303);
    }
    public function deny(array $actor): void {http_response_code(403);$token=$this->token();$this->render('denied',compact('actor','token'));}
    public function dispatch(string $path,string $method,array $actor): void
    {
        if(!Rules::allowed($actor,Rules::RBAC)){$this->deny($actor);return;}
        if($method==='POST'){
            if(!$this->csrf()){http_response_code(403);echo 'CSRF không hợp lệ.';return;}
            try{
                if($path==='/admin/rbac/role'){
                    $id=filter_var($_POST['user_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
                    $role=$_POST['role']??null;
                    if(!$id||!is_string($role))throw new DomainException('Dữ liệu không hợp lệ.');
                    $this->repo->changeRole((int)$actor['id'],$id,$role);
                }elseif($path==='/admin/rbac/staff-write'){
                    $allow=$_POST['allow']??null;if(!in_array($allow,['0','1'],true))throw new DomainException('Dữ liệu không hợp lệ.');
                    $this->repo->staffWrite((int)$actor['id'],$allow==='1');
                }else{http_response_code(404);return;}
                $_SESSION['task17_message']='Đã cập nhật phân quyền.';unset($_SESSION['task17_csrf']);header('Location: /admin/rbac',true,303);return;
            }catch(DomainException $e){http_response_code(403);$this->deny($actor);return;}
        }
        $page=filter_var($_GET['page']??1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>10000]])?:1;
        $accounts=$this->repo->accounts($page);$matrix=$this->repo->matrix();$token=$this->token();$message=$_SESSION['task17_message']??'';unset($_SESSION['task17_message']);
        $this->render('index',compact('accounts','matrix','token','message','actor','page'));
    }
}
