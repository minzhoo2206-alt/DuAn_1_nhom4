# FOURTECH Task 22 – Giá, cấu hình, biến thể và ảnh (local only)

## Cài đặt
1. Commit Task 21 và Dashboard trước khi ghi đè. Dừng server, giải nén đúng gốc repo.
2. Backup database local `fourtech_admin_test` bằng phpMyAdmin Export. Đăng nhập phpMyAdmin bằng tài khoản quản trị MySQL local; chọn đúng database, Import `docs/admin-test-catalog-details.sql`. SQL thêm 4 bảng và 8 màu mẫu; không xóa hay sửa dữ liệu sản phẩm, tài khoản, đơn hàng cũ. Không chạy trên iNET. Nếu import gặp lỗi, dừng và xem lỗi trước khi tiếp tục; CREATE IF NOT EXISTS không migrate bảng có cấu trúc khác.
3. PHP 64-bit, PDO MySQL, fileinfo; file upload PHP cần `upload_max_filesize` >= 5M, `post_max_size` > 5M để nhận đủ 5MB. Không cần đổi cấu hình nếu ảnh nhỏ tải được. Tài khoản test cần SELECT, INSERT, UPDATE trên 4 bảng mới; quyền cấp toàn DB test ở Task 14.3 đã đủ nếu vẫn còn hiệu lực.
4. `php docs/check-admin-task22.php`, `php docs/check-admin-task21.php`, `php docs/check-admin-task20.php --offline`. Khởi động `php -S 127.0.0.1:8001 -t public docs/admin-db-test.php`. Đăng nhập như Task 17. Vào Sản phẩm, chọn Giá / Biến thể / Ảnh.

## Test thủ công
- Chọn một sản phẩm draft/hidden, thêm cấu hình RAM 8GB, 256GB, giá 29990000 VND. Giá khuyến mãi tùy chọn, nếu nhập thì phải nhỏ hơn giá niêm yết, đủ 2 ngày và ngày kết thúc sau ngày bắt đầu. Giá nguyên VND; ngày/giờ local, chưa tính giá checkout theo lịch.
- Thêm biến thể từ cấu hình đó, màu Đen, SKU riêng (ví dụ T22-P123-256-BLACK, thay 123 bằng ID sản phẩm). Thử SKU trùng và cấu hình RAM/dung lượng trùng: bị từ chối. Thêm màu khác dùng cùng cấu hình, giá dùng chung. Có sửa cấu hình, biến thể và ngừng/kích hoạt; muốn ngừng thì ẩn sản phẩm trước. Không xóa vật lý cấu hình/biến thể.
- Upload ảnh JPEG/PNG/WebP thật tối đa 5 MB, 6000x6000; thử file không phải ảnh: bị từ chối. Đổi ảnh bằng thêm ảnh mới rồi xóa mềm ảnh cũ. Ảnh được lưu ngoài public, trong `storage/admin-task22-images`, tên ngẫu nhiên; chỉ trả qua route có quyền đọc. Xóa mềm giữ file để tránh mất dữ liệu. Backup thư mục ảnh riêng, không commit ảnh tải lên.
- Kích hoạt sản phẩm từ danh sách: cần thương hiệu, danh mục hoạt động và ít nhất một cấu hình giá dương có biến thể/màu hoạt động. Thử một bản nháp chưa có cấu hình: phải bị từ chối. Các sản phẩm active cũ không bị thay đổi tự động. Không kiểm tra IMEI/tồn kho, không đảm bảo bán thật.
- Tài khoản nhân viên chỉ có quyền đọc thấy dữ liệu, không có form ghi; POST trực tiếp phải bị 403. Kiểm tra lại Dashboard, danh mục, đơn hàng.

## Phạm vi và tích hợp
Theo ERD Database_Ban_dien_thoai.pdf: `product_configurations` lưu RAM, dung lượng, giá; `product_variants` nối cấu hình với `colors` và SKU; `product_images` lưu storage_key. SQL local dùng tên và kiểu cột tương ứng nhưng ràng buộc unique, default VAT 0/false, màu mẫu là quyết định kiểm thử, không phải migration production. Không sửa Core/routes chính thức hoặc schema hosting. Không tạo tự động cấu hình/ảnh cho 100 sản phẩm hiện có. Chưa có bảng tồn kho/IMEI, giá checkout theo thời gian, tính VAT, audit theo actor, CRUD màu, chỉnh thứ tự ảnh sau upload (có thể thêm ảnh mới), resize/re-encode ảnh, dọn file xóa mềm, hoặc tích hợp storage production. Phải thống nhất với Nhuận và Minh trước khi merge. Hạn chế nhập dữ liệu demo, không đưa tài liệu/upload local lên môi trường công khai.

## Kiểm thử
Offline: Task 22 31 checks, Task 21 23 checks, Task 20 7 nhóm đạt trên bộ code ghép. Chưa có PDO MySQL server trong môi trường tạo gói nên chưa chạy SQL thật, chưa thử upload HTTP thật. Kiểm tra trên máy người dùng trước khi commit.
