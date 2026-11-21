<?php
declare(strict_types=1);

use App\Repositories\Store\ProductCatalogRepositoryInterface;
use App\Services\Store\CatalogService;
use PHPUnit\Framework\TestCase;

/** Fake chỉ nằm trong test, không đọc database thật. */
final class FakeCatalogRepository implements ProductCatalogRepositoryInterface
{
    public array $lastFilters = [];
    public int $lastLimit = 0;
    public int $lastOffset = -1;
    public int $total = 13;
    public ?array $product = ['id' => 7, 'name' => 'Máy mẫu', 'slug' => 'may-mau'];

    public function countActive(array $filters): int
    {
        $this->lastFilters = $filters;
        return $this->total;
    }

    public function listActive(array $filters, int $limit, int $offset): array
    {
        $this->lastLimit = $limit;
        $this->lastOffset = $offset;
        if ($this->total === 0) {
            return [];
        }
        return [[
            'id' => 7, 'name' => 'Máy mẫu', 'slug' => 'may-mau',
            'description' => 'Mô tả', 'brand_name' => 'Demo',
            'category_name' => 'Điện thoại', 'thumbnail_key' => 'demo.webp',
            'min_price' => '1000000.00', 'max_price' => '2000000.00',
        ]];
    }

    public function findActiveBySlug(string $slug): ?array
    {
        return $slug === 'may-mau' ? $this->product : null;
    }

    public function imagesFor(int $productId): array
    {
        return [['storage_key' => 'demo.webp']];
    }

    public function configurationsAndVariantsFor(int $productId): array
    {
        $base = [
            'configuration_id' => 50, 'storage_gb' => 128, 'ram_gb' => 8,
            'list_price' => '1000000.00', 'sale_price' => null,
            'sale_starts_at' => null, 'sale_ends_at' => null,
            'vat_rate' => '10.00', 'vat_applicable' => 1,
        ];
        return [
            $base + ['variant_id' => 101, 'sku' => 'SKU-RED', 'color_name' => 'Đỏ'],
            $base + ['variant_id' => 102, 'sku' => 'SKU-BLUE', 'color_name' => 'Xanh'],
        ];
    }

    public function activeBrands(): array
    {
        return [['name' => 'Demo', 'slug' => 'demo']];
    }

    public function activeCategories(): array
    {
        return [['name' => 'Điện thoại', 'slug' => 'dien-thoai']];
    }
}

final class CatalogServiceTest extends TestCase
{
    public function testPaginationAndFilters(): void
    {
        $fake = new FakeCatalogRepository();
        $result = (new CatalogService($fake))->listing(2, 12, [
            'q' => '  máy  ', 'brand' => 'demo', 'category' => '',
        ]);

        self::assertSame(2, $result['page']);
        self::assertSame(2, $result['totalPages']);
        self::assertSame(13, $result['total']);
        self::assertSame(12, $fake->lastOffset);
        self::assertSame('máy', $fake->lastFilters['q']);
        self::assertSame('demo', $fake->lastFilters['brand']);
        self::assertSame('1000000.00', $result['products'][0]['min_price']);
    }

    public function testDetailGroupsTwoColorsIntoOneConfiguration(): void
    {
        $result = (new CatalogService(new FakeCatalogRepository()))->detail('may-mau');

        self::assertNotNull($result);
        self::assertCount(1, $result['configurations']);
        self::assertCount(2, $result['configurations'][0]['variants']);
        self::assertSame('SKU-RED', $result['configurations'][0]['variants'][0]['sku']);
    }

    public function testUnknownProductIsNull(): void
    {
        self::assertNull((new CatalogService(new FakeCatalogRepository()))->detail('khong-co'));
    }

    public function testEmptyCatalogReturnsPageOne(): void
    {
        $fake = new FakeCatalogRepository();
        $fake->total = 0;
        $result = (new CatalogService($fake))->listing(99, 12);

        self::assertSame(1, $result['page']);
        self::assertSame(1, $result['totalPages']);
        self::assertSame(0, $result['total']);
        self::assertCount(0, $result['products']);
    }

    public function testOutOfRangePageClampsToLastPage(): void
    {
        $fake = new FakeCatalogRepository();
        $result = (new CatalogService($fake))->listing(999, 12);

        self::assertSame(2, $result['page']);
        self::assertSame(12, $fake->lastOffset);
    }

    public function testInvalidBrandSlugIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new CatalogService(new FakeCatalogRepository()))->listing(1, 12, [
            'brand' => 'demo; DROP TABLE products',
        ]);
    }

    public function testPriceFilterIsForwarded(): void
    {
        $fake = new FakeCatalogRepository();
        (new CatalogService($fake))->listing(1, 12, ['price' => 'under5']);
        self::assertSame('under5', $fake->lastFilters['price']);
    }

    public function testInvalidPriceFilterIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new CatalogService(new FakeCatalogRepository()))->listing(1, 12, ['price' => 'under5 OR 1=1']);
    }

}
