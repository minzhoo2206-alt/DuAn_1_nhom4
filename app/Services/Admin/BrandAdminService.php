<?php

declare(strict_types=1);
namespace App\Services\Admin;
use App\Repositories\Admin\BrandAdminRepository;
use InvalidArgumentException;
use OutOfBoundsException;

final class BrandAdminService
{
    public function __construct(private BrandAdminRepository $brands) {}
    public static function validate(array $input): array
    {
        $name = is_string($input['name'] ?? null) ? trim($input['name']) : '';
        $slug = is_string($input['slug'] ?? null) ? trim($input['slug']) : '';
        if (!preg_match('/^.{1,100}$/uD', $name)) throw new InvalidArgumentException('Tên thương hiệu phải từ 1 đến 100 ký tự.');
        if (strlen($slug) > 120 || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) throw new InvalidArgumentException('Slug không hợp lệ. Ví dụ: sony.');
        return ['name'=>$name, 'slug'=>$slug];
    }
    public function listing(): array { return $this->brands->all(); }
    public function find(int $id): ?array { return $id > 0 ? $this->brands->find($id) : null; }
    public function save(array $input, int $id = 0): int
    {
        if ($id !== 0 && $this->find($id) === null) throw new OutOfBoundsException('Không tìm thấy thương hiệu.');
        $data = self::validate($input);
        if ($this->brands->slugExists($data['slug'], $id)) throw new InvalidArgumentException('Slug đã tồn tại.');
        if ($id === 0) return $this->brands->create($data);
        $this->brands->update($id, $data);
        return $id;
    }
    public static function validateAction(string $action, array $brand, bool $hasProducts): void
    {
        $active = (bool) $brand['is_active'];
        if ($action === 'activate' && !$active) return;
        if (in_array($action, ['deactivate','delete'], true) && $hasProducts) throw new InvalidArgumentException('Thương hiệu vẫn còn sản phẩm. Hãy chuyển sản phẩm sang thương hiệu khác trước.');
        if (($action === 'deactivate' && $active) || ($action === 'delete' && !$active)) return;
        throw new InvalidArgumentException('Thao tác không hợp lệ. Hãy ngừng sử dụng trước khi xóa.');
    }
    public function lifecycle(int $id, string $action): void
    {
        $brand = $this->find($id);
        if ($brand === null) throw new OutOfBoundsException('Không tìm thấy thương hiệu.');
        self::validateAction($action, $brand, $this->brands->hasProducts($id));
        if (!$this->brands->lifecycle($id, $action)) throw new InvalidArgumentException('Thương hiệu đã thay đổi hoặc còn sản phẩm. Hãy tải lại trang.');
    }
}
