<?php
declare(strict_types=1);
namespace App\Controllers\Store;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\Store\CartRepository;
use App\Services\Store\CartService;
use DomainException;

final class CartController extends StoreController
{
    private function service(): CartService {return new CartService(new CartRepository());}
    public function index(Request $request,Response $response,array $params=[]): void
    {
        $id=$this->user($response);if($id===null)return;
        $this->view($response,'cart/index',$this->service()->list($id));
    }
    public function add(Request $request,Response $response,array $params=[]): void
    {
        $id=$this->user($response);if($id===null||!$this->csrf($response))return;
        try {$this->service()->add($id,(int)$this->post('variant_id'),(int)$this->post('quantity'));$this->redirect('/cart');}
        catch(DomainException $e){$this->view($response,'cart/index',$this->service()->list($id)+['error'=>$e->getMessage()],422);}
    }
    public function update(Request $request,Response $response,array $params=[]): void
    {
        $id=$this->user($response);if($id===null||!$this->csrf($response))return;
        try {$this->service()->set($id,(int)$this->post('variant_id'),(int)$this->post('quantity'));$this->redirect('/cart');}
        catch(DomainException $e){$this->view($response,'cart/index',$this->service()->list($id)+['error'=>$e->getMessage()],422);}
    }
}
