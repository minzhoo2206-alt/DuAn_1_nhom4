<?php

declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Database, Request, Response, View};
use App\Repositories\Admin\CategoryAdminRepository;
use App\Services\Admin\CategoryAdminService;

// Chỉ đăng ký route sau khi tích hợp middleware xác thực, phân quyền và CSRF của nhóm.
final class CategoryAdminController
{
    private CategoryAdminService $service;
    public function __construct() { $this->service = new CategoryAdminService(new CategoryAdminRepository(Database::connection())); }
    private function token(): string { return $_SESSION['fourtech_category_token'] ??= bin2hex(random_bytes(32)); }
    private function valid(Request $request): bool
    {
        $token = $request->input('category_token', '');
        $expected = $_SESSION['fourtech_category_token'] ?? '';
        return is_string($token) && is_string($expected) && $expected !== '' && hash_equals($expected, $token);
    }
    public function index(Request $request, Response $response, array $params = []): void
    {
        $data = ['categories'=>$this->service->listing(), 'categoryToken'=>$this->token(), 'success'=>$_SESSION['fourtech_category_success'] ?? ''];
        unset($_SESSION['fourtech_category_success']);
        $response->html(View::render('admin/categories/index', $data));
    }
    private function form(Response $response, int $categoryId, array $old = [], string $error = '', int $status = 200): void
    {
        $categoryToken = $this->token();
        $response->html(View::render('admin/categories/form', compact('categoryId','old','error','categoryToken')), $status);
    }
    public function create(Request $request, Response $response, array $params = []): void { $this->form($response, 0); }
    public function edit(Request $request, Response $response, array $params = []): void
    {
        $id = (int) filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        $row = $this->service->find($id);
        if ($row === null) { $response->html('Không tìm thấy danh mục.', 404); return; }
        $this->form($response, $id, $row);
    }
    public function store(Request $request, Response $response, array $params = []): void { $this->save($request,$response,0); }
    public function update(Request $request, Response $response, array $params = []): void
    {
        $id = (int) filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id < 1) { $response->html('Không tìm thấy danh mục.',404); return; }
        $this->save($request,$response,$id);
    }
    private function save(Request $request, Response $response, int $id): void
    {
        if (!$this->valid($request)) { $response->html('CSRF token không hợp lệ.',403); return; }
        $old = ['name'=>$request->input('name',''), 'slug'=>$request->input('slug','')];
        try { $this->service->save($old,$id); }
        catch (\OutOfBoundsException) { $response->html('Không tìm thấy danh mục.',404); return; }
        catch (\InvalidArgumentException $e) { $this->form($response,$id,$old,$e->getMessage(),422); return; }
        catch (\PDOException $e) { error_log('Category save SQLSTATE: '.$e->getCode()); $this->form($response,$id,$old,'Không lưu được danh mục.',500); return; }
        $this->done($response,'Lưu danh mục thành công!');
    }
    public function activate(Request $request, Response $response, array $params = []): void { $this->action('activate',$request,$response,$params); }
    public function deactivate(Request $request, Response $response, array $params = []): void { $this->action('deactivate',$request,$response,$params); }
    public function softDelete(Request $request, Response $response, array $params = []): void { $this->action('delete',$request,$response,$params); }
    private function action(string $action, Request $request, Response $response, array $params): void
    {
        if (!$this->valid($request)) { $response->html('CSRF token không hợp lệ.',403); return; }
        try { $this->service->lifecycle((int) filter_var($params['id'] ?? null,FILTER_VALIDATE_INT),$action); }
        catch (\OutOfBoundsException) { $response->html('Không tìm thấy danh mục.',404); return; }
        catch (\InvalidArgumentException $e) { $response->html(htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8'),409); return; }
        catch (\PDOException $e) { error_log('Category action SQLSTATE: '.$e->getCode()); $response->html('Không thực hiện được thao tác.',500); return; }
        $this->done($response,'Cập nhật trạng thái danh mục thành công!');
    }
    private function done(Response $response, string $message): void
    {
        $_SESSION['fourtech_category_success'] = $message;
        unset($_SESSION['fourtech_category_token']);
        $response->redirect('/admin/categories',303);
    }
}
