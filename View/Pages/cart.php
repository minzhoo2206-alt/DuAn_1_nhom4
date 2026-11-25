<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giỏ hàng | FOURTECH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { navy: '#061936', brand: '#1261dc', pale: '#e8f3ff' } } } }</script>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="bg-slate-50 text-slate-900">
   <?php require __DIR__ . '/../../resources/views/components/header.php'; ?>
    <main class="mx-auto max-w-7xl px-4 py-8">
        <h1 class="mb-6 text-3xl font-black text-navy">GIỎ HÀNG CỦA BẠN</h1>
        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            <section id="cart-items" class="rounded-2xl border bg-white p-5"></section>
            <aside class="h-fit rounded-2xl border bg-white p-5">
                <h2 class="text-xl font-bold">Tóm tắt đơn hàng</h2>
                <div class="my-4 flex justify-between"><span>Tổng cộng</span><b data-cart-total class="text-red-700">0
                        đ</b></div><a href="checkout.php"
                    class="mt-5 block rounded-xl bg-brand py-3 text-center font-bold text-white">Tiến hành thanh
                    toán</a><a href="products.php" class="mt-3 block text-center text-sm text-brand">Tiếp tục mua
                    sắm</a>
            </aside>
        </div>
    </main>
    <section
        class="mx-auto my-8 grid max-w-7xl gap-3 rounded-2xl bg-pale px-5 py-5 text-center font-bold text-navy md:grid-cols-3">
        <div>♧ Tư vấn tận tâm</div>
        <div class="border-y border-blue-200 py-3 md:border-x md:border-y-0">🚚 Giao hàng toàn quốc</div>
        <div>♢ Hỗ trợ bảo hành</div>
    </section>
<?php require __DIR__ . '/../../resources/views/components/footer.php'; ?>    <script src="../../assets/js/products.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>

</html>