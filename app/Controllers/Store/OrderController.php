<?php
declare(strict_types=1);
namespace App\Controllers\Store;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\Store\OrderRepository;
use App\Services\Store\CheckoutService;
use App\Services\Store\CryptoService;
use DomainException;

final class OrderController extends StoreController
{
    private function service():CheckoutService
    {return new CheckoutService(new OrderRepository(),new CryptoService());}
    public function index(Request $request,Response $response,array $params=[]):void
    {
        $id=$this->user($response);if($id===null)return;
        $this->view($response,'orders/index',['orders'=>$this->service()->history($id)]);
    }
    public function show(Request $request,Response $response,array $params):void
    {
        $id=$this->user($response);if($id===null)return;
        $order=$this->service()->detail($id,(int)($params['id']??0));
        if(!$order){$response->html('404 - Không tìm thấy đơn hàng của bạn.',404);return;}
        $this->view($response,'orders/show',['order'=>$order]);
    }
    public function cancel(Request $request,Response $response,array $params):void
    {
        $id=$this->user($response);if($id===null||!$this->csrf($response))return;
        try {$this->service()->cancel($id,(int)($params['id']??0));$this->redirect('/orders/'.(int)$params['id']);}
        catch(DomainException $e){$response->html('Không thể hủy đơn: '.htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8'),422);}
    }
}
