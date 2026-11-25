<?php
declare(strict_types=1);
namespace Tests\Feature\Admin;

use App\Repositories\Admin\RoleRepository;
use App\Services\Admin\AdminException;
use App\Services\Admin\RolePermissionService;
use PHPUnit\Framework\TestCase;

final class RolePermissionServiceTest extends TestCase
{
    public function testCannotGrantPermissionToOwnRole(): void
    {
        $repo = new class extends RoleRepository {
            public function __construct() {}
            public function rolesForUser(int $userId): array { return [7]; }
        };
        $service = new RolePermissionService($repo);
        try { $service->grant(1,7,10); }
        catch (AdminException $e) { self::assertSame(403,$e->status()); return; }
        self::fail('Không được tự nâng quyền qua role của mình.');
    }

    public function testCannotAssignOwnRole(): void
    {
        $repo = new class extends RoleRepository { public function __construct() {} };
        $service = new RolePermissionService($repo);
        try { $service->assign(1,1,1); }
        catch (AdminException $e) { self::assertSame(403,$e->status()); return; }
        self::fail('Không được tự nâng quyền.');
    }
}
