<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\Admin\CatalogAdminRepository;
use App\Services\Admin\AdminValidationException;
use App\Services\Admin\CatalogAdminService;

final class CatalogController extends BaseAdminController
{
    private function service(): CatalogAdminService { return new CatalogAdminService(new CatalogAdminRepository()); }

    private function kind(array $vars): string { return (string) ($vars['kind'] ?? 'products'); }

    public function index(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($request, $response, $vars): void {
            $kind = $this->kind($vars);
            $this->authorize($kind.'.view');
            $service = $this->service();
            $listing = $service->listing($kind, (int) $request->input('page', 1), (string) $request->input('q', ''));
            $this->html($response, 'admin/'.$kind.'/index', $listing + [
                'kind'=>$kind, 'label'=>$service->label($kind), 'pageTitle'=>'Quản lý '.$service->label($kind),
            ]);
        });
    }

    public function create(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($response, $vars): void {
            $kind = $this->kind($vars);
            $this->authorize($kind.'.create');
            $this->renderForm($response, $kind, [], [], 200);
        });
    }

    public function store(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($request, $response, $vars): void {
            $kind = $this->kind($vars);
            $this->authorize($kind.'.create');
            $this->csrf($request);
            try {
                $this->service()->create($kind, $request->all());
                $this->success($response, 'Đã thêm bản ghi mới.', '/admin/'.$kind);
            } catch (AdminValidationException $e) {
                $this->renderForm($response, $kind, $request->all(), $e->errors(), 422);
            }
        });
    }

    public function edit(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($response, $vars): void {
            $kind = $this->kind($vars);
            $this->authorize($kind.'.update');
            $this->renderForm($response, $kind, $this->service()->find($kind, (int) ($vars['id'] ?? 0)), [], 200);
        });
    }

    public function update(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($request, $response, $vars): void {
            $kind = $this->kind($vars);
            $this->authorize($kind.'.update');
            $this->csrf($request);
            $id = (int) ($vars['id'] ?? 0);
            try {
                $this->service()->update($kind, $id, $request->all());
                $this->success($response, 'Đã cập nhật thành công.', '/admin/'.$kind);
            } catch (AdminValidationException $e) {
                $this->renderForm($response, $kind, $request->all() + ['id'=>$id], $e->errors(), 422);
            }
        });
    }

    public function toggle(Request $request, Response $response, array $vars = []): void
    {
        $this->handle($response, function () use ($request, $response, $vars): void {
            $kind = $this->kind($vars);
            $this->authorize($kind.'.update');
            $this->csrf($request);
            $next = $this->service()->toggle($kind, (int) ($vars['id'] ?? 0));
            $this->success($response, 'Đã chuyển trạng thái sang '.$next.'.', '/admin/'.$kind);
        });
    }

    private function renderForm(Response $response, string $kind, array $item, array $errors, int $status): void
    {
        $service = $this->service();
        $this->html($response, 'admin/'.$kind.'/form', [
            'kind'=>$kind, 'item'=>$item, 'errors'=>$errors,
            'label'=>$service->label($kind), 'pageTitle'=>($item['id'] ?? false ? 'Sửa ' : 'Thêm ').$service->label($kind),
        ], $status);
    }
}
