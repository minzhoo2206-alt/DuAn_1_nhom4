# Checklist UAT module Admin

- [ ] Admin đăng nhập và truy cập Dashboard.
- [ ] Staff/customer bị chặn quyền không được phép (cả bằng URL trực tiếp).
- [ ] 100 sản phẩm và 20 danh mục demo **nếu nhóm đã xác nhận seed schema** được hiển thị đúng.
- [ ] Tìm kiếm, phân trang, thêm, sửa và ẩn/hiện sản phẩm.
- [ ] Thêm, sửa, ẩn/hiện danh mục và thương hiệu.
- [ ] Validation name/slug/status, không tạo slug trùng.
- [ ] CSRF bị từ chối khi thiếu/sai.
- [ ] Gán role đúng user, không tự gán role.
- [ ] Trang đơn hàng hiển thị dữ liệu thật và 404 khi ID sai.
- [ ] Chuyển trạng thái đơn chỉ được mở sau khi Nhuận bàn giao transition API.
- [ ] Admin giao diện phù hợp desktop/tablet/mobile.
- [ ] Có báo cáo test, danh sách lỗi đã đóng hoặc đã chấp thuận.
