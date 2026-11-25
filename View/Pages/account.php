<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tài khoản | FOURTECH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { navy: '#061936', brand: '#1261dc', pale: '#e8f3ff' } } } }</script>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="bg-slate-50 text-slate-900">
<?php require __DIR__ . '/../../resources/views/components/header.php'; ?>    <main class="mx-auto max-w-5xl px-4 py-8">
        <h1 class="mb-6 text-3xl font-black text-navy">TÀI KHOẢN CỦA TÔI</h1>
        <div class="grid gap-5 md:grid-cols-[240px_1fr]">
            <aside class="rounded-2xl border bg-white p-5"><b>Xin chào khách hàng!</b>
                <nav class="mt-4 grid gap-3 text-sm"><a href="account.php">Thông tin cá nhân</a><a
                        href="orders.php">Lịch sử đơn hàng</a><a href="login.php">Đăng nhập</a></nav>
            </aside>
            <section class="rounded-2xl border bg-white p-6">
                <h2 class="mb-4 text-xl font-bold">Thông tin cá nhân</h2>
                <p class="text-sm text-slate-500">Thông tin tài khoản sẽ hiển thị sau khi kết nối hệ thống đăng nhập.
                </p>
            </section>
        </div>
    </main>
    <section
        class="mx-auto my-8 grid max-w-7xl gap-3 rounded-2xl bg-pale px-5 py-5 text-center font-bold text-navy md:grid-cols-3">
        <div>♧ Tư vấn tận tâm</div>
        <div class="border-y border-blue-200 py-3 md:border-x md:border-y-0">🚚 Giao hàng toàn quốc</div>
        <div>♢ Hỗ trợ bảo hành</div>
    </section>
<?php require __DIR__ . '/../../resources/views/components/footer.php'; ?>    <script src="../../assets/js/products.js"></script>
<script src="../../assets/js/main.js"></script></body>

</html>