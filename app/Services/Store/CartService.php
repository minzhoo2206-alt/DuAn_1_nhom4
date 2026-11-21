<?php
declare(strict_types=1);
namespace App\Services\Store;

use App\Repositories\Store\CartRepository;
use DomainException;

final class CartService
{
    public function __construct(private CartRepository $carts) {}
    public function list(int $userId): array
    {
        $rows=$this->carts->forUser($userId);
        $sum=0;
        foreach ($rows as &$row) {
            $unit=Money::cents((string)$row['unit_price']);
            $row['unit_cents']=$unit;
            $row['line_cents']=$unit*(int)$row['quantity'];
            $row['in_stock']=(int)$row['in_stock'];
            $sum+=$row['line_cents'];
        } unset($row);
        return ['items'=>$rows,'total_cents'=>$sum,'total'=>Money::decimal($sum)];
    }
    public function add(int $userId,int $variantId,int $amount): void
    {
        if($amount<1||$amount>5)throw new DomainException('Mỗi lần chỉ thêm từ 1 đến 5 sản phẩm.');
        $old=$this->carts->quantity($userId,$variantId);
        $this->set($userId,$variantId,$old+$amount);
    }
    public function set(int $userId,int $variantId,int $quantity): void
    {
        if($variantId<1||$quantity<0||$quantity>5)
            throw new DomainException('Số lượng hợp lệ từ 0 đến 5.');
        if($quantity>0) {
            $v=$this->carts->variantForSale($variantId);
            if (!$v) throw new DomainException('Phiên bản điện thoại không còn mở bán.');
            if((int)$v['in_stock']<$quantity) throw new DomainException('Không đủ hàng. Kiểm tra tồn kho thử nghiệm.');
        }
        $this->carts->save($userId,$variantId,$quantity);
    }
}
