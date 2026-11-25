<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thanh toán | FOURTECH</title>
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
    <?php require __DIR__ . '/../../resources/views/components/header.php'; ?> <main class="mx-auto max-w-5xl px-4 py-8">
        <h1 class="mb-6 text-3xl font-black text-navy">THANH TOÁN</h1>
        <form
            onsubmit="event.preventDefault(); this.style.display = 'none'; const message = document.getElementById('order-success'); message.hidden = false; message.focus();"
            class="grid gap-6 md:grid-cols-2">
            <section class="rounded-2xl border bg-white p-6">
                <h2 class="mb-4 text-xl font-bold">Thông tin nhận hàng</h2><label
                    class="mb-3 block text-sm font-semibold">Họ và tên<input required
                        class="mt-1 w-full rounded-lg border p-3 font-normal" placeholder="Họ và tên"></label><label
                    class="mb-3 block text-sm font-semibold">Số điện thoại<input required pattern="0[0-9]{9}"
                        title="Nhập 10 chữ số, bắt đầu bằng số 0" class="mt-1 w-full rounded-lg border p-3 font-normal"
                        placeholder="Số điện thoại"></label><label class="mb-3 block text-sm font-semibold">Email<input
                        required class="mt-1 w-full rounded-lg border p-3 font-normal"
                        placeholder="Email"></label><label class="mb-3 block text-sm font-semibold">Địa chỉ nhận
                    hàng<input required class="mt-1 w-full rounded-lg border p-3 font-normal"
                        placeholder="Địa chỉ nhận hàng"></label><label class="block text-sm font-semibold">Ghi
                    chú<textarea class="mt-1 w-full rounded-lg border p-3 font-normal" rows="3"></textarea></label>
            </section>
            <section class="h-fit rounded-2xl border bg-white p-6">
                <h2 class="mb-4 text-xl font-bold">Phương thức thanh toán</h2>
                <ul id="checkout-items" class="mb-2 text-sm"></ul>
                <p class="mb-4">
                    Tạm tính: <strong id="checkout-total"></strong>
                </p><label class="mb-3 block rounded-lg border p-3"><input type="radio" name="pay" checked> Thanh toán
                    khi nhận
                    hàng (COD)</label><label class="block rounded-lg border p-3"><input type="radio" name="pay"> Chuyển
                    khoản ngân hàng</label><button class="mt-6 w-full rounded-xl bg-brand py-3 font-bold text-white">Đặt
                    hàng</button>
            </section>
        </form>
        <section id="order-success" hidden tabindex="-1" class="rounded-2xl border bg-white p-6">
            <h2 class="mb-2 text-xl font-bold">Đã xác nhận đơn hàng mẫu</h2>
            <p>Bạn đã hoàn tất bước đặt hàng trên bản demo FOURTECH.</p>
            <a href="products.php" class="mt-4 inline-block text-blue-700 underline"> Tiếp tục mua sắm
            </a>
        </section>
    </main>
    <section
        class="mx-auto my-8 grid max-w-7xl gap-3 rounded-2xl bg-pale px-5 py-5 text-center font-bold text-navy md:grid-cols-3">
        <div>♧ Tư vấn tận tâm</div>
        <div class="border-y border-blue-200 py-3 md:border-x md:border-y-0">🚚 Giao hàng toàn quốc</div>
        <div>♢ Hỗ trợ bảo hành</div>
    </section>
    <?php require __DIR__ . '/../../resources/views/components/footer.php'; ?> <script src="../../assets/js/products.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>

</html>