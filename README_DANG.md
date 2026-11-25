# FOURTECH – Phần Đăng (Admin + RBAC + Integration)

Bản bàn giao **chỉ chứa file thuộc quyền sở hữu của Đăng** theo kế hoạch 18/11/2026, không sửa `routes/`, `bootstrap/`, `app/Core/`, `resources/css/`, `resources/js/`, `database/migrations/` hoặc module Store.

## Đã có mã nguồn

- Dashboard Admin; layout/sidebar responsive, flash, form và table.
- CRUD có **thêm/xem/sửa/ẩn-hiện** sản phẩm, danh mục và thương hiệu qua Controller → Service → PDO Repository; search, pagination, validation, unique slug.
- Kiểm tra quyền thực sự ở server, CSRF cho các POST, trả 401/403/419/422/404 khi thích hợp.
- Trang user/role và gán role (chặn tự nâng quyền).
- Danh sách/chi tiết đơn **chỉ đọc**; thay đổi trạng thái chủ ý từ chối (501) đến khi Nhuận cung cấp OrderService.
- Seeder quyền, account demo (chỉ local/test, mật khẩu nhận từ ENV), 20 danh mục + 8 thương hiệu + 100 sản phẩm DEMO (tuỳ chọn, tất cả sản phẩm là draft).
- PHPUnit test cho validate/permission/RBAC và smoke test PHP chạy độc lập.
- Đặc tả, danh sách route gửi Minh, test plan, ghi chú tích hợp, hướng dẫn cài đặt.

## Điều kiện tích hợp (quan trọng)

1. Dự án của **Minh** phải có PHP 8.3+, Composer PSR-4 `App\` ↔ `app/`, `App\Core\\Database`, `Request`, `Response`, `View` và router FastRoute như trong tài liệu.
2. Dự án của **Nhuận** phải tạo schema SQL chuẩn. Mã hiện tại dùng **giả định** `products`, `categories`, `brands` có các cột `id,name,slug,status` (status = `draft`, `active`, `inactive`); `orders` có `id,status,created_at`; RBAC có bảng liên kết như trong docs. Không chạy seed lên DB thật cho đến khi xác minh.
3. Minh thêm các route trong `docs/API_CONTRACT.md`, bảo đảm auth/session và CSRF; đồng bộ khóa session `user_id` mà Auth của Nhuận sẽ gán. Phần Đăng cũng kiểm tra quyền và CSRF trong controller nhưng không thay middleware của Minh.
4. Tín bảo đảm Vite/Tailwind build hoặc có thể dùng CSS nội tuyến riêng của Admin layout; không sửa asset phía Store.

## Kiểm tra nhanh KHÔNG CẦN database

Tại thư mục gốc package này:

```sh
php tests/Integration/smoke_admin.php
```

Để chạy PHPUnit khi đã ghép vào dự án nhóm:

```sh
composer test -- --filter 'CatalogAdminServiceTest|AdminAuthorizationTest|RolePermissionServiceTest|OrderAdminBoundaryTest'
```

## Cách nhập vào dự án nhóm

Giải nén package và **chỉ sao chép** thư mục `app/Controllers/Admin`, `app/Services/Admin`, `app/Repositories/Admin`, `resources/views/admin`, `database/seeders`, `tests/Feature/Admin`, `tests/Integration`, `docs`. Không chép đè file của thành viên khác. Xem `docs/README_ADMIN.md` để cài đặt/seed và `docs/API_CONTRACT.md` để Minh thêm route.

## Các điểm chưa thể khẳng định hoàn thành

- Chưa có source code thực tế của Minh/Nhuận/Tín hay DDL được xác thực, nên **chưa chạy end-to-end với MySQL và chưa xác nhận tương thích**.
- Form product hiện là **bản master tối thiểu** (name/slug/status). Giá, cấu hình, biến thể, hình ảnh, serial/IMEI cần DDL và contract Store trước khi bổ sung.
- Chưa có contract cho update order/status; không tự cập nhật bảng `orders` hoặc kho.
- Seeder catalog chỉ được dùng với cấu trúc DB chấp nhận tập cột demo; nếu DB có cột NOT NULL bắt buộc khác thì phải cập nhật sau khi chốt schema.
- Các test trong package đã được viết nhưng chỉ smoke test độc lập có thể chạy nếu chưa có `vendor`, PHPUnit và MySQL.
