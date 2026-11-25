# Đặc tả Admin – FOURTECH (phần Đăng)

**Nguồn:** Kế hoạch phân công 4 thành viên 18/11/2026: mục 3, phần A, B.4 và phụ lục C. BRD-CT-001 05/11/2026 là tài liệu nghiệp vụ bản Draft về linh kiện/thiết bị, còn dự án thực tế được nhóm xác định bán điện thoại. Các trường DDL chưa được xác minh.

## Phạm vi & luồng

| Module | Nghiệp vụ | Quyền | Dữ liệu input | Kết quả / lỗi |
| --- | --- | --- | --- | --- |
| Dashboard | Xem số lượng catalog | dashboard.view | không | Count / 401,403,500 |
| Products | Danh sách, tìm kiếm, phân trang | products.view | q, page | items, total, pages / 401,403 |
| Products | Tạo bản ghi | products.create | name, slug, status, _csrf | Redirect / 419,422,403 |
| Products | Chỉnh sửa, ẩn/hiện | products.update | id,name,slug,status,_csrf | Redirect / 404,419,422 |
| Categories | CRUD + ẩn/hiện | categories.view/create/update | như Product | như Product |
| Brands | CRUD + ẩn/hiện | brands.view/create/update | như Product | như Product |
| Roles | Xem user/role, gán role cho user khác | users.view / users.assign_roles | user_id,role_id,_csrf | Redirect / 403,404,419,422 |
| Permissions | Cấp permission cho role khác | roles.assign_permissions | role_id, permission_id, _csrf | Redirect / 403,404,419,422 |
| Orders | Xem danh sách, lọc, chi tiết | orders.view | page,status,id | Read-only / 404,403 |
| Orders | Cập nhật trạng thái | orders.update | status,id,_csrf | **Chưa tích hợp; 501** |

## Quy tắc quan trọng

- Không cho xóa cứng sản phẩm/danh mục/thương hiệu: chỉ `active` ↔ `inactive`.
- Không tin quyền gửi từ frontend hoặc chỉ ẩn nút; mỗi controller đều kiểm tra RBAC từ database.
- Không cho tài khoản tự gán role cho chính mình.
- Mỗi thay đổi cần POST + CSRF token, dùng chuẩn server side.
- Không viết SQL trong controller/view. PDO chuẩn bị statement ở repository.
- `products.slug` phải duy nhất (nên có unique constraint trong DDL, phụ thuộc Nhuận).
- Không thay đổi order-status hoặc inventory trong Admin trước khi có hợp đồng nghiệp vụ với Nhuận.
- Form sản phẩm chỉ có 3 trường bản master là bản khởi đầu theo phụ lục C; chưa đầy đủ catalog điện thoại/variants.

## Cần Minh/Nhuận xác nhận

1. Schema bảng `products/categories/brands`: name, slug, status và enum chính xác, unique index.
2. `users` session key chứa ID khi đã authenticate. Trong bản này: `$_SESSION['user_id']`.
3. RBAC: bảng `user_has_roles`, `role_has_permissions`; các cột và name/id.
4. CSRF contract: bản Admin dùng POST `_csrf` ↔ `$_SESSION['admin_csrf']`; phải đồng bộ middleware của Minh.
5. DDL `orders`, DTO chi tiết đơn, state machine và service đổi trạng thái.
6. Dashboard có thể bổ sung KPI doanh thu sau khi có hợp đồng reporting, không giả lập con số.
