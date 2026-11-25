# Ghi chú tích hợp cho 4 thành viên

**Đăng đang bàn giao:** Admin MVC, CRUD catalog tối thiểu, RBAC, order read-only, seeders, tests và docs.

| Người nhận | Cần cung cấp/xử lý | Owner |
| --- | --- | --- |
| Minh | Đăng ký Admin routes; middleware auth, permission, CSRF; session security | Minh |
| Nhuận | DDL thật, FK/enum/status; contract Store catalog, Auth session user ID, OrderService transitions | Nhuận |
| Tín | Kiểm thử style Admin vs Store, responsive/assets, không sửa vùng Admin nếu chưa review | Tín |
| Đăng | Hoàn thiện query theo DDL, kiểm thử HTTP/DB, xử lý bug Admin và báo kết quả | Đăng |

## Giới hạn hiện tại và rủi ro cần xử lý

- Cột `status` của categories/brands và cột đơn hàng chưa được xác nhận. Repo đang dùng giả định theo code mẫu; có thể chưa chạy được trên schema của nhóm.
- Auth của Nhuận/Minh phải cung cấp `$_SESSION['user_id']`. Tránh tin `$_SESSION['role']` do mã ngoài đặt.
- Seeder admin demo phụ thuộc users.email + password/password_hash; bắt buộc local/test.
- Products có thể bắt buộc category/brand/config/variant/timestamp khác. Đối chiếu trước khi ghi.
- Chưa đủ thông tin cập nhật trạng thái đơn, tồn kho, giao hàng, hoàn tiền; không mở route POST status.
- BRD bản Draft vẫn mô tả linh kiện máy tính; kế hoạch nhóm đã chuyển sang điện thoại. Phải chốt lại BA scope trước khi bổ sung POS, kho, bảo hành.

## Pull Request đề xuất

- Branch: `feature/dang-admin-module`.
- Commit 1: `feat(admin): add catalog dashboard and views`.
- Commit 2: `feat(admin): add server-side permissions and roles`.
- Commit 3: `feat(admin): add order read-only screens and demo seeders`.
- Commit 4: `test(admin): cover validation and authorization`.
- Commit 5: `docs(admin): add contract and integration notes`.

## Trạng thái QA hiện tại

- Smoke test logic PHP độc lập: kiểm tra bằng `php tests/Integration/smoke_admin.php`.
- PHP syntax: lint toàn bộ file trong package.
- MySQL end-to-end: **chưa xác nhận vì thiếu dự án + DDL chung**.
- HTTP middleware/route integration: **chờ Minh**.
- Order transitions: **chờ Nhuận**.
