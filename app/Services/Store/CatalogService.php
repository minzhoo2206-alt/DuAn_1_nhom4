<?php
declare(strict_types=1);

namespace App\Services\Store;

use App\Repositories\Store\ProductCatalogRepositoryInterface;
use InvalidArgumentException;

/** Quy tắc nghiệp vụ catalog: lọc, phân trang và nhóm biến thể. */
final class CatalogService
{
    public function __construct(private ProductCatalogRepositoryInterface $products)
    {
    }

    public function listing(int $page = 1, int $perPage = 12, array $filters = []): array
    {
        $page = max(1, $page);
        $perPage = min(48, max(1, $perPage));
        $filters = $this->normalizeFilters($filters);

        $total = $this->products->countActive($filters);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $rows = $this->products->listActive($filters, $perPage, $offset);

        $items = array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'description' => $row['description'],
            'brand_name' => $row['brand_name'],
            'category_name' => $row['category_name'],
            // Đây là storage key, CHƯA phải URL ảnh public.
            'thumbnail_key' => $row['thumbnail_key'],
            // Giá hiện hành từ MySQL; ưu tiên sale_price khi nằm trong thời gian áp dụng.
            'min_price' => $row['min_price'],
            'max_price' => $row['max_price'],
            'available' => null, // Chưa kết luận tồn kho từ ERD.
        ], $rows);

        return [
            'products' => $items,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'filters' => $filters,
            'brands' => $this->products->activeBrands(),
            'categories' => $this->products->activeCategories(),
        ];
    }

    public function detail(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }
        $product = $this->products->findActiveBySlug($slug);
        if ($product === null) {
            return null;
        }

        $id = (int) $product['id'];
        $images = $this->products->imagesFor($id);
        $configurations = [];

        foreach ($this->products->configurationsAndVariantsFor($id) as $row) {
            $configurationId = (int) $row['configuration_id'];
            if (!isset($configurations[$configurationId])) {
                $configurations[$configurationId] = [
                    'id' => $configurationId,
                    'storage_gb' => $row['storage_gb'],
                    'ram_gb' => $row['ram_gb'],
                    'list_price' => $row['list_price'],
                    'sale_price' => $row['sale_price'],
                    'sale_starts_at' => $row['sale_starts_at'],
                    'sale_ends_at' => $row['sale_ends_at'],
                    'vat_rate' => $row['vat_rate'],
                    'vat_applicable' => $row['vat_applicable'],
                    'variants' => [],
                ];
            }
            // Không thêm biến thể nếu chưa có biến thể hoặc màu bị ẩn/xóa.
            if ($row['variant_id'] !== null && $row['color_name'] !== null) {
                $configurations[$configurationId]['variants'][] = [
                    'id' => (int) $row['variant_id'],
                    'sku' => $row['sku'],
                    'color_name' => $row['color_name'],
                    'in_stock' => (int)($row['in_stock'] ?? 0),
                ];
            }
        }

        $product['id'] = $id;
        $product['images'] = $images;
        $product['configurations'] = array_values($configurations);
        return $product;
    }

    private function normalizeFilters(array $filters): array
    {
        $result = [];
        foreach (['q', 'brand', 'category'] as $key) {
            $value = $filters[$key] ?? '';
            $result[$key] = is_scalar($value) ? trim((string) $value) : '';
        }

        $price = $filters['price'] ?? 'all';
        $result['price'] = is_string($price) ? $price : 'all';
        if (!in_array($result['price'], ['all', 'under5', '5to10', 'over10'], true)) {
            throw new InvalidArgumentException('Khoảng giá không hợp lệ.');
        }

        if (strlen($result['q']) > 120) {
            throw new InvalidArgumentException('Từ khóa tìm kiếm quá dài (tối đa 120 byte).');
        }
        foreach (['brand', 'category'] as $key) {
            if (strlen($result[$key]) > 120 ||
                preg_match('/^[a-z0-9-]*$/D', $result[$key]) !== 1) {
                throw new InvalidArgumentException('Slug bộ lọc không hợp lệ.');
            }
        }
        return $result;
    }
}
