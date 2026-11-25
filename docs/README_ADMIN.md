# Hướng dẫn cài đặt module Admin

## 1. Trộn vào repo

Sao chép các thư mục sở hữu Đăng vào repo nhóm. **Không** thay thế Core, Router, migrations hay CSS/JS Store. Code nằm đúng namespace `App\...` tương ứng với `app/`.

## 2. Đồng bộ hợp đồng

- Minh thêm routes từ `docs/API_CONTRACT.md`, gắn middleware auth + permission, CSRF; bảo đảm `App\Core\View::render` hoạt động.
- Nhuận xác nhận DDL và truyền `$_SESSION['user_id']` sau đăng nhập. Schema hiện được **giả định**, không có migration trong package.
- Đăng và Nhuận thống nhất các cột `products/categories/brands`: id,name,slug,status. Nếu DDL khác, phải sửa tại lớp Repository/Admin và báo cho nhóm.

## 3. Lệnh trên Windows/PowerShell (trong thư mục repo)

```powershell
composer install
Copy-Item .env.example .env
# Cập nhật cấu hình database trong .env
npm install
npm run build
php -S 127.0.0.1:8000 -t public
```

Truy cập `http://127.0.0.1:8000/admin` sau khi đăng nhập bằng tài khoản có role Admin (và middleware đã hoàn thành).

## 4. Seed database (chỉ local/test)

Trước khi seed, **chỉ chạy trên DB giả lập** và xác minh schema. Ví dụ lệnh PowerShell (Composer và Dotenv có sẵn):

```powershell
$env:APP_ENV='local'
$env:ADMIN_DEMO_EMAIL='admin.demo@fourtech.local'
# Đặt mật khẩu mạnh qua ENV riêng trên máy local (ít nhất 12 ký tự), KHÔNG commit.
$env:ADMIN_DEMO_PASSWORD = Read-Host 'Nhap mat khau demo 12+ ky tu'
php -r 'require "vendor/autoload.php"; Dotenv\Dotenv::createImmutable(getcwd())->safeLoad(); require "database/seeders/DatabaseSeeder.php"; (new Database\Seeders\DatabaseSeeder())->run(App\Core\Database::connection());'
```

Để thêm 20 danh mục, 8 thương hiệu và 100 sản phẩm **demo draft** (chỉ khi DDL hỗ trợ):

```powershell
php -r 'require "vendor/autoload.php"; Dotenv\Dotenv::createImmutable(getcwd())->safeLoad(); require "database/seeders/DatabaseSeeder.php"; (new Database\Seeders\DatabaseSeeder())->seedCatalogDemo(App\Core\Database::connection());'
```

> Nếu bảng users có cột bắt buộc ngoài email/password/name, `AdminUserSeeder` sẽ cần cập nhật. Đây là tình huống chờ schema, không được thêm cột ngẫu nhiên vào DDL.

## 5. Kiểm thử

```powershell
php tests/Integration/smoke_admin.php
composer test -- --filter Admin
```

Smoke test không cần DB. Feature tests PHPUnit chỉ xác nhận logic cô lập, phải bổ sung kiểm thử HTTP/DB thật khi merge dự án.

## 6. Lỗi thường gặp

- 404 `/admin`: Minh chưa đăng ký routes.
- 401: chưa đăng nhập, hoặc Nhuận chưa đưa `user_id` vào session.
- 403: user không có permission trong DB / chưa chạy RolePermissionSeeder.
- 419: token CSRF không khớp session; kiểm tra middleware và POST `_csrf`.
- 500 SQL Unknown column/table: DDL khác giả định; gửi schema cho Đăng đối chiếu.
- Lỗi asset Tailwind: yêu cầu Tín build Vite, nhưng layout Admin có CSS riêng để dự phòng.
