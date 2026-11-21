<?php
declare(strict_types=1);

namespace App\Controllers\Store;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\Store\ProductRepository;
use App\Services\Store\CatalogService;
use InvalidArgumentException;

/** Controller đọc input HTTP, gọi Service rồi truyền dữ liệu sang View của Tín. */
final class ProductController
{
    private CatalogService $catalog;

    public function __construct()
    {
        // Router trong kế hoạch dùng "new $controllerClass()", nên không cần tham số.
        $this->catalog = new CatalogService(new ProductRepository());
    }

    public function index(Request $request, Response $response, array $params = []): void
    {
        $rawPage = $request->input('page', 1);
        $page = filter_var($rawPage, FILTER_VALIDATE_INT);
        $page = $page === false ? 1 : max(1, $page);

        try {
            $data = $this->catalog->listing($page, 12, [
                'q' => $request->input('q', ''),
                'brand' => $request->input('brand', ''),
                'category' => $request->input('category', ''),
                'price' => $request->input('price', 'all'),
            ]);
        } catch (InvalidArgumentException $exception) {
            $response->html('Bộ lọc không hợp lệ.', 400);
            return;
        }

        // Tín sẽ tạo resources/views/store/products/index.php.
        $response->html(View::render('store/products/index', $data));
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $product = $this->catalog->detail((string) ($params['slug'] ?? ''));
        if ($product === null) {
            $response->html('Không tìm thấy sản phẩm', 404);
            return;
        }

        // Tín sẽ tạo resources/views/store/products/show.php.
        $response->html(View::render('store/products/show', ['product' => $product]));
    }
}
