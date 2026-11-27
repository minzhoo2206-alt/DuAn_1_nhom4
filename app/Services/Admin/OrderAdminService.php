<?php
declare(strict_types=1);
namespace App\Services\Admin;

use DomainException;
final class OrderAdminService
{
    public const LABELS = ['pending_confirmation'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','packing'=>'Đóng gói','shipped'=>'Đang giao','delivered'=>'Giao thành công','delivery_failed'=>'Giao thất bại','returning'=>'Đang hoàn hàng','returned'=>'Đã hoàn hàng','cancelled'=>'Đã hủy','rejected'=>'Từ chối','expired'=>'Hết hạn'];
    // Only a local simulation; never a production fulfillment policy.
    public static function next(string $status): array
    {
        return match($status){'pending_confirmation'=>['confirmed','cancelled'],'confirmed'=>['packing','cancelled'],'packing'=>['shipped','cancelled'],'shipped'=>['delivered','delivery_failed'],'delivery_failed'=>['returning'],'returning'=>['returned'],default=>[]};
    }
    public static function validate(string $from,string $to,string $reason): void
    {
        if(!in_array($to,self::next($from),true)) throw new DomainException('Không được chuyển trạng thái này.');
        if(strlen($reason)>255 || (in_array($to,['cancelled','delivery_failed'],true) && trim($reason)==='')) throw new DomainException('Cần lý do hợp lệ (tối đa 255 byte).');
    }
}
