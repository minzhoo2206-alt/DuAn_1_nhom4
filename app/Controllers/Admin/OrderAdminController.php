<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\Admin\OrderAdminRepository;
use App\Services\Admin\OrderAdminService;

final class OrderAdminController extends BaseAdminController
{
    private function service(): OrderAdminService { return new OrderAdminService(new OrderAdminRepository()); }

    public function index(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($request, $response): void {
            $this->authorize('orders.view');
            $this->html($response, 'admin/orders/index', $this->service()->listing(
                (int) $request->input('page', 1), (string) $request->input('status', '')
            ) + ['pageTitle'=>'Quản lý đơn hàng']);
        });
    }

    public function show(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($response, $vars): void {
            $this->authorize('orders.view');
            $this->html($response, 'admin/orders/show', [
                'order'=>$this->service()->find((int) ($vars['id'] ?? 0)), 'pageTitle'=>'Chi tiết đơn hàng',
            ]);
        });
    }

    public function updateStatus(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($request, $vars): void {
            $this->authorize('orders.update');
            $this->csrf($request);
            $this->service()->changeStatus((int) ($vars['id'] ?? 0), (string) $request->input('status', ''));
        });
    }
}
