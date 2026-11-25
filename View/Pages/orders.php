<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đơn hàng | FOURTECH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#061936',
                        brand: '#1261dc',
                        pale: '#e8f3ff'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="bg-slate-50 text-slate-900">
    <?php require __DIR__ . '/../../resources/views/components/header.php'; ?> <main class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="mb-6 text-3xl font-black text-navy">LỊCH SỬ ĐƠN HÀNG</h1>
        <div class="overflow-x-auto rounded-2xl border bg-white p-5">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead class="border-b bg-slate-50">
                    <tr>
                        <th class="p-3">Mã đơn</th>
                        <th class="p-3">Ngày đặt</th>
                        <th class="p-3">Tổng tiền</th>
                        <th class="p-3">Trạng thái</th>
                    </tr>
                </thead>
                <tbody id="order-list">
                    <tr>
                        <td class="p-3 text-slate-500" colspan="4">Bạn chưa có đơn hàng nào.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
    <section
        class="mx-auto my-8 grid max-w-7xl gap-3 rounded-2xl bg-pale px-5 py-5 text-center font-bold text-navy md:grid-cols-3">
        <div>♧ Tư vấn tận tâm</div>
        <div class="border-y border-blue-200 py-3 md:border-x md:border-y-0">🚚 Giao hàng toàn quốc</div>
        <div>♢ Hỗ trợ bảo hành</div>
    </section>
    <?php require __DIR__ . '/../../resources/views/components/footer.php'; ?> <script src="../../assets/js/products.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script>
        const orders = JSON.parse(localStorage.getItem("fourtech_orders") || "[]");
        const orderList = document.getElementById("order-list");

        if (orders.length > 0) {
            // Xóa dòng “Bạn chưa có đơn hàng nào.”
            orderList.textContent = "";

            // Hiện đơn mới nhất ở đầu bảng
            for (const order of orders.reverse()) {
                const row = orderList.insertRow();
                const price = Number(order.total).toLocaleString("vi-VN") + " đ";

                for (const value of [order.code, order.date, price, order.status]) {
                    const cell = row.insertCell();
                    cell.className = "p-3";
                    cell.textContent = value;
                }
            }
        }
    </script>
</body>

</html>