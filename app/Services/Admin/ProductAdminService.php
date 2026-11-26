<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Repositories\Admin\ProductAdminRepository;

use InvalidArgumentException;
final class ProductAdminService
{
    public function __construct(private ProductAdminRepository $products)
    {
    }

    // Lấy danh sách sản phẩm và thông tin phân trang
    public function listing(int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));

        $total = $this->products->count();
        $totalPages = max(1, (int) ceil($total / $perPage));

        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'products' => $this->products->paginate($perPage, $offset),
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

    public function formOptions(): array
    {
        return ['brands' => $this->products->brands(),
                'categories' => $this->products->categories()];
    }

    // Dùng chung quy tắc validation cho demo và backend MySQL.
    public static function validateCreate(array $input, array $brands, array $categories): array
    {
        $text = static fn(string $key): string => is_string($input[$key] ?? null)
            ? trim($input[$key]) : '';
        $name = $text('name');
        $slug = $text('slug');
        $description = $text('description');
        $brandId = filter_var($input['brand_id'] ?? null, FILTER_VALIDATE_INT);
        $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT);
        $warranty = filter_var($input['warranty_months'] ?? null, FILTER_VALIDATE_INT);

        if (!preg_match('/^.{1,200}$/us', $name)) {
            throw new InvalidArgumentException('Tên sản phẩm phải từ 1 đến 200 ký tự.');
        }
        if (strlen($slug) > 220 || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) {
            throw new InvalidArgumentException('Slug không hợp lệ. Ví dụ: iphone-16-pro-max.');
        }
        if ($brandId === false || $brandId < 1 || !in_array($brandId, array_map('intval', array_column($brands, 'id')), true)) {
            throw new InvalidArgumentException('Hãy chọn thương hiệu hợp lệ.');
        }
        if ($categoryId === false || $categoryId < 1 || !in_array($categoryId, array_map('intval', array_column($categories, 'id')), true)) {
            throw new InvalidArgumentException('Hãy chọn danh mục hợp lệ.');
        }
        if ($warranty === false || $warranty < 0 || $warranty > 65535) {
            throw new InvalidArgumentException('Số tháng bảo hành không hợp lệ.');
        }
        if (strlen($description) > 10000 || !preg_match('//u', $description)) {
            throw new InvalidArgumentException('Mô tả quá dài hoặc không hợp lệ.');
        }

        return [
            'name' => $name, 'slug' => $slug, 'brand_id' => $brandId,
            'category_id' => $categoryId, 'description' => $description,
            'warranty_months' => $warranty, 'status' => 'draft',
        ];
    }

    public function create(array $input): int
    {
        $options = $this->formOptions();
        $data = self::validateCreate($input, $options['brands'], $options['categories']);
        if ($this->products->slugExists($data['slug'])) {
            throw new InvalidArgumentException('Slug đã tồn tại.');
        }
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $data['public_id'] = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
        return $this->products->create($data);
    }


    public static function validateUpdate(array $input, array $brands, array $categories): array
    {
        $data = self::validateCreate($input, $brands, $categories);
        // Không chuyển sản phẩm đang bán về bản nháp khi sửa.
        unset($data['status']);
        return $data;
    }

    public function update(int $id, array $input): void
    {
        if ($this->find($id) === null) {
            throw new \OutOfBoundsException('Không tìm thấy sản phẩm.');
        }
        $options = $this->formOptions();
        $data = self::validateUpdate($input, $options['brands'], $options['categories']);
        if ($this->products->slugExistsForOther($data['slug'], $id)) {
            throw new InvalidArgumentException('Slug đã tồn tại ở sản phẩm khác.');
        }
        $this->products->update($id, $data);
    }


    // Dùng chung quy tắc nghiệp vụ cho demo và MySQL.
    public static function validateLifecycleAction(string $action, array $product): void
    {
        if (!empty($product['deleted_at'])) {
            throw new \OutOfBoundsException('Không tìm thấy sản phẩm.');
        }
        $status = $product['status'] ?? '';
        if ($action === 'hide' && $status === 'active') {
            return;
        }
        if ($action === 'delete' && in_array($status, ['draft', 'hidden'], true)) {
            return;
        }
        throw new InvalidArgumentException('Thao tác không hợp lệ. Hãy ẩn sản phẩm đang bán trước khi xóa.');
    }

    public function hide(int $id): void
    {
        $product = $this->find($id);
        if ($product === null) {
            throw new \OutOfBoundsException('Không tìm thấy sản phẩm.');
        }
        self::validateLifecycleAction('hide', $product);
        if (!$this->products->hide($id)) {
            throw new InvalidArgumentException('Sản phẩm đã thay đổi. Vui lòng tải lại trang.');
        }
    }

    public function softDelete(int $id): void
    {
        $product = $this->find($id);
        if ($product === null) {
            throw new \OutOfBoundsException('Không tìm thấy sản phẩm.');
        }
        self::validateLifecycleAction('delete', $product);
        if (!$this->products->softDelete($id)) {
            throw new InvalidArgumentException('Sản phẩm đã thay đổi. Vui lòng tải lại trang.');
        }
    }

}
