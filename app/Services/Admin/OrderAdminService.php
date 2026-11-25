<?php
declare(strict_types=1);
namespace App\Services\Admin;

use App\Repositories\Admin\OrderAdminRepository;

final class OrderAdminService
{
    public function __construct(private OrderAdminRepository $orders) {}

    public function listing(int $page, ?string $status): array
    {
        $page = max(1, $page);
        $status = trim($status ?? '') ?: null;
        if ($status !== null && !preg_match('/^[a-z][a-z0-9_]{0,39}$/D', $status)) {
            throw new AdminValidationException(['status'=>'Bộ lọc trạng thái không hợp lệ.']);
        }
        $limit = 15;
        $total = $this->orders->count($status);
        $pages = max(1, (int) ceil($total/$limit));
        $page = min($page, $pages);
        return [
            'items'=>$this->orders->paginate($status, $limit, ($page-1)*$limit),
            'page'=>$page, 'pages'=>$pages, 'total'=>$total, 'status'=>$status ?? '',
        ];
    }

    public function find(int $id): array
    {
        $order = $id > 0 ? $this->orders->find($id) : null;
        if ($order === null) { throw new AdminException('Không tìm thấy đơn hàng.', 404); }
        return $order;
    }

    /** Chưa triển khai: phải gọi OrderService/transition API thuộc Nhuận, không SQL tự cập nhật. */
    public function changeStatus(int $id, string $toStatus): never
    {
        throw new AdminException('Chưa tích hợp contract cập nhật trạng thái với module Store.', 501);
    }
}
