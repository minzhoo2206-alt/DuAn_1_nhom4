<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\Admin\CatalogAdminRepository;

final class DashboardController extends BaseAdminController
{
    public function index(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($response): void {
            $this->authorize('dashboard.view');
            $repo = new CatalogAdminRepository();
            $counts = [];
            foreach (['products','categories','brands'] as $kind) { $counts[$kind] = $repo->count($kind); }
            $this->html($response, 'admin/dashboard', ['counts'=>$counts, 'pageTitle'=>'Tổng quan Admin']);
        });
    }
}
