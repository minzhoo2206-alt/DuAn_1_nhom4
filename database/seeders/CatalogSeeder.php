<?php
declare(strict_types=1);
namespace Database\Seeders;

use PDO;
use RuntimeException;

/** Dữ liệu giả để trình diễn; sản phẩm draft, không tạo IMEI/tồn kho thật. */
final class CatalogSeeder
{
    private const CATEGORIES = [
        'Điện thoại thông minh','Điện thoại phổ thông','Điện thoại chụp ảnh','Điện thoại chơi game',
        'Điện thoại pin lớn','Điện thoại nhỏ gọn','Điện thoại màn hình gập','Điện thoại 5G',
        'Điện thoại cao cấp','Điện thoại tầm trung','Điện thoại giá rẻ','Điện thoại doanh nhân',
        'Điện thoại màn hình lớn','Điện thoại bền bỉ','Điện thoại thiết kế mỏng','Điện thoại cho học sinh',
        'Điện thoại cho sinh viên','Điện thoại camera selfie','Điện thoại sạc nhanh','Điện thoại mới ra mắt',
    ];
    private const BRANDS = ['Apple','Samsung','Xiaomi','OPPO','vivo','realme','HONOR','TECNO'];

    public function run(PDO $db): void
    {
        $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production';
        if (!in_array($env, ['local','testing','test'], true)) {
            throw new RuntimeException('Chỉ seed catalog demo trên local/test.');
        }
        $db->beginTransaction();
        try {
            $categoryIds = []; $brandIds = [];
            foreach (self::CATEGORIES as $i=>$name) {
                $categoryIds[] = $this->upsert($db, 'categories', $name, 'dm-demo-'.str_pad((string)($i+1),2,'0',STR_PAD_LEFT));
            }
            foreach (self::BRANDS as $i=>$name) {
                $brandIds[] = $this->upsert($db, 'brands', $name, 'brand-demo-'.($i+1));
            }
            $columns = $db->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_COLUMN);
            for ($i=1;$i<=100;$i++) {
                $name = 'Điện thoại DEMO '.str_pad((string)$i, 3, '0', STR_PAD_LEFT);
                $slug = 'fourtech-demo-phone-'.str_pad((string)$i, 3, '0', STR_PAD_LEFT);
                $brandId = $brandIds[($i-1)%count($brandIds)];
                $categoryId = $categoryIds[($i-1)%count($categoryIds)];
                $data = ['name'=>$name, 'slug'=>$slug, 'status'=>'draft'];
                if (in_array('brand_id', $columns, true)) { $data['brand_id'] = $brandId; }
                if (in_array('category_id', $columns, true)) { $data['category_id'] = $categoryId; }
                if (in_array('description', $columns, true)) { $data['description'] = 'Sản phẩm mẫu nội bộ. Không dùng làm hàng bán thật.'; }
                $this->upsertProduct($db, $data);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw new RuntimeException('CatalogSeeder cần đối chiếu DDL products/categories/brands thực tế.', 0, $e);
        }
    }

    private function upsert(PDO $db, string $table, string $name, string $slug): int
    {
        $stmt = $db->prepare("SELECT id FROM {$table} WHERE slug=:slug LIMIT 1");
        $stmt->execute(['slug'=>$slug]);
        $id = $stmt->fetchColumn();
        if ($id !== false) { return (int)$id; }
        $stmt = $db->prepare("INSERT INTO {$table} (name,slug,status) VALUES (:name,:slug,:status)");
        $stmt->execute(['name'=>$name,'slug'=>$slug,'status'=>'draft']);
        return (int)$db->lastInsertId();
    }

    private function upsertProduct(PDO $db, array $data): void
    {
        $stmt = $db->prepare('SELECT id FROM products WHERE slug=:slug LIMIT 1');
        $stmt->execute(['slug'=>$data['slug']]);
        if ($stmt->fetchColumn() !== false) { return; }
        $columns = array_keys($data); // Chỉ hằng số do seeder tạo ra.
        $fields = implode(',', $columns);
        $placeholders = implode(',', array_map(static fn($col)=>':'.$col, $columns));
        $stmt = $db->prepare("INSERT INTO products ({$fields}) VALUES ({$placeholders})");
        $stmt->execute($data);
    }
}
