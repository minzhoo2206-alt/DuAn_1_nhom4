<?php
declare(strict_types=1);
namespace App\Controllers\Store;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\Store\UserRepository;
use App\Services\Store\AuthService;
use App\Services\Store\CryptoService;
use App\Services\Store\EmailVerificationService;
use App\Services\Store\SessionAuth;
use DomainException;

final class AuthController extends StoreController
{
    private function auth(): AuthService
    { return new AuthService(new UserRepository(),new CryptoService()); }

    public function loginForm(Request $request, Response $response, array $params=[]): void
    { $this->view($response,'auth/login',['registered'=>$request->input('registered')==='1']); }

    public function login(Request $request, Response $response, array $params=[]): void
    {
        if(!$this->csrf($response))return;
        try {
            $id=$this->auth()->login($this->post('email'),$this->post('password'));
            SessionAuth::login($id);
            $this->redirect('/profile');
        } catch(DomainException $e) {
            $this->view($response,'auth/login',['error'=>$e->getMessage(),'email'=>$this->post('email')],422);
        }
    }

    public function registerForm(Request $request, Response $response, array $params=[]): void
    { $this->view($response,'auth/register'); }

    public function register(Request $request, Response $response, array $params=[]): void
    {
        if(!$this->csrf($response))return;
        try {
            $auth=$this->auth();
            $email=$this->post('email');
            $auth->register($email,$this->post('password'),$this->post('name'));
            $demo=($_ENV['APP_ENV']??'production')==='local' &&
                 ($_ENV['ALLOW_DEMO_AUTO_VERIFY']??'false')==='true';
            if (!$demo) {
                try { (new EmailVerificationService())->send($email); }
                catch (DomainException $e) {
                    $this->view($response,'auth/register',['notice'=>'Tài khoản đã được tạo nhưng chưa gửi được email xác minh: '.$e->getMessage()]);
                    return;
                }
            }
            $this->redirect('/login?registered=1');
        } catch(DomainException $e) {
            $this->view($response,'auth/register',['error'=>$e->getMessage(),'email'=>$this->post('email'),'name'=>$this->post('name')],422);
        }
    }

    public function verifyForm(Request $request, Response $response, array $params=[]): void
    { $token=$request->input('token','');$this->view($response,'auth/verify',['token'=>is_string($token)?$token:'']); }

    public function verify(Request $request, Response $response, array $params=[]): void
    {
        if(!$this->csrf($response))return;
        try {(new EmailVerificationService())->confirm($this->post('token'));$this->redirect('/login?registered=1');}
        catch(DomainException $e){$this->view($response,'auth/verify',['error'=>$e->getMessage(),'token'=>''],422);}
    }

    public function resendForm(Request $request, Response $response, array $params=[]): void
    { $this->view($response,'auth/resend'); }

    public function resend(Request $request, Response $response, array $params=[]): void
    {
        if(!$this->csrf($response))return;
        try { (new EmailVerificationService())->send($this->post('email'));
            $this->view($response,'auth/resend',['notice'=>'Nếu tài khoản đang chờ xác minh, email đã được xử lý gửi.']); }
        catch(DomainException $e) { $this->view($response,'auth/resend',['error'=>$e->getMessage()],422); }
    }

    public function profile(Request $request, Response $response, array $params=[]): void
    {
        $id=$this->user($response);if($id===null)return;
        $this->view($response,'auth/profile',['profile'=>$this->auth()->profile($id)]);
    }
    public function updateProfile(Request $request, Response $response, array $params=[]): void
    {
        $id=$this->user($response);if($id===null||!$this->csrf($response))return;
        try {$this->auth()->changeName($id,$this->post('name'));$this->redirect('/profile?updated=1');}
        catch(DomainException $e){$this->view($response,'auth/profile',['error'=>$e->getMessage(),'profile'=>$this->auth()->profile($id)],422);}
    }
    public function logout(Request $request, Response $response, array $params=[]): void
    { if(!$this->csrf($response))return;SessionAuth::logout();$this->redirect('/login'); }
}
