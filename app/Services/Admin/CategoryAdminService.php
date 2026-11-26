<?php

declare(strict_types=1);
namespace App\Services\Admin;
use App\Repositories\Admin\CategoryAdminRepository;
use InvalidArgumentException;
use OutOfBoundsException;

final class CategoryAdminService
{
    public function __construct(private CategoryAdminRepository $categories) {}
    public static function validate(array $input): array
    {
        $name = is_string($input['name'] ?? null) ? trim($input['name']) : '';
        $slug = is_string($input['slug'] ?? null) ? trim($input['slug']) : '';
        if (!preg_match('/^.{1,100}$/uD', $name)) throw new InvalidArgumentException('Tên danh mục phải từ 1 đến 100 ký tự.');
        if (strlen($slug) > 120 || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) throw new InvalidArgumentException('Slug không hợp lệ. Ví dụ: dien-thoai.');
        return ['name'=>$name, 'slug'=>$slug];
    }
    public function listing(): array { return $this->categories->all(); }
    public function find(int $id): ?array { return $id > 0 ? $this->categories->find($id) : null; }
    public function save(array $input, int $id = 0): int
    {
        if ($id !== 0 && $this->find($id) === null) throw new OutOfBoundsException('Không tìm thấy danh mục.');
        $data = self::validate($input);
        if ($this->categories->slugExists($data['slug'], $id)) throw new InvalidArgumentException('Slug đã tồn tại.');
        if ($id === 0) return $this->categories->create($data);
        $this->categories->update($id, $data);
        return $id;
    }
    public static function validateAction(string $action, array $category, bool $hasProducts): void
    {
        $active = (bool) $category['is_active'];
        if ($action === 'activate' && !$active) return;
        if (in_array($action, ['deactivate','delete'], true) && $hasProducts) throw new InvalidArgumentException('Danh mục vẫn còn sản phẩm. Hãy chuyển sản phẩm sang danh mục khác trước.');
        if (($action === 'deactivate' && $active) || ($action === 'delete' && !$active)) return;
        throw new InvalidArgumentException('Thao tác không hợp lệ. Hãy ngừng sử dụng trước khi xóa.');
    }
    public function lifecycle(int $id, string $action): void
    {
        $category = $this->find($id);
        if ($category === null) throw new OutOfBoundsException('Không tìm thấy danh mục.');
        self::validateAction($action, $category, $this->categories->hasProducts($id));
        if (!$this->categories->lifecycle($id, $action)) throw new InvalidArgumentException('Danh mục đã thay đổi hoặc còn sản phẩm. Hãy tải lại trang.');
    }
}
