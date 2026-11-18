// JavaScript nhỏ cho giao diện quản trị: xác nhận thao tác demo.
document
  .querySelectorAll("[data-demo-action]")
  .forEach((btn) =>
    btn.addEventListener("click", () =>
      alert("Đây là nút giao diện mẫu. Chức năng sẽ được kết nối backend sau."),
    ),
  );
