<?php
declare(strict_types=1);
namespace App\Controllers\Store;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\Store\UserRepository;
use App\Services\Store\SessionAuth;
use App\Services\Store\CsrfService;

/** Adapter HTTP của Store: tách khỏi logic nghiệp vụ và repository. */
abstract class StoreController
{
    protected function view(Response $response, string $name, array $data=[], int $status=200): void
    { $response->html(View::render('store/'.$name,$data), $status); }

    protected function redirect(string $url): void
    { header('Location: '.$url, true, 303); }

    protected function user(Response $response): ?int
    {
        $id=SessionAuth::id();
        if (!$id) { $this->redirect('/login');return null; }
        $row=(new UserRepository())->findById($id);
        if (!$row || $row['status']!=='active') {
            SessionAuth::logout();$this->redirect('/login');return null;
        }
        return $id;
    }

    protected function csrf(Response $response): bool
    {
        if (!CsrfService::verify($_POST['_csrf'] ?? null)) {
            $response->html('419 - Phiên biểu mẫu không hợp lệ, vui lòng tải lại trang.', 419);
            return false;
        }
        return true;
    }

    protected function post(string $key): string
    { $value=$_POST[$key] ?? '';return is_string($value) ? $value : ''; }
}
