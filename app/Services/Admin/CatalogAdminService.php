<?php
declare(strict_types=1);
namespace App\Services\Admin;

use App\Repositories\Admin\CatalogAdminRepository;
use PDOException;

final class CatalogAdminService
{
    private const LABELS = ['products' => 'Sản phẩm', 'categories' => 'Danh mục', 'brands' => 'Thương hiệu'];
    private const STATUSES = ['draft', 'active', 'inactive'];

    public function __construct(private CatalogAdminRepository $repository) {}

    public function label(string $kind): string
    {
        return self::LABELS[$kind] ?? throw new AdminException('Module không tồn tại.', 404);
    }

    public function listing(string $kind, int $page = 1, string $search = ''): array
    {
        $this->label($kind);
        $page = max(1, min($page, 100000));
        $search = trim(substr($search, 0, 100));
        $perPage = 15;
        $total = $this->repository->count($kind, $search);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);
        return [
            'items' => $this->repository->paginate($kind, $search, $perPage, ($page - 1) * $perPage),
            'total' => $total, 'page' => $page, 'pages' => $pages, 'search' => $search,
        ];
    }

    public function find(string $kind, int $id): array
    {
        $this->label($kind);
        if ($id < 1 || ($item = $this->repository->find($kind, $id)) === null) {
            throw new AdminException('Không tìm thấy bản ghi.', 404);
        }
        return $item;
    }

    public function create(string $kind, array $input): int
    {
        $this->label($kind);
        $data = $this->validate($input);
        $this->ensureSlugAvailable($kind, $data['slug']);
        try { return $this->repository->create($kind, $data); }
        catch (PDOException $e) { $this->translateDbException($e); }
    }

    public function update(string $kind, int $id, array $input): void
    {
        $this->find($kind, $id);
        $data = $this->validate($input);
        $this->ensureSlugAvailable($kind, $data['slug'], $id);
        try { $this->repository->update($kind, $id, $data); }
        catch (PDOException $e) { $this->translateDbException($e); }
    }

    /** Không xóa cứng: ẩn sản phẩm/danh mục/thương hiệu khỏi Store. */
    public function toggle(string $kind, int $id): string
    {
        $record = $this->find($kind, $id);
        $next = $record['status'] === 'active' ? 'inactive' : 'active';
        $this->repository->setStatus($kind, $id, $next);
        return $next;
    }

    private function validate(array $input): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $status = (string) ($input['status'] ?? 'draft');
        $errors = [];
        if ($name === '' || (function_exists('mb_strlen') ? mb_strlen($name) : strlen($name)) > 255) { $errors['name'] = 'Tên bắt buộc, tối đa 255 ký tự.'; }
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug) || strlen($slug) > 255) {
            $errors['slug'] = 'Slug gồm chữ thường, số và dấu - (tối đa 255 ký tự).';
        }
        if (!in_array($status, self::STATUSES, true)) { $errors['status'] = 'Trạng thái không hợp lệ.'; }
        if ($errors) { throw new AdminValidationException($errors); }
        return compact('name', 'slug', 'status');
    }

    private function ensureSlugAvailable(string $kind, string $slug, ?int $exceptId = null): void
    {
        if ($this->repository->slugExists($kind, $slug, $exceptId)) {
            throw new AdminValidationException(['slug' => 'Slug đã tồn tại.']);
        }
    }

    private function translateDbException(PDOException $e): never
    {
        if (in_array((string) $e->getCode(), ['23000', '23505'], true)) {
            throw new AdminValidationException(['slug' => 'Dữ liệu bị trùng hoặc vi phạm ràng buộc database.']);
        }
        throw $e;
    }
}
