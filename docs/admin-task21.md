# FOURTECH — Task 21: hoàn thiện catalog Admin local

Nền tảng: Task 20 + Dashboard Fix. Không sửa Core, schema hay file môi trường. Sao lưu hoặc commit các thay đổi đang có trước khi chép gói.

## Chạy

- `php docs/check-admin-task21.php` (23 kiểm tra offline)
- `php docs/check-admin-task20.php --offline` (hồi quy 7 nhóm)
- `php -S 127.0.0.1:8001 -t public docs/admin-db-test.php`
- Đăng nhập bằng Admin local Task 17; mở `/admin/products`.

## Contract local

GET `/admin/products`: q (tên hoặc slug, tối đa 200 ký tự Unicode), brand_id/category_id (0 hoặc bỏ trống = tất cả), status (draft/active/hidden hoặc rỗng), page. Các điều kiện kết hợp AND, tên/slug OR, LIKE tìm chuỗi con với ký tự %, _, ! được escape. COUNT và SELECT dùng chung predicate; loại bản ghi xóa mềm. Phân trang và thao tác vòng đời giữ bộ lọc, riêng thêm/sửa quay về danh sách mặc định như trước.

POST `/admin/products/{id}/activate`: cần `task17.catalog.write`, token HMAC theo action và ID. Chỉ chuyển draft/hidden sang active nếu brand/category còn hoạt động và chưa xóa. UPDATE có điều kiện trạng thái, không xóa bản ghi, không thay đổi tồn kho. Không cấp thêm quyền mới. Staff chỉ đọc không thấy nút ghi ở danh sách sản phẩm; route vẫn kiểm tra quyền server.

## Nghiệm thu local

1. Tìm iPhone, lọc Apple + trạng thái draft, thử bộ lọc không có kết quả.
2. Chuyển trang khi kết quả nhiều hơn 10; kiểm tra điều kiện được giữ.
3. Chọn sản phẩm test draft/hidden, kích hoạt, ẩn rồi kích hoạt lại; đổi bộ lọc thành tất cả/active nếu bản ghi biến mất khỏi danh sách draft.
4. Kiểm tra `SELECT id,name,status,deleted_at FROM products WHERE id = ...;` trên fourtech_admin_test.
5. Đăng nhập staff chỉ có quyền đọc; không thấy các nút ghi, POST trực tiếp không được phép.
6. Dashboard, danh mục, thương hiệu, tài khoản, RBAC, đơn hàng vẫn truy cập được theo quyền.

## Giới hạn

Gói chỉ sửa router và View kiểm thử local cùng Repository/Service dùng chung. Controller và View chính thức chưa nối chức năng mới; phải tích hợp với Minh ở Task 23. Active chỉ là trạng thái catalog mô phỏng, chưa đủ điều kiện bán hàng thật: giá, cấu hình, ảnh, IMEI, tồn kho và quy tắc xuất bản phải thống nhất với Nhuận. Không chạy trên hosting. Chưa có PDO MySQL trong môi trường tạo gói, chưa kiểm thử SQL ghi trực tiếp hay thao tác đồng thời. 23 kiểm tra offline bao gồm predicate/validation/quy tắc quyền/render View, không phải 23 integration tests. 
