<?php
declare(strict_types=1);
namespace Database\Seeders;

use PDO;

require_once __DIR__.'/RolePermissionSeeder.php';
require_once __DIR__.'/AdminUserSeeder.php';
require_once __DIR__.'/CatalogSeeder.php';

final class DatabaseSeeder
{
    public function run(PDO $db): void
    {
        (new RolePermissionSeeder())->run($db);
        (new AdminUserSeeder())->run($db);
        // Seed 100 sản phẩm/20 danh mục chỉ khi gọi thủ công để tránh ghi dữ liệu mẫu vào DB chung.
    }

    public function seedCatalogDemo(PDO $db): void
    {
        (new CatalogSeeder())->run($db);
    }
}
