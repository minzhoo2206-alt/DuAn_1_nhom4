# Kế hoạch test & nghiệm thu của Đăng

## Test chức năng Admin (thực hiện khi ghép dự án)

| ID | Kịch bản | Kết quả kỳ vọng | Mức ưu tiên |
| --- | --- | --- | --- |
| A01 | Guest mở `/admin/products` | 401 hoặc redirect login | High |
| A02 | Customer mở URL Admin trực tiếp | 403 | Critical |
| A03 | Admin có products.view xem list | 200, dữ liệu DB, phân trang | High |
| A04 | Admin tạo sản phẩm hợp lệ | Redirect, có bản ghi DB | High |
| A05 | Bỏ trống name / slug sai | HTTP 422, hiển thị lỗi | High |
| A06 | Slug trùng | 422 hoặc lỗi ràng buộc DB thân thiện | High |
| A07 | Sửa ID không tồn tại | 404 | Medium |
| A08 | POST không có CSRF | 419 hoặc bị middleware chặn | Critical |
| A09 | Ẩn/hiện sản phẩm | Đổi status, không xóa cứng | High |
| A10 | Search, phân trang, empty state | Đúng kết quả, không lỗi UI | Medium |
| A11 | Admin gán role cho tài khoản khác | Role được gán một lần | High |
| A12 | Admin tự cấp role cho chính mình | 403 | Critical |
| A13 | Staff chỉ có quyền xem, POST update | 403 | Critical |
| A13b | Admin tự thêm permission vào role của mình | 403 | Critical |
| A14 | Admin xem đơn hàng | Danh sách read-only đúng DB | High |
| A15 | Admin tự đổi trạng thái đơn trước contract | 501 (route chưa mở) | High |
| A16 | Chạy RolePermissionSeeder 2 lần | Không tạo role/permission trùng | High |
| A17 | Chạy CatalogSeeder 2 lần | Không tăng số bản ghi demo | Medium |
| A18 | Cùng slug được submit cạnh tranh | DB unique index chặn trùng | High |
| A19 | Test XSS trong name/slug | Output được escape | Critical |
| A20 | Mobile/tablet Admin list/form | Không tràn ngang ngoài vùng bảng; nút sử dụng được | Medium |

## Tự động hiện có

- `tests/Feature/Admin/CatalogAdminServiceTest.php`: validation, CRUD happy/error paths, toggle.
- `tests/Feature/Admin/AdminAuthorizationTest.php`: guest 401, không permission 403, đúng quyền, CSRF 419.
- `tests/Feature/Admin/RolePermissionServiceTest.php`: chặn tự cấp quyền.
- `tests/Integration/OrderAdminBoundaryTest.php`: không tự cập nhật đơn khi chưa có contract.
- `tests/Integration/smoke_admin.php`: CLI không cần Composer/DB.

**Giới hạn:** chưa có MySQL/DDL/Router dự án và account test nên chưa kiểm chứng API HTTP end-to-end hoặc seed idempotency trên DB thật. Không đánh dấu các test chưa chạy là PASS.

## QA workflow

1. Tạo DB test riêng, chạy migration của Nhuận.
2. Seed demo và ghi kết quả.
3. Thử cả role admin, staff, customer, guest; CSRF hợp lệ/sai.
4. Kiểm tra payload injection, XSS, duplicate slug.
5. Chạy PHPUnit toàn nhóm và `npm run build`; xem diff/PR.
6. Đưa lỗi vào mẫu `docs/BUG_REPORT_TEMPLATE.md`, sau fix chạy regression.
