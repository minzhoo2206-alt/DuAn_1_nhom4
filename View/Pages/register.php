<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký | FOURTECH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { navy: '#061936', brand: '#1261dc', pale: '#e8f3ff' } } } }</script>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="bg-slate-50 text-slate-900">
<?php require __DIR__ . '/../../resources/views/components/header.php'; ?>    <main class="mx-auto max-w-md px-4 py-12">
        <form onsubmit="event.preventDefault();alert('Chức năng đăng ký cần backend.')"
            class="rounded-2xl border bg-white p-7 shadow-sm">
            <h1 class="mb-6 text-center text-2xl font-black text-navy">TẠO TÀI KHOẢN</h1><input required
                class="mb-3 w-full rounded-lg border p-3" placeholder="Họ và tên"><input required
                class="mb-3 w-full rounded-lg border p-3" placeholder="Số điện thoại"><input required
                class="mb-3 w-full rounded-lg border p-3" placeholder="Email"><input required
                class="mb-3 w-full rounded-lg border p-3" placeholder="Mật khẩu" type=password><button
                class="w-full rounded-xl bg-brand py-3 font-bold text-white">Đăng ký</button>
            <p class="mt-5 text-center text-sm">Đã có tài khoản? <a class="text-brand" href="login.php">Đăng nhập</a>
            </p>
        </form>
    </main>
    <section
        class="mx-auto my-8 grid max-w-7xl gap-3 rounded-2xl bg-pale px-5 py-5 text-center font-bold text-navy md:grid-cols-3">
        <div>♧ Tư vấn tận tâm</div>
        <div class="border-y border-blue-200 py-3 md:border-x md:border-y-0">🚚 Giao hàng toàn quốc</div>
        <div>♢ Hỗ trợ bảo hành</div>
    </section>
<?php require __DIR__ . '/../../resources/views/components/footer.php'; ?>    
<script src="../../assets/js/products.js"></script>
<script src="../../assets/js/main.js"></script>
</body>

</html>