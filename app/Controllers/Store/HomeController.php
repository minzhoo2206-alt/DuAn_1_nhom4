<?php
declare(strict_types=1);

namespace App\Controllers\Store;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\Store\ProductRepository;
use App\Services\Store\CatalogService;

/** Trang chủ dùng cùng CatalogService, không lấy sản phẩm từ JavaScript giả. */
final class HomeController
{
    public function index(Request $request, Response $response, array $params = []): void
    {
        $catalog = new CatalogService(new ProductRepository());
        $data = $catalog->listing(1, 4);
        $response->html(View::render('store/home', $data));
    }
}
