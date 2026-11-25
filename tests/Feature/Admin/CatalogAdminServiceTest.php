<?php
declare(strict_types=1);
namespace Tests\Feature\Admin;

use App\Repositories\Admin\CatalogAdminRepository;
use App\Services\Admin\AdminException;
use App\Services\Admin\AdminValidationException;
use App\Services\Admin\CatalogAdminService;
use PHPUnit\Framework\TestCase;

final class CatalogAdminServiceTest extends TestCase
{
    private function fixture(): CatalogAdminService
    {
        $repo = new class extends CatalogAdminRepository {
            public array $items = [1=>['id'=>1,'name'=>'Demo','slug'=>'demo','status'=>'active']];
            public function __construct() {}
            public function count(string $kind, string $search=''): int { return count($this->items); }
            public function paginate(string $kind, string $search, int $limit, int $offset): array { return array_values($this->items); }
            public function find(string $kind, int $id): ?array { return $this->items[$id] ?? null; }
            public function slugExists(string $kind, string $slug, ?int $exceptId=null): bool {
                foreach ($this->items as $item) { if ($item['slug']===$slug && $item['id']!==$exceptId) { return true; } }
                return false;
            }
            public function create(string $kind, array $data): int {
                $id = count($this->items)+1;
                $this->items[$id] = $data+['id'=>$id]; return $id;
            }
            public function update(string $kind, int $id, array $data): void { $this->items[$id] = $data+['id'=>$id]; }
            public function setStatus(string $kind, int $id, string $status): void { $this->items[$id]['status']=$status; }
        };
        return new CatalogAdminService($repo);
    }

    public function testCreateSuccess(): void
    {
        self::assertSame(2, $this->fixture()->create('products', ['name'=>'Máy demo','slug'=>'may-demo','status'=>'draft']));
    }

    public function testMissingNameIsRejected(): void
    {
        $this->expectException(AdminValidationException::class);
        $this->fixture()->create('products', ['name'=>'','slug'=>'may-demo','status'=>'draft']);
    }

    public function testInvalidSlugIsRejected(): void
    {
        $this->expectException(AdminValidationException::class);
        $this->fixture()->create('products', ['name'=>'Máy demo','slug'=>'Dien Thoai','status'=>'active']);
    }

    public function testDuplicateSlugIsRejected(): void
    {
        $this->expectException(AdminValidationException::class);
        $this->fixture()->create('products', ['name'=>'Demo trùng','slug'=>'demo','status'=>'active']);
    }

    public function testUpdateMissingIdReturns404(): void
    {
        try { $this->fixture()->update('products', 999, ['name'=>'Demo','slug'=>'new','status'=>'draft']); }
        catch (AdminException $e) { self::assertSame(404, $e->status()); return; }
        self::fail('Không trả 404 cho ID không tồn tại.');
    }

    public function testHideInsteadOfHardDelete(): void
    {
        $service = $this->fixture();
        self::assertSame('inactive', $service->toggle('products',1));
        self::assertSame('inactive', $service->find('products',1)['status']);
    }

    public function testUnknownModuleReturns404(): void
    {
        try { $this->fixture()->listing('unknown'); }
        catch (AdminException $e) { self::assertSame(404, $e->status()); return; }
        self::fail('Module lạ phải bị từ chối.');
    }
}
