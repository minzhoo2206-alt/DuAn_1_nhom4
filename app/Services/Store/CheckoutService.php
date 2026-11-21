<?php
declare(strict_types=1);
namespace App\Services\Store;

use App\Repositories\Store\OrderRepository;
use DomainException;

final class CheckoutService
{
    public function __construct(private OrderRepository $orders,private CryptoService $crypto) {}

    public function place(int $userId,string $key,string $name,string $phone,string $address): array
    {
        $name=trim($name);$phone=trim($phone);$address=trim($address);
        if(!preg_match('/^[a-f0-9]{48}$/D',$key))throw new DomainException('Mã checkout không hợp lệ.');
        if($name===''||strlen($name)>200)throw new DomainException('Tên người nhận không hợp lệ.');
        if(!preg_match('/^\+?[0-9]{9,15}$/D',$phone))throw new DomainException('Số điện thoại cần 9–15 chữ số.');
        if(strlen($address)<10||strlen($address)>500)throw new DomainException('Địa chỉ cần từ 10 đến 500 byte.');
        return $this->orders->checkout($userId,$key,$name,$phone,$address,$this->crypto);
    }
    public function history(int $userId): array{return $this->orders->forUser($userId);}
    public function detail(int $userId,int $orderId): ?array
    {
        $order=$this->orders->detail($userId,$orderId);
        if($order) {
            foreach(['recipient_name','phone','address'] as $key) {
                $order[$key]=$this->crypto->decrypt($order[$key.'_ciphertext']);
                unset($order[$key.'_ciphertext']);
            }
        }
        return $order;
    }
    public function cancel(int $userId,int $orderId): void {$this->orders->cancelPending($userId,$orderId);}
}
