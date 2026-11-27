<?php

declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Database, Request, Response, View};
use App\Repositories\Admin\BrandAdminRepository;
use App\Services\Admin\BrandAdminService;

// Chỉ đăng ký route sau khi tích hợp middleware xác thực, phân quyền và CSRF của nhóm.
final class BrandAdminController
{
    private BrandAdminService $service;
    public function __construct() { $this->service = new BrandAdminService(new BrandAdminRepository(Database::connection())); }
    private function token(): string { return $_SESSION['fourtech_brand_token'] ??= bin2hex(random_bytes(32)); }
    private function valid(Request $request): bool
    {
        $token = $request->input('brand_token', '');
        $expected = $_SESSION['fourtech_brand_token'] ?? '';
        return is_string($token) && is_string($expected) && $expected !== '' && hash_equals($expected, $token);
    }
    public function index(Request $request, Response $response, array $params = []): void
    {
        $data = ['brands'=>$this->service->listing(), 'brandToken'=>$this->token(), 'success'=>$_SESSION['fourtech_brand_success'] ?? '', 'brandModuleEnabled'=>true];
        unset($_SESSION['fourtech_brand_success']);
        $response->html(View::render('admin/brands/index', $data));
    }
    private function form(Response $response, int $brandId, array $old = [], string $error = '', int $status = 200): void
    {
        $brandToken = $this->token();
        $brandModuleEnabled = true;
        $response->html(View::render('admin/brands/form', compact('brandId','old','error','brandToken','brandModuleEnabled')), $status);
    }
    public function create(Request $request, Response $response, array $params = []): void { $this->form($response, 0); }
    public function edit(Request $request, Response $response, array $params = []): void
    {
        $id = (int) filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        $row = $this->service->find($id);
        if ($row === null) { $response->html('Không tìm thấy thương hiệu.', 404); return; }
        $this->form($response, $id, $row);
    }
    public function store(Request $request, Response $response, array $params = []): void { $this->save($request,$response,0); }
    public function update(Request $request, Response $response, array $params = []): void
    {
        $id = (int) filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id < 1) { $response->html('Không tìm thấy thương hiệu.',404); return; }
        $this->save($request,$response,$id);
    }
    private function save(Request $request, Response $response, int $id): void
    {
        if (!$this->valid($request)) { $response->html('CSRF token không hợp lệ.',403); return; }
        $old = ['name'=>$request->input('name',''), 'slug'=>$request->input('slug','')];
        try { $this->service->save($old,$id); }
        catch (\OutOfBoundsException) { $response->html('Không tìm thấy thương hiệu.',404); return; }
        catch (\InvalidArgumentException $e) { $this->form($response,$id,$old,$e->getMessage(),422); return; }
        catch (\PDOException $e) { error_log('Brand save SQLSTATE: '.$e->getCode()); $duplicate = (int) ($e->errorInfo[1] ?? 0) === 1062; $this->form($response,$id,$old,$duplicate ? 'Slug đã tồn tại.' : 'Không lưu được thương hiệu.',$duplicate ? 409 : 500); return; }
        $this->done($response,'Lưu thương hiệu thành công!');
    }
    public function activate(Request $request, Response $response, array $params = []): void { $this->action('activate',$request,$response,$params); }
    public function deactivate(Request $request, Response $response, array $params = []): void { $this->action('deactivate',$request,$response,$params); }
    public function softDelete(Request $request, Response $response, array $params = []): void { $this->action('delete',$request,$response,$params); }
    private function action(string $action, Request $request, Response $response, array $params): void
    {
        if (!$this->valid($request)) { $response->html('CSRF token không hợp lệ.',403); return; }
        try { $this->service->lifecycle((int) filter_var($params['id'] ?? null,FILTER_VALIDATE_INT),$action); }
        catch (\OutOfBoundsException) { $response->html('Không tìm thấy thương hiệu.',404); return; }
        catch (\InvalidArgumentException $e) { $response->html(htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8'),409); return; }
        catch (\PDOException $e) { error_log('Brand action SQLSTATE: '.$e->getCode()); $response->html('Không thực hiện được thao tác.',500); return; }
        $this->done($response,'Cập nhật trạng thái thương hiệu thành công!');
    }
    private function done(Response $response, string $message): void
    {
        $_SESSION['fourtech_brand_success'] = $message;
        unset($_SESSION['fourtech_brand_token']);
        $response->redirect('/admin/brands',303);
    }
}
