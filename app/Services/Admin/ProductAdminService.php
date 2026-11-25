<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Repositories\Admin\ProductAdminRepository;

final class ProductAdminService
{
    public function __construct(
        private ProductAdminRepository $products
    )
    {
    }

    // Lấy danh sách sản phẩm và thông tin phân trang
    public function listing(
        int $page = 1,
        int $perPage = 20
    ): array
    {
        $page = max(1, $page);

        $perPage = min(100, max(1, $perPage));

        $total = $this->products->count();

        $totalPages = max(
            1,
            (int) ceil($total / $perPage)
        );

        $page = min($page, $totalPages);

        $offset = ($page - 1) * $perPage;

        return [
            'products' => $this->products->paginate(
                $perPage,
                $offset
            ),
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
        ];
    }

    // Tìm sản phẩm có ID hợp lệ
    public function find(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        return $this->products->find($id);
    }
}
