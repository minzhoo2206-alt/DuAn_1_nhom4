<?php
declare(strict_types=1);
namespace Tests\Integration;

use App\Repositories\Admin\OrderAdminRepository;
use App\Services\Admin\AdminException;
use App\Services\Admin\OrderAdminService;
use PHPUnit\Framework\TestCase;

final class OrderAdminBoundaryTest extends TestCase
{
    public function testOrderTransitionCannotBypassStoreService(): void
    {
        $repo = new class extends OrderAdminRepository { public function __construct() {} };
        $service = new OrderAdminService($repo);
        try { $service->changeStatus(1,'delivered'); }
        catch (AdminException $e) { self::assertSame(501,$e->status()); return; }
        self::fail('Order Admin không được trực tiếp tự đổi trạng thái trước khi tích hợp Store.');
    }
}
