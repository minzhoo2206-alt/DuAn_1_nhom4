<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập | FOURTECH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { navy: '#061936', brand: '#1261dc', pale: '#e8f3ff' } } } }</script>
<link rel="stylesheet" href="../../assets/css/style.css"></head>

<body class="bg-slate-50 text-slate-900">
<?php require __DIR__ . '/../../resources/views/components/header.php'; ?>    <main class="mx-auto max-w-md px-4 py-12">
        <form onsubmit="event.preventDefault();alert('Chức năng đăng nhập cần backend.')"
            class="rounded-2xl border bg-white p-7 shadow-sm">
            <h1 class="text-center text-2xl font-black text-navy">ĐĂNG NHẬP</h1>
            <p class="mb-6 mt-2 text-center text-sm text-slate-500">Chào mừng bạn quay lại FOURTECH</p><input required
                class="mb-3 w-full rounded-lg border p-3" placeholder="Email hoặc số điện thoại"><input required
                type="password" class="mb-5 w-full rounded-lg border p-3" placeholder="Mật khẩu"><button
                class="w-full rounded-xl bg-brand py-3 font-bold text-white">Đăng nhập</button>
            <p class="mt-5 text-center text-sm">Chưa có tài khoản? <a class="font-bold text-brand"
                    href="register.php">Đăng ký</a></p>
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