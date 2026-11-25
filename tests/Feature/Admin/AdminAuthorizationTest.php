<?php
declare(strict_types=1);
namespace Tests\Feature\Admin;

use App\Repositories\Admin\RoleRepository;
use App\Services\Admin\AdminException;
use App\Services\Admin\AdminGuard;
use PHPUnit\Framework\TestCase;

final class AdminAuthorizationTest extends TestCase
{
    private array $savedSession;

    protected function setUp(): void
    {
        $this->savedSession = $_SESSION ?? [];
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
    }

    protected function tearDown(): void { $_SESSION = $this->savedSession; }

    private function guard(array $permissions): AdminGuard
    {
        $repo = new class($permissions) extends RoleRepository {
            public function __construct(private array $fakePermissions) {}
            public function permissionsForUser(int $userId): array { return $this->fakePermissions; }
        };
        return new AdminGuard($repo);
    }

    public function testGuestIsUnauthorized(): void
    {
        unset($_SESSION['user_id']);
        try { $this->guard(['products.view'])->enforce('products.view'); }
        catch (AdminException $e) { self::assertSame(401,$e->status()); return; }
        self::fail('Guest không bị chặn.');
    }

    public function testUserMissingPermissionIsForbidden(): void
    {
        $_SESSION['user_id']=3;
        try { $this->guard(['products.view'])->enforce('products.update'); }
        catch (AdminException $e) { self::assertSame(403,$e->status()); return; }
        self::fail('User chưa có quyền không bị chặn.');
    }

    public function testAuthorizedAction(): void
    {
        $_SESSION['user_id']=3;
        $this->guard(['products.view'])->enforce('products.view');
        self::assertTrue(true);
    }

    public function testInvalidCsrfIsRejected(): void
    {
        $_SESSION['admin_csrf'] = 'secure-token';
        try { AdminGuard::verifyCsrf('wrong'); }
        catch (AdminException $e) { self::assertSame(419, $e->status()); return; }
        self::fail('CSRF sai không bị chặn.');
    }

    public function testValidCsrfIsAccepted(): void
    {
        $_SESSION['admin_csrf'] = 'secure-token';
        AdminGuard::verifyCsrf('secure-token');
        self::assertTrue(true);
    }
}
