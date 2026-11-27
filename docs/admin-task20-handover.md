# FOURTECH — Task 20: Kiểm thử và bàn giao Admin

**Ngày:** 27/11/2026 | **Nhánh:** `feature/dang-admin-module` | **Phạm vi:** module Admin local.

## 1. Cài đặt và kiểm tra

1. Kiểm tra `git status` và `git log --oneline -5`; commit riêng Task 18/19 nếu còn thay đổi, sao lưu database local nếu sẽ thử thao tác ghi. Dừng PHP server (Ctrl+C). Giải nén Task20 vào thư mục gốc dự án. Chỉ ghi đè `app/Repositories/Admin/OrderAdminRepository.php` (bản Task18); nếu đã chỉnh riêng file này, đối chiếu trước khi ghi đè.
2. `php docs/check-admin-task20.php --offline` — lint PHP Admin và chạy các test quy tắc; không cần database. Kết quả cuối `PASS TASK 20 OFFLINE CHECK`.
3. `php docs/check-admin-task20.php --db` — đọc `.env.admin-test` độc lập, chỉ cho 127.0.0.1/fourtech_admin_test, mở transaction READ ONLY, kiểm tra 11 bảng, số fixture, quyền Admin và Repository. Không nạp `.env` hosting; không in mật khẩu hoặc hash. Kết quả cuối `PASS TASK 20 LOCAL READ CHECK`. Nếu FAIL, ghi lại tên bước; không import lại hoặc xóa DB tùy tiện. Số lượng kỳ vọng là fixture chưa bị sửa/xóa (100 sản phẩm, 3 khách, 2+8 đơn; ít nhất 10 hãng, 20 danh mục đang hoạt động); sai lệch cần giải thích, không tự phục hồi hàng đã xóa. Nếu cần kiểm tra idempotency, chạy lại `php docs/seed-admin-demo.php --apply` riêng khi đã sao lưu local và không có thao tác đồng thời; tất cả inserted phải bằng 0 nếu dữ liệu không đổi.
4. `php -S 127.0.0.1:8001 -t public docs/admin-db-test.php` — đăng nhập `admin17@example.test` bằng mật khẩu local đã đặt, không đưa mật khẩu vào Git/báo cáo. Mở /admin/products, /admin/categories, /admin/brands, /admin/users, /admin/rbac, /admin/orders.
5. Task20 sửa phạm vi đơn mẫu: Repository cho phép cả `task18-demo-` lẫn `task19-demo-` ở danh sách, chi tiết, cập nhật; giữ lọc deleted_at và quyền Admin/transaction cũ. Không thay đổi schema hay quy trình đơn hàng. Không dùng Repository này trên hosting thật.

## 2. Kết quả và phạm vi kiểm thử

Trên bản ghép ZIP Task11–19 (cộng file diagnostic 14.3), PHP 8.4.24: lint 52/52 file trong phạm vi runner; brand 21, user 18, RBAC 41, order 33, catalog 18 (131 kiểm thử quy tắc); fixture offline đủ 10 hãng, 20 danh mục, 100 sản phẩm, 3 khách, 8 đơn. 7 nhóm kiểm tra offline đều PASS. Máy tạo gói không có vendor của dự án và PDO MySQL; kiểm tra DB dừng tại prerequisite, chưa chạy SQL. Đây không phải kết quả trên checkout Windows hiện tại. Không xác nhận tích hợp production.

| Mã | Thao tác trên local | Kết quả mong đợi | Thực tế / người kiểm tra |
|---|---|---|---|
| A01 | Đăng nhập Admin, sai mật khẩu, đăng xuất | Đúng vào trang; sai bị từ chối; đăng xuất yêu cầu đăng nhập lại | Chờ ghi |
| A02 | Staff đọc catalog; truy cập trực tiếp /admin/users, /admin/rbac, /admin/orders | Đọc được catalog; trang bị cấm trả 403 | Chờ ghi |
| A03 | Cấp rồi thu quyền ghi catalog cho staff, thử cả POST không token | Quyền đổi ở request tiếp theo; token thiếu bị từ chối; không đổi DB trái phép | Chờ ghi |
| A04 | Thêm, sửa điện thoại test; slug trùng | Lưu đúng, giữ ID/trạng thái; trùng bị chặn | Chờ ghi |
| A05 | Ẩn active, xóa mềm draft/hidden | Không xóa vật lý; deleted_at có giá trị | Chờ ghi |
| A06 | Thêm/sửa/ngừng/kích hoạt/xóa mềm danh mục và hãng test | Chặn trùng slug, chặn ngừng/xóa khi còn sản phẩm | Chờ ghi |
| A07 | Tạo/sửa/khóa/mở khóa/reset mật khẩu nhân viên test | Không lộ mật khẩu, phiên cũ bị vô hiệu khi khóa/đổi mật khẩu; Admin được bảo vệ | Chờ ghi |
| A08 | Lọc đơn theo mã task18 và task19, mở chi tiết | Cả hai nhóm đơn thấy được, chi tiết có dòng hàng | Chờ ghi |
| A09 | Dùng đơn mẫu chưa kết thúc: chuyển trạng thái hợp lệ, hủy có lý do, thử thao tác sai | Cập nhật và lịch sử cùng transaction; sai bị chặn | Chờ ghi |
| A10 | Chạy lại Seeder có kiểm soát | inserted=0 khi dữ liệu không đổi, không sửa tài khoản Admin | Chờ ghi |
| A11 | Kiểm tra git staged, file bí mật và log | Không commit .env, .env.admin-test, vendor, mật khẩu, file Core của Minh | Chờ ghi |

Dùng tài khoản/đơn/sản phẩm **test** mới hoặc chưa kết thúc; không reset đơn đã hoàn tất chỉ để thử lại. Nếu không có staff, tạo tài khoản `@example.test` riêng qua trang Admin; không dùng khách Seeder vì mật khẩu ngẫu nhiên. Không ghi kết quả PASS nếu chưa thực hiện. Chụp ảnh đã che thông tin bí mật, ghi commit, ngày, ca kiểm thử, kết quả thực tế.

## 3. Tổng kết lộ trình 20 Task

| Task | Phạm vi | Mức xác nhận |
|---|---|---|
| 01–06 | Môi trường, Git, MVC, layout, dashboard | Đã triển khai theo báo cáo trước; dashboard còn placeholder |
| 07–13 | Repository/Service/Controller sản phẩm, phân trang, thêm/sửa/ẩn/xóa mềm | Đã triển khai, commit được ghi nhận |
| 14.1–14.6 | Đọc hosting, CRUD sản phẩm/danh mục trên DB test | Đã triển khai; cần kiểm thử tích hợp DB thật |
| 15 | Thương hiệu | Push b61da6f được xác nhận |
| 16 | Tài khoản | Push c35da1d được xác nhận |
| 17 | RBAC | Người thực hiện báo đã commit; cần lấy mã commit và test quyền |
| 18 | Đơn hàng mô phỏng | Người thực hiện báo chuyển trạng thái được; chưa có log push trong cuộc trao đổi |
| 19 | Seeder | Người thực hiện báo xử lý lỗi danh mục xong; chưa có log PASS/push đầy đủ trong cuộc trao đổi |
| 20 | QA, sửa phạm vi đơn mẫu, bàn giao | Offline PASS trên bản ghép; chờ DB/manual/Git của người thực hiện |

Mốc 20/20 chỉ là hoàn tất lộ trình triển khai Admin sau khi kiểm tra và commit; không đồng nghĩa website hoàn thiện 100% hoặc đã nghiệm thu.

## 4. Bàn giao nhóm và việc còn mở

Nguồn đối chiếu: `FOURTECH_TONG_KET_TIEN_DO_ADMIN.md`, `FOURTECH_Doi_chieu_va_xu_ly_chung.md` (26/11/2026); code/tài liệu integration của Task15–19. Bản local có 11 bảng rút gọn, không phải schema iNET (báo cáo ghi 59 bảng). Không import local DDL/seeders lên hosting. Không đưa router docs ra internet.

- **Minh (Core/Router/Security):** chốt commit nền, chạy `/health`, đăng ký route chính thức của Admin; hợp nhất Session/CSRF/RBAC, xác thực và bảo vệ thông tin cá nhân. Router local dùng email test, các role cố định, chưa có 2FA, audit đầy đủ hoặc bảo mật production. Đồng bộ Vite/Tailwind build thay CDN preview. Không thay Core trên nhánh Admin.
- **Nhuận (Store/DB):** đối chiếu DDL, khóa, trạng thái và chữ ký CheckoutService/OrderService; sản phẩm còn thiếu giá/cấu hình/biến thể/ảnh/IMEI/tồn kho; đơn mô phỏng không có thanh toán, nhả giữ hàng, giao vận thật. Chốt các quy tắc thời hạn giữ hàng còn khác nhau giữa tài liệu; thực hiện trừ tồn khi bàn giao, giao thành công kích hoạt bảo hành; hủy/hoàn tiền/đối soát và đổi trả phải đi qua nghiệp vụ chung, không trực tiếp đổi trạng thái như local.
- **Tín (Frontend Store):** ghép route, tài nguyên, catalog, biến thể và checkout dùng dữ liệu server; kiểm tra đơn khách nhìn thấy đồng nhất với Admin.
- **Đăng (Admin):** gửi commit cuối, kết quả QA và bảng lỗi; cùng Minh/Nhuận nối Controller/Service/Repository với middleware và nghiệp vụ chính thức; thử toàn luồng trên DB test chung từ Store đến Admin, hủy/nhả tồn đúng một lần. Dashboard doanh thu/tồn kho thật chưa hoàn tất, không báo đã có.

Không commit file chứa bí mật. `git ls-files -- .env .env.admin-test` phải trống; `git check-ignore -v .env .env.admin-test` xác nhận rule. Không dùng `git add .` vì có file Core ngoài phạm vi. Nếu bí mật từng vào Git, báo nhóm để thu hồi/đổi, không chỉ xóa file. Không tự merge vào nhánh chính.

## 5. Commit và gửi nhóm

```powershell
git add app/Repositories/Admin/OrderAdminRepository.php
git add docs/check-admin-task20.php docs/check-admin-catalog-rules.php docs/admin-task20-handover.md
git --no-pager diff --cached --name-only
git ls-files -- .env .env.admin-test
git diff --cached --check
git commit -m "test(admin): add regression checks and handover documentation"
git push origin feature/dang-admin-module
git log --oneline -5
git status
```

Mẫu báo cáo: Đăng | feature/dang-admin-module | commit: ... | offline: ... | DB: ... | manual: ... | lỗi còn lại: ... | cần Minh/Nhuận/Tín hỗ trợ: ... .
