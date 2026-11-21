<?php
declare(strict_types=1);
namespace App\Controllers\Store;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\Store\CartRepository;
use App\Repositories\Store\OrderRepository;
use App\Services\Store\CartService;
use App\Services\Store\CryptoService;
use App\Services\Store\CheckoutService;
use DomainException;

final class CheckoutController extends StoreController
{
    private function service():CheckoutService
    {return new CheckoutService(new OrderRepository(),new CryptoService());}
    private function display(Response $response,int $id,?string $error=null):void
    {
        $cart=(new CartService(new CartRepository()))->list($id);
        if(!isset($_SESSION['checkout_key']))$_SESSION['checkout_key']=bin2hex(random_bytes(24));
        $this->view($response,'checkout/index',$cart+['checkout_key'=>$_SESSION['checkout_key'],'error'=>$error],$error?422:200);
    }
    public function index(Request $request,Response $response,array $params=[]):void
    { $id=$this->user($response);if($id!==null)$this->display($response,$id); }
    public function place(Request $request,Response $response,array $params=[]):void
    {
        $id=$this->user($response);if($id===null||!$this->csrf($response))return;
        $key=$this->post('checkout_key');
        if (!isset($_SESSION['checkout_key']) || !hash_equals((string)$_SESSION['checkout_key'],$key)) {
            $response->html('419 - Mã checkout không hợp lệ. Hãy mở lại trang checkout.',419);return;
        }
        try {
            $order=$this->service()->place($id,$key,$this->post('recipient_name'),$this->post('phone'),$this->post('address'));
            unset($_SESSION['checkout_key']);
            $this->redirect('/orders/'.(int)$order['id']);
        } catch (DomainException $e) { $this->display($response,$id,$e->getMessage()); }
    }
}
